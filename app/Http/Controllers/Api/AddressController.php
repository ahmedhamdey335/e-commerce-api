<?php

namespace App\Http\Controllers\Api;

use App\Models\Address;
use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return $this->success(AddressResource::collection($request->user()->addresses));
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

        return $this->success(new AddressResource($address), 'Address created successfully', 201);
    }

    public function update(Request $request, Address $address) {
        $this->authorize('update', $address);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:50',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $address->update($validated);

        return $this->success(new AddressResource($address), 'Address updated successfully');
    }
    
    public function destroy(Address $address) {
        $this->authorize('delete', $address);
        $address->delete();
        return $this->success(null, 'Address deleted successfully', 204);
    }
}