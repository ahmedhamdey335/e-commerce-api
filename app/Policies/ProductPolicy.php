<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     * Everyone can browse products.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a single product.
     */
    public function view(User $user, Product $product): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create a product.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSeller();
    }

    /**
     * Determine whether the user can update a product.
     * Admin can update any product.
     * Seller can only update their own product.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin() ||
            ($user ->isSeller() && $user->id === $product->user_id);
    }

    /**
     * Determine whether the user can delete a product.
     * Same logic as update
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin() ||
            ($user ->isSeller() && $user->id === $product->user_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }
}
