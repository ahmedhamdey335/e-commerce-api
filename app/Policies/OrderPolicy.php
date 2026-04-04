<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * View all orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * View a single order.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() ||
            ($user->isCustomer() && $user->id === $order->user_id);
    }

    /**
     * Only customers can place orders.
     */
    public function create(User $user): bool
    {
        return $user->isCustomer();
    }

    /**
     * Nobody can update an order for now.
     * Can be opened later for admin (e.g. change order status).
     */
    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admin can delete an order.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
