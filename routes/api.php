<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\EmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Catalog Service Routes
Route::prefix('catalog')->group(function () {
    Route::get('/products', [CatalogController::class, 'index']);
    Route::get('/products/{id}', [CatalogController::class, 'show']);
    Route::get('/categories', [CatalogController::class, 'categories']);
});

// Checkout Service Routes
Route::prefix('checkout')->group(function () {
    Route::get('/orders', [CheckoutController::class, 'index']);
    Route::post('/orders', [CheckoutController::class, 'createOrder']);
    Route::get('/orders/{orderNumber}', [CheckoutController::class, 'getOrder']);
    Route::patch('/orders/{orderNumber}/status', [CheckoutController::class, 'updateOrderStatus']);
});

// Email Service Routes
Route::prefix('email')->group(function () {
    Route::post('/order-confirmation', [EmailController::class, 'sendOrderConfirmation']);
    Route::post('/order-confirmation-by-number', [EmailController::class, 'sendOrderConfirmationByNumber']);
    Route::post('/custom-notification', [EmailController::class, 'sendCustomNotification']);
});
