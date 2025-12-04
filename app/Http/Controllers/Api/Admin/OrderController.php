<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;


class OrderController extends Controller
{
    // View all orders (for the Admin dashboard)
    public function index()
    {
        $orders = Order::with('user', 'items.product')->latest()->paginate(10);
        return response()->json($orders);
    }

    public function updatestatus (Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,shipped,cancelled',
        ]);
        $order->update(['status' => $request->status,]);
        return response()->json(['message' => 'Order status updated.', 'order' => $order]);
    }
}