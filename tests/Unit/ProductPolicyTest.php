<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProductPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ProductPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ProductPolicy;
    }

    public function test_admin_can_create_product(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);

        // Act & Assert
        $this->assertTrue($this->policy->create($admin));
        $this->assertTrue(Gate::forUser($admin)->allows('create', Product::class));
    }

    public function test_seller_can_create_product(): void
    {
        // Arrange
        $seller = User::factory()->create(['role' => 'seller']);

        // Act & Assert
        $this->assertTrue($this->policy->create($seller));
        $this->assertTrue(Gate::forUser($seller)->allows('create', Product::class));
    }

    public function test_customer_cannot_create_product(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);

        // Act & Assert
        $this->assertFalse($this->policy->create($customer));
        $this->assertFalse(Gate::forUser($customer)->allows('create', Product::class));
    }

    public function test_admin_can_update_any_product(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id]);

        // Act & Assert
        $this->assertTrue($this->policy->update($admin, $product));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $product));
    }

    public function test_seller_can_update_own_product(): void
    {
        // Arrange
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id]);

        // Act & Assert
        $this->assertTrue($this->policy->update($seller, $product));
        $this->assertTrue(Gate::forUser($seller)->allows('update', $product));
    }

    public function test_seller_cannot_update_another_sellers_product(): void
    {
        // Arrange
        $seller1 = User::factory()->create(['role' => 'seller']);
        $seller2 = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller1->id]);

        // Act & Assert
        $this->assertFalse($this->policy->update($seller2, $product));
        $this->assertFalse(Gate::forUser($seller2)->allows('update', $product));
    }

    public function test_admin_can_delete_any_product(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id]);

        // Act & Assert
        $this->assertTrue($this->policy->delete($admin, $product));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $product));
    }

    public function test_seller_can_delete_own_product(): void
    {
        // Arrange
        $seller = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller->id]);

        // Act & Assert
        $this->assertTrue($this->policy->delete($seller, $product));
        $this->assertTrue(Gate::forUser($seller)->allows('delete', $product));
    }

    public function test_seller_cannot_delete_another_sellers_product(): void
    {
        // Arrange
        $seller1 = User::factory()->create(['role' => 'seller']);
        $seller2 = User::factory()->create(['role' => 'seller']);
        $product = Product::factory()->create(['user_id' => $seller1->id]);

        // Act & Assert
        $this->assertFalse($this->policy->delete($seller2, $product));
        $this->assertFalse(Gate::forUser($seller2)->allows('delete', $product));
    }
}
