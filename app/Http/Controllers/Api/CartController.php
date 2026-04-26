<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Product;
use App\Http\Requests\AddToCartRequest;
use App\Http\Resources\CartItemResource;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()->cartItems()->with('product')->get();
        return $this->success(CartItemResource::collection($items));
    }

    public function store(AddToCartRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        // Check product stock
        $product = Product::findOrFail($validated['product_id']);
        if ($product->stock < $validated['quantity']) {
            return $this->error('Not enough stock available', 400);
        }
        // Check if item already in cart
        $cartItem = CartItem::where('user_id', $user->id)
                            ->where('product_id', $validated['product_id'])
                            ->first();
        if ($cartItem) {
            $cartItem->quantity += $validated['quantity'];
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'user_id' => $user->id,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]);
        }
        return $this->success(new CartItemResource($cartItem->load('product')), 'Item added to cart successfully', 201);
    }
    
    public function destroy(Request $request, CartItem $cartItem) {
        // Ensure the cart belongs to the authenticated user
        if ($request->user()->id !== $cartItem->user_id) {
            return $this->error('Unauthorized', 403);
        }
        $cartItem->delete();
        return $this->success(null, 'Item removed from cart successfully', 204);
    }
}