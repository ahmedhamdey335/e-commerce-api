<?php

namespace App\Http\Controllers\Api;

use App\Models\Address;
use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use Illuminate\Http\Request;

/**
 * @group Addresses
 *
 * Endpoints for managing customer shipping addresses.
 */
class AddressController extends Controller
{
    /**
     * List addresses
     *
     * Returns all saved addresses for the authenticated customer. Requires customer role.
     */
    public function index(Request $request)
    {
        return $this->success(AddressResource::collection($request->user()->addresses));
    }
    
    /**
     * Create address
     *
     * Creates a new address for the authenticated customer. Requires customer role.
     */
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

    /**
     * Update address
     *
     * Updates an existing address owned by the authenticated customer. Requires customer role.
     */
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
    
    /**
     * Delete address
     *
     * Deletes an address owned by the authenticated customer. Requires customer role.
     */
    public function destroy(Address $address) {
        $this->authorize('delete', $address);
        $address->delete();
        return $this->success(null, 'Address deleted successfully', 204);
    }
}