<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Auth
 * 
 * Endpoints for user authentication.
 */
class AuthController extends Controller
{
    /**
     * Register
     * 
     * Creates a new customer account and returns an access token.
     * 
     * @unauthenticated
     */
    public function register(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer'
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id'=> $user->id,
                'name'=> $user->name,
                'email'=> $user->email,
                'role' => $user->role,
                ],
            ],'Registration successful',201);

    }

    /**
     * Login
     * 
     * Authenticates a user and returns an access token.
     * 
     * @unauthenticated
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('The provided credentials are incorrect.', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role'=> $user->role,
            ],
        ],'Login successful');
    }

    /**
     * Logout
     * 
     * Revokes the current access token.
     */
    public function logout(Request $request) 
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return $this->success(null, 'Logout successful');
    }
}