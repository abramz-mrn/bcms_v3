<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'version' => '1.0.0',
    ]);
});

// Auth routes (public)
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/me', 'me');
    });

    // Users
    Route::apiResource('users', App\Http\Controllers\Api\UserController::class);
    
    // Customers
    Route::apiResource('customers', App\Http\Controllers\Api\CustomerController::class);
    
    // Products
    Route::apiResource('products', App\Http\Controllers\Api\ProductController::class);
    
    // Invoices
    Route::apiResource('invoices', App\Http\Controllers\Api\InvoiceController::class);
    
    // Payments
    Route::apiResource('payments', App\Http\Controllers\Api\PaymentController::class);
    
    // Routers
    Route::apiResource('routers', App\Http\Controllers\Api\RouterController::class);
    Route::post('routers/{router}/test-connection', [App\Http\Controllers\Api\RouterController::class, 'testConnection']);
    
    // Tickets
    Route::apiResource('tickets', App\Http\Controllers\Api\TicketController::class);
});

// Webhooks (no auth required, will be verified inside)
Route::prefix('webhooks')->group(function () {
    Route::post('/midtrans', [App\Http\Controllers\Api\WebhookController::class, 'midtrans']);
    Route::post('/xendit', [App\Http\Controllers\Api\WebhookController::class, 'xendit']);
});
