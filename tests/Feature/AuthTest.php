<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ---Register---

    public function test_user_can_register_successfully()
    {

        $response = $this->postJson('/api/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ]);
        $this->assertDatabaseHas('users', [
            'email' => 'ahmed@example.com',
        ]);
    }

    public function test_register_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors' => ['name', 'email', 'password'],
            ]);
    }

    public function test_register_fails_with_duplicate_email()
    {
        User::factory()->create(['email' => 'ahmed@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_mismatched_password()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    // ---Login---

    public function test_user_can_login_successfully()
    {
        User::factory()->create([
            'email' => 'ahmed@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'ahmed@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user',
                ],
            ]);
    }

    public function test_login_fails_with_wrong_password()
    {
        User::factory()->create([
            'email' => 'ahmed@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'ahmed@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_fails_with_wrong_email()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'notexist@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }
    // ---Logout---

    public function test_user_can_logout_successfully()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200);
    }

    public function test_logout_fails_without_token()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }
}