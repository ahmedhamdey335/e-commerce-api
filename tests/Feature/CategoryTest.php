<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_all_categories(): void
    {
        // Arrange
        Category::factory(3)->create();

        // Act
        $response = $this->getJson('/api/categories');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);
    }

    public function test_guest_can_view_single_category_with_products(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $category->products()->attach($product->id);

        // Act
        $response = $this->getJson('/api/categories/'.$category->id);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'products',
                ],
            ]);
    }

    public function test_admin_can_create_category(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);

        // Act
        $response = $this->actingAs($admin)->postJson('/api/categories', [
            'name' => 'Electronics',
            'description' => 'Devices and gadgets',
        ]);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'name', 'slug'],
            ]);
        $this->assertDatabaseHas('categories', ['name' => 'Electronics']);
    }

    public function test_admin_cannot_create_category_with_duplicate_name(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        Category::factory()->create(['name' => 'Electronics']);

        // Act
        $response = $this->actingAs($admin)->postJson('/api/categories', [
            'name' => 'Electronics',
            'description' => 'Duplicate',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors' => ['name'],
            ]);
    }

    public function test_admin_cannot_create_category_with_missing_name(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);

        // Act
        $response = $this->actingAs($admin)->postJson('/api/categories', [
            'description' => 'No name provided',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors' => ['name'],
            ]);
    }

    public function test_guest_cannot_create_category(): void
    {
        // Act
        $response = $this->postJson('/api/categories', [
            'name' => 'Electronics',
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_seller_cannot_create_category(): void
    {
        // Arrange
        $seller = User::factory()->create(['role' => 'seller']);

        // Act
        $response = $this->actingAs($seller)->postJson('/api/categories', [
            'name' => 'Electronics',
        ]);

        // Assert
        $response->assertStatus(403)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_customer_cannot_create_category(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);

        // Act
        $response = $this->actingAs($customer)->postJson('/api/categories', [
            'name' => 'Electronics',
        ]);

        // Assert
        $response->assertStatus(403)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_admin_can_update_category(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create(['name' => 'Old Name']);

        // Act
        $response = $this->actingAs($admin)->putJson('/api/categories/'.$category->id, [
            'name' => 'New Name',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'name', 'slug'],
            ])
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_guest_cannot_update_category(): void
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $response = $this->putJson('/api/categories/'.$category->id, [
            'name' => 'Hacked Name',
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_admin_can_delete_category(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        // Act
        $response = $this->actingAs($admin)->deleteJson('/api/categories/'.$category->id);

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_guest_cannot_delete_category(): void
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $response = $this->deleteJson('/api/categories/'.$category->id);

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }
}
