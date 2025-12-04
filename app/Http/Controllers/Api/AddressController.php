<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->addresses;
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);
        $address = $request->user()->addresses()->create($validated);

        return response()->json([
            'message' => 'Address created successfully',
            'address' => $address,
        ], 201);
    }
}