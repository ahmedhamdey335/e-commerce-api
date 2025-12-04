<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
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
            
            $fulladdress = "{$address->title}, {$address->address}, {$address->city}, {$address->postal_code}, {$address->country}";
            // Create Order Record
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'address' => $fulladdress,
                'total_price' => $total / 100,
            ]);
            //Move products from Cart to OrderItems
            foreach ($cartItems as $cartItem) {
                // Check stock one last time
                if ($cartItem->product->stock < $cartItem->quantity) {
                    throw new \Exception("Product {$cartItem->product->name} is out of stock");
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
        });
    }
                        //---end transaction---
    public function index(Request $request){
    return $request->user()->orders()->with('items.product')->latest()->get();
    }
}