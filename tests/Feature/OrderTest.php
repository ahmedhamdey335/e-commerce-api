<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_checkout_successfully(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create([
            'user_id' => $seller->id,
            'price' => 10000,
            'stock' => 10,
        ]);
        $address = Address::create([
            'user_id' => $customer->id,
            'title' => 'Home',
            'address' => 'Street 1',
            'city' => 'Cairo',
            'postal_code' => '12345',
            'country' => 'Egypt',
        ]);
        CartItem::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Act
        $response = $this->actingAs($customer)->postJson('/api/checkout', [
            'address_id' => $address->id,
        ]);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['order_id'],
            ]);
        $this->assertDatabaseHas('orders', ['user_id' => $customer->id]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_customer_cannot_checkout_with_empty_cart(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        $address = Address::create([
            'user_id' => $customer->id,
            'title' => 'Home',
            'address' => 'Street 1',
            'city' => 'Cairo',
            'postal_code' => '12345',
            'country' => 'Egypt',
        ]);

        // Act
        $response = $this->actingAs($customer)->postJson('/api/checkout', [
            'address_id' => $address->id,
        ]);

        // Assert
        $response->assertStatus(400)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_customer_cannot_checkout_with_invalid_address(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id, 'stock' => 10]);
        $otherAddress = Address::create([
            'user_id' => $otherCustomer->id,
            'title' => 'Work',
            'address' => 'Street 2',
            'city' => 'Giza',
            'postal_code' => '54321',
            'country' => 'Egypt',
        ]);
        CartItem::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Act
        $response = $this->actingAs($customer)->postJson('/api/checkout', [
            'address_id' => $otherAddress->id,
        ]);

        // Assert
        $response->assertStatus(400)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_guest_cannot_checkout(): void
    {
        // Act
        $response = $this->postJson('/api/checkout', [
            'address_id' => 1,
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_seller_cannot_checkout(): void
    {
        // Arrange
        $seller = User::factory()->create(['role' => 'seller']);

        // Act
        $response = $this->actingAs($seller)->postJson('/api/checkout', [
            'address_id' => 1,
        ]);

        // Assert
        $response->assertStatus(403)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_customer_can_view_their_orders(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        Order::create([
            'user_id' => $customer->id,
            'total_price' => 120.50,
            'status' => 'pending',
            'address' => 'Home, Street 1, Cairo',
        ]);

        // Act
        $response = $this->actingAs($customer)->getJson('/api/orders');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);
    }

    public function test_admin_can_view_all_orders(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        Order::create([
            'user_id' => $customer->id,
            'total_price' => 99.99,
            'status' => 'pending',
            'address' => 'Home, Street 1, Cairo',
        ]);

        // Act
        $response = $this->actingAs($admin)->getJson('/api/orders');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);
    }

    public function test_seller_can_view_orders_containing_their_products(): void
    {
        // Arrange
        $seller = User::factory()->create(['role' => 'seller']);
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create(['user_id' => $seller->id]);
        $order = Order::create([
            'user_id' => $customer->id,
            'total_price' => 50.00,
            'status' => 'pending',
            'address' => 'Home, Street 1, Cairo',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        // Act
        $response = $this->actingAs($seller)->getJson('/api/orders');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);
    }

    public function test_guest_cannot_view_orders(): void
    {
        // Act
        $response = $this->getJson('/api/orders');

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_admin_can_update_order_status(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'user_id' => $customer->id,
            'total_price' => 120.50,
            'status' => 'pending',
            'address' => 'Home, Street 1, Cairo',
        ]);

        // Act
        $response = $this->actingAs($admin)->patchJson('/api/orders/'.$order->id.'/status', [
            'status' => 'processing',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'status'],
            ])
            ->assertJsonPath('data.status', 'processing');
    }

    public function test_admin_cannot_update_order_status_with_invalid_status(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'user_id' => $customer->id,
            'total_price' => 120.50,
            'status' => 'pending',
            'address' => 'Home, Street 1, Cairo',
        ]);

        // Act
        $response = $this->actingAs($admin)->patchJson('/api/orders/'.$order->id.'/status', [
            'status' => 'invalid_status',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors' => ['status'],
            ]);
    }

    public function test_customer_cannot_update_order_status(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'user_id' => $customer->id,
            'total_price' => 120.50,
            'status' => 'pending',
            'address' => 'Home, Street 1, Cairo',
        ]);

        // Act
        $response = $this->actingAs($customer)->patchJson('/api/orders/'.$order->id.'/status', [
            'status' => 'processing',
        ]);

        // Assert
        $response->assertStatus(403)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_guest_cannot_update_order_status(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'user_id' => $customer->id,
            'total_price' => 120.50,
            'status' => 'pending',
            'address' => 'Home, Street 1, Cairo',
        ]);

        // Act
        $response = $this->patchJson('/api/orders/'.$order->id.'/status', [
            'status' => 'processing',
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }
}
