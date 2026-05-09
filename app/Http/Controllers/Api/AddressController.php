<?php

namespace App\Http\Controllers\Api;

use App\Models\Address;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
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
    public function store(StoreAddressRequest $request)
    {
        $address = $request->user()->addresses()->create($request->validated());

        return $this->success(new AddressResource($address), 'Address created successfully', 201);
    }

    /**
     * Update address
     *
     * Updates an existing address owned by the authenticated customer. Requires customer role.
     */
    public function update(UpdateAddressRequest $request, Address $address) {
        $this->authorize('update', $address);

        $address->update($request->validated());

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