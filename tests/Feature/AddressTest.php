<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_their_addresses(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);
        Address::create([
            'user_id' => $customer->id,
            'title' => 'Home',
            'address' => 'Street 1',
            'city' => 'Cairo',
            'postal_code' => '12345',
            'country' => 'Egypt',
        ]);

        // Act
        $response = $this->actingAs($customer)->getJson('/api/addresses');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ]);
    }

    public function test_guest_cannot_view_addresses(): void
    {
        // Act
        $response = $this->getJson('/api/addresses');

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_seller_cannot_view_addresses(): void
    {
        // Arrange
        $seller = User::factory()->create(['role' => 'seller']);

        // Act
        $response = $this->actingAs($seller)->getJson('/api/addresses');

        // Assert
        $response->assertStatus(403)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_customer_can_create_address(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);

        // Act
        $response = $this->actingAs($customer)->postJson('/api/addresses', [
            'title' => 'Home',
            'address' => 'Street 1',
            'city' => 'Cairo',
            'postal_code' => '12345',
            'country' => 'Egypt',
        ]);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'title', 'address', 'city'],
            ]);
        $this->assertDatabaseHas('addresses', [
            'user_id' => $customer->id,
            'city' => 'Cairo',
        ]);
    }

    public function test_customer_cannot_create_address_with_missing_fields(): void
    {
        // Arrange
        $customer = User::factory()->create(['role' => 'customer']);

        // Act
        $response = $this->actingAs($customer)->postJson('/api/addresses', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors' => ['title', 'address', 'city'],
            ]);
    }

    public function test_guest_cannot_create_address(): void
    {
        // Act
        $response = $this->postJson('/api/addresses', [
            'title' => 'Home',
            'address' => 'Street 1',
            'city' => 'Cairo',
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_customer_can_update_their_own_address(): void
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
        $response = $this->actingAs($customer)->putJson('/api/addresses/'.$address->id, [
            'city' => 'Giza',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'city'],
            ])
            ->assertJsonPath('data.city', 'Giza');
    }

    public function test_customer_cannot_update_another_customers_address(): void
    {
        // Arrange
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);
        $address = Address::create([
            'user_id' => $customer1->id,
            'title' => 'Home',
            'address' => 'Street 1',
            'city' => 'Cairo',
            'postal_code' => '12345',
            'country' => 'Egypt',
        ]);

        // Act
        $response = $this->actingAs($customer2)->putJson('/api/addresses/'.$address->id, [
            'city' => 'Giza',
        ]);

        // Assert
        $response->assertStatus(403)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_guest_cannot_update_address(): void
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
        $response = $this->putJson('/api/addresses/'.$address->id, [
            'city' => 'Giza',
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_customer_can_delete_their_own_address(): void
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
        $response = $this->actingAs($customer)->deleteJson('/api/addresses/'.$address->id);

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_customer_cannot_delete_another_customers_address(): void
    {
        // Arrange
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);
        $address = Address::create([
            'user_id' => $customer1->id,
            'title' => 'Home',
            'address' => 'Street 1',
            'city' => 'Cairo',
            'postal_code' => '12345',
            'country' => 'Egypt',
        ]);

        // Act
        $response = $this->actingAs($customer2)->deleteJson('/api/addresses/'.$address->id);

        // Assert
        $response->assertStatus(403)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    public function test_guest_cannot_delete_address(): void
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
        $response = $this->deleteJson('/api/addresses/'.$address->id);

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }
}
