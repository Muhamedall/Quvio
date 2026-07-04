<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;

// ── Public routes (no auth required) ─────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
});

// Stripe webhook — public, verified by signature
Route::post('webhooks/stripe', [InvoiceController::class, 'stripeWebhook']);

// ── Protected routes (Sanctum token required) ─────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get ('auth/me',     [AuthController::class, 'me']);

    // AI — protected 
Route::prefix('ai')->group(function () {
    Route::post('generate-items', [App\Http\Controllers\Api\AiController::class, 'generateItems']);
});
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Clients (still use integer id — not sensitive to enumerate)
    Route::apiResource('clients', ClientController::class);

    // Quotes — use {uuid} in URL for security
    Route::get   ('quotes',                [QuoteController::class, 'index']);
    Route::post  ('quotes',                [QuoteController::class, 'store']);
    Route::get   ('quotes/{uuid}',         [QuoteController::class, 'show']);
    Route::put   ('quotes/{uuid}',         [QuoteController::class, 'update']);
    Route::delete('quotes/{uuid}',         [QuoteController::class, 'destroy']);
    Route::post  ('quotes/{uuid}/send',    [QuoteController::class, 'send']);
    Route::post  ('quotes/{uuid}/convert', [QuoteController::class, 'convert']);
    Route::get   ('quotes/{uuid}/pdf',     [PdfController::class,   'quotePdf']);

    // Invoices — use {uuid} in URL for security
    Route::get   ('invoices',                      [InvoiceController::class, 'index']);
    Route::post  ('invoices',                      [InvoiceController::class, 'store']);
    Route::get   ('invoices/{uuid}',               [InvoiceController::class, 'show']);
    Route::put   ('invoices/{uuid}',               [InvoiceController::class, 'update']);
    Route::delete('invoices/{uuid}',               [InvoiceController::class, 'destroy']);
    Route::post  ('invoices/{uuid}/send',          [InvoiceController::class, 'send']);
    Route::get   ('invoices/{uuid}/pdf',           [InvoiceController::class, 'pdf']);
    Route::post  ('invoices/{uuid}/payment-link',  [InvoiceController::class, 'generatePaymentLink']);

    // Settings
    Route::prefix('settings')->group(function () {
        Route::get ('profile',  [SettingsController::class, 'getProfile']);
        Route::put ('profile',  [SettingsController::class, 'updateProfile']);
        Route::put ('password', [SettingsController::class, 'updatePassword']);
        Route::get ('branding', [SettingsController::class, 'getBranding']);
        Route::put ('branding', [SettingsController::class, 'updateBranding']);
    });
});