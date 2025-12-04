<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\AddressController;

// These routes are PUBLIC
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// These routes are PROTECTED
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Product routes
    Route::apiResource('products', ProductController::class);

    // Category routes
    Route::apiResource('categories', CategoryController::class);

    // Cart routes
    Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'index']);
    Route::post('/cart', [\App\Http\Controllers\Api\CartController::class, 'store']);
    Route::delete('/cart/{cartItem}', [\App\Http\Controllers\Api\CartController::class, 'destroy']);

    // Order routes
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'index']);
    
    // User Profile routes
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
});

// === ADMIN ROUTES ===
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Admin Order routes
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updatestatus']);
});

