<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_their_cart(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id, 'stock' => 10]);
        CartItem::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Act
        $response = $this->actingAs($customer)->getJson('/api/cart');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);
    }

    public function test_guest_cannot_view_cart(): void
    {
        // Act
        $response = $this->getJson('/api/cart');

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_seller_cannot_view_cart(): void
    {
        // Arrange
        $seller = User::factory()->create(['role' => 'seller']);

        // Act
        $response = $this->actingAs($seller)->getJson('/api/cart');

        // Assert
        $response->assertStatus(403)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_customer_can_add_item_to_cart(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id, 'stock' => 10]);

        // Act
        $response = $this->actingAs($customer)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'quantity', 'product'],
            ]);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_customer_cannot_add_item_with_invalid_product_id(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);

        // Act
        $response = $this->actingAs($customer)->postJson('/api/cart', [
            'product_id' => 999999,
            'quantity' => 1,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors' => ['product_id'],
            ]);
    }

    public function test_customer_cannot_add_item_with_quantity_less_than_one(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id, 'stock' => 10]);

        // Act
        $response = $this->actingAs($customer)->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors' => ['quantity'],
            ]);
    }

    public function test_guest_cannot_add_item_to_cart(): void
    {
        // Arrange
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id, 'stock' => 10]);

        // Act
        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_customer_can_remove_item_from_cart(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id, 'stock' => 10]);
        $cartItem = CartItem::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Act
        $response = $this->actingAs($customer)->deleteJson('/api/cart/'.$cartItem->id);

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_customer_cannot_remove_another_customers_cart_item(): void
    {
        // Arrange
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id, 'stock' => 10]);
        $cartItem = CartItem::create([
            'user_id' => $customer1->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Act
        $response = $this->actingAs($customer2)->deleteJson('/api/cart/'.$cartItem->id);

        // Assert
        $response->assertStatus(403)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_guest_cannot_remove_item_from_cart(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id, 'stock' => 10]);
        $cartItem = CartItem::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Act
        $response = $this->deleteJson('/api/cart/'.$cartItem->id);

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }
}
