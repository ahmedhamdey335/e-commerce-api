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
        // Create a specific Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Password is "password"
            'role' => 'admin',
            'phone' => '1234567890',
        ]);
        // Create a regular User
        User::create([
            'name' => 'Test customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '0987654321',
        ]);
        // Create Categories
        $electronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $apple = Category::create(['name' => 'Apple', 'slug' => 'apple']);
        $samsung = Category::create(['name' => 'Samsung', 'slug' => 'samsung']);

        // Create Products
        Product::create([
            'name' => 'iPhone 17',
            'slug' => 'iphone-17',
            'description' => 'Latest Apple smartphone.',
            'price' => 99900, // $999.00
            'stock' => 10,
        ])->categories()->attach([$electronics->id, $apple->id]);

        Product::create([
            'name' => 'Samsung Galaxy S30',
            'slug' => 'samsung-galaxy-s30',
            'description' => 'Latest Samsung smartphone.',
            'price' => 89900, // $899.00
            'stock' => 15,
        ])->categories()->attach([$electronics->id, $samsung->id]);

    }
}
