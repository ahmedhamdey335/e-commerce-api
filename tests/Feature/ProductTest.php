<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    // ---Public Endpoints---

    public function test_guest_can_view_all_products()
    {
        // Create 3 products in the database
        Product::factory(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);
    }

    public function test_guest_can_view_single_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'price',
                    'stock',
                ],
            ]);
    }

    public function test_guest_can_search_products()
    {
        Product::factory()->create(['name' => 'iPhone 17']);

        $response = $this->getJson('/api/search?q=iPhone');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);
    }

    // ---Seller Endpoints---

    public function test_seller_can_create_product()
    {
        $seller = User::factory()->create(['role' => 'seller']);

        $response = $this->actingAs($seller)->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'name', 'price', 'stock'],
            ]);
    }

    public function test_guest_cannot_create_product()
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10,
        ]);

        $response->assertStatus(401);
    }

    public function test_customer_cannot_create_product()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10,
        ]);

        $response->assertStatus(403);
    }

    public function test_seller_can_update_own_product()
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAs($seller)->putJson('/api/products/' . $product->id, [
            'name' => 'Updated Product',
            'price' => 199.99,
            'stock' => 20,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Product');
    }

    public function test_seller_cannot_update_another_sellers_product()
    {
        $seller1 = User::factory()->create(['role' => 'seller']);
        $seller2 = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller1->id]);

        $response = $this->actingAs($seller2)->putJson('/api/products/' . $product->id, [
            'name' => 'Hacked Product',
        ]);

        $response->assertStatus(403);
    }

    public function test_seller_can_delete_own_product()
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAs($seller)->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(204);
    }

    public function test_seller_cannot_delete_another_sellers_product()
    {
        $seller1 = User::factory()->create(['role' => 'seller']);
        $seller2 = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller1->id]);

        $response = $this->actingAs($seller2)->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(403);
    }

    // ---Admin Endpoints---

    public function test_admin_can_create_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/products', [
            'name' => 'Admin Product',
            'price' => 99.99,
            'stock' => 10,
        ]);

        $response->assertStatus(201);
    }

    public function test_admin_can_update_any_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAs($admin)->putJson('/api/products/' . $product->id, [
            'name' => 'Admin Updated Product',
        ]);

        $response->assertStatus(200);
    }

    public function test_admin_can_delete_any_product()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id]);

        $response = $this->actingAs($admin)->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(204);
    }
}