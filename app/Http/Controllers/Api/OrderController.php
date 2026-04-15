<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Resources\OrderResource;
use App\Http\Resources\SellerOrderResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class OrderController extends Controller
{
    // Browse orders for admin and customer
    public function index(Request $request){
        $user = $request->user();

        // Admin view
        if ($user->isAdmin()) {
            // Admin can see all orders, with relationships loaded
            return OrderResource::collection(
                Order::with('user', 'items.product')->latest()->paginate(10)
            );
        }

        // Seller view (orders that include at least one of their products)
        if ($user->isSeller()) {
            $orders = Order::with('items.product')
                ->whereHas('items', function ($query) use ($user) {
                    $query->whereHas('product', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                })
                ->latest()
                ->paginate(10);

            return SellerOrderResource::collection($orders);
        }

        // Customer view
        $query = $user->orders()->with('items.product')->latest();
        if ($request->filter === 'active') {
            $query->whereIn('status', ['pending', 'processing', 'shipped']);
        } elseif ($request->filter === 'previous') {
            $query->whereIn('status', ['delivered', 'cancelled']);
        }
        return OrderResource::collection($query->get());
    }

    // View placed order
    public function show(Request $request , Order $order){
        $order->load('items.product');

        $this->authorize('view', $order);

        return new OrderResource($order);
    }

    // Update pleced order's status
    public function updateStatus(Request $request, Order $order){
        $this->authorize('update', $order);

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => new OrderResource($order),
        ]);
    }

    // Checkout by customer
    public function checkout(Request $request){
        $this->authorize('create', Order::class);
        // Validate address
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);
        
        $user = $request->user();
        
        // Ensure the address belongs to the user
        $address = $user->addresses()->where('id', $request->address_id)->first();
        if (!$address) {
            return response()->json(['message' => 'Invalid address'], 400);
        }
        
        // Get the user's cart
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

                        //---start transaction---
        return DB::transaction(function () use ($user, $cartItems, $address) {
            // Calculate total price
            $total = $cartItems->sum(function ($item) {
                return ($item->product->price * $item->quantity);
            });
            
            $fullAddress = "{$address->title},
                {$address->address},
                {$address->city}, 
                {$address->postal_code}, 
                {$address->country}";
            
            // Create Order Record
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'address' => $fullAddress,
                'total_price' => $total / 100,
            ]);
            //Move products from Cart to OrderItems
            foreach ($cartItems as $cartItem) {
                // Check stock one last time
                if ($cartItem->product->stock < $cartItem->quantity) {
                    abort(400, "Product {$cartItem->product->name} is out of stock");
                }
                // deduct stock
                $cartItem->product->decrement('stock', $cartItem->quantity);
                // Create OrderItem
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                ]);
            }
                // Clear user's cart
                $user->cartItems()->delete();
                
                return response()->json([
                    'message' => 'Order placed successfully',
                    'order_id' => $order->id,
                ], 201);
                //---end transaction---
        });
    }
}