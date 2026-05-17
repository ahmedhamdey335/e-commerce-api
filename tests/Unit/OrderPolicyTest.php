<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class OrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    private OrderPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new OrderPolicy;
    }

    public function test_admin_can_view_any_order(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->createOrderForCustomer($customer);

        // Act & Assert
        $this->assertTrue($this->policy->view($admin, $order));
        $this->assertTrue(Gate::forUser($admin)->allows('view', $order));
    }

    public function test_customer_can_view_their_own_order(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->createOrderForCustomer($customer);

        // Act & Assert
        $this->assertTrue($this->policy->view($customer, $order));
        $this->assertTrue(Gate::forUser($customer)->allows('view', $order));
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        // Arrange
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);
        $order = $this->createOrderForCustomer($customer1);

        // Act & Assert
        $this->assertFalse($this->policy->view($customer2, $order));
        $this->assertFalse(Gate::forUser($customer2)->allows('view', $order));
    }

    public function test_seller_can_view_order_containing_their_product(): void
    {
        // Arrange
        $seller = User::factory()->create(['role' => 'seller']);
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create(['user_id' => $seller->id]);
        $order = $this->createOrderForCustomer($customer);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);
        $order->load('items.product');

        // Act & Assert
        $this->assertTrue($this->policy->view($seller, $order));
        $this->assertTrue(Gate::forUser($seller)->allows('view', $order));
    }

    public function test_seller_cannot_view_order_not_containing_their_product(): void
    {
        // Arrange
        $seller = User::factory()->create(['role' => 'seller']);
        $otherSeller = User::factory()->create(['role' => 'seller']);
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create(['user_id' => $otherSeller->id]);
        $order = $this->createOrderForCustomer($customer);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);
        $order->load('items.product');

        // Act & Assert
        $this->assertFalse($this->policy->view($seller, $order));
        $this->assertFalse(Gate::forUser($seller)->allows('view', $order));
    }

    public function test_only_customer_can_create_order(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Act & Assert
        $this->assertTrue($this->policy->create($customer));
        $this->assertFalse($this->policy->create($seller));
        $this->assertFalse($this->policy->create($admin));
        $this->assertTrue(Gate::forUser($customer)->allows('create', Order::class));
        $this->assertFalse(Gate::forUser($seller)->allows('create', Order::class));
    }

    public function test_only_admin_can_update_order_status(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $order = $this->createOrderForCustomer($customer);

        // Act & Assert
        $this->assertTrue($this->policy->update($admin, $order));
        $this->assertFalse($this->policy->update($customer, $order));
        $this->assertFalse($this->policy->update($seller, $order));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $order));
        $this->assertFalse(Gate::forUser($customer)->allows('update', $order));
    }

    public function test_only_admin_can_delete_order(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $order = $this->createOrderForCustomer($customer);

        // Act & Assert
        $this->assertTrue($this->policy->delete($admin, $order));
        $this->assertFalse($this->policy->delete($customer, $order));
        $this->assertFalse($this->policy->delete($seller, $order));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $order));
        $this->assertFalse(Gate::forUser($customer)->allows('delete', $order));
    }

    private function createOrderForCustomer(User $customer): Order
    {
        return Order::create([
            'user_id' => $customer->id,
            'total_price' => 99.99,
            'status' => 'pending',
            'address' => 'Home, Street 1, Cairo',
        ]);
    }
}
