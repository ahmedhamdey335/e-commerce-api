<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ---Create Users---
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Password is "password"
            'role' => 'admin',
            'phone' => '1234567890',
        ]);

        $seller1 = User::create([
            'name' => 'Seller One',
            'email' => 'seller1@example.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'phone' => '1111111111',
        ]);

        $seller2 = User::create([
            'name' => 'Seller Two',
            'email' => 'seller2@example.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'phone' => '2222222222',
        ]);

        $customer = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '0987654321',
        ]);

        // ---Create Categories---
        // Known categories
        $electronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $apple = Category::create(['name' => 'Apple', 'slug' => 'apple']);
        $samsung = Category::create(['name' => 'Samsung', 'slug' => 'samsung']);
        // Fake
        $fakeCategories = Category::factory(5)->create();
        // Combining in one collection
        $allCategories = $fakeCategories->push($electronics)->push($apple)->push($samsung);

        // ---Create Products---
        // Known Products (owned by Seller1)
        Product::create([
            'name' => 'iPhone 17',
            'slug' => 'iphone-17',
            'description' => 'Latest Apple smartphone.',
            'price' => 99900,
            'stock' => 10,
            'user_id' => $seller1->id,
        ])->categories()->attach([$electronics->id, $apple->id]);

        Product::create([
            'name' => 'Samsung Galaxy S30',
            'slug' => 'samsung-galaxy-s30',
            'description' => 'Latest Samsung smartphone.',
            'price' => 89900,
            'stock' => 15,
            'user_id' => $seller1->id,
        ])->categories()->attach([$electronics->id, $samsung->id]);

        // Fake Products
        // for Seller1
        Product::factory(10)->create(['user_id' => $seller1->id])
            ->each(function ($product) use ($allCategories) {
                // attach 1-3 random categories to each product
                $product->categories()->attach(
                    $allCategories->random(rand(1, 3))->pluck('id')
                );
            });
        // For Seller2
        Product::factory(8)->create(['user_id' => $seller2->id])
            ->each(function ($product) use ($allCategories) {
                $product->categories()->attach(
                    $allCategories->random(rand(1, 3))->pluck('id')
                );
            });
    }
}
