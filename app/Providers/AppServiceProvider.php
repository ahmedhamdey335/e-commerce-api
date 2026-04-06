<?php

namespace App\Providers;

use App\Models\Product;
use App\Policies\ProductPolicy;
use App\Models\Order;
use App\Policies\OrderPolicy;
use App\Models\Address;
use App\Policies\AddressPolicy;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Address::class, AddressPolicy::class);
    }
}
