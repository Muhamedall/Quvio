<?php

// ============================================================
// routes/api.php  —  Laravel 13
//
// ALL API ROUTES live here.
// Automatically prefixed with /api by Laravel.
// So Route::post('auth/register') → POST /api/auth/register
//
// TWO GROUPS:
//
//   PUBLIC — no authentication required
//     POST /api/auth/register
//     POST /api/auth/login
//
//   PROTECTED — requires valid Sanctum token in header
//     Authorization: Bearer <token>
//     The 'auth:sanctum' middleware:
//       1. Reads the Authorization header
//       2. Finds the token in personal_access_tokens table
//       3. Loads the user → auth()->user() is available
//       4. If token missing/invalid → returns 401 automatically
//
// WHAT IS Route::prefix()?
//   Groups routes under a common URL segment.
//   prefix('auth') means all routes inside start with /auth/
//
// WHAT IS Route::middleware()?
//   Applies middleware to all routes in the group.
//   'auth:sanctum' = Sanctum's token authentication middleware
//
// WHAT IS Route::apiResource()?
//   Generates all 5 RESTful routes for a resource:
//     GET    /clients          → index()   (list all)
//     POST   /clients          → store()   (create one)
//     GET    /clients/{client} → show()    (get one)
//     PUT    /clients/{client} → update()  (update one)
//     DELETE /clients/{client} → destroy() (delete one)
//   Skips 'create' and 'edit' routes (those are for web, not API)
// ============================================================

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\QuoteController;
use Illuminate\Support\Facades\Route;

// ════════════════════════════════════════════════════════
// PUBLIC ROUTES — no auth required
// ════════════════════════════════════════════════════════
Route::prefix('auth')->group(function () {

    // POST /api/auth/register → create account
    Route::post('register', [AuthController::class, 'register']);

    // POST /api/auth/login → get token
    Route::post('login', [AuthController::class, 'login']);

});

// ════════════════════════════════════════════════════════
// PROTECTED ROUTES — require valid Sanctum token
// All routes here need: Authorization: Bearer <token>
// ════════════════════════════════════════════════════════
Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ─────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        // POST /api/auth/logout → revoke token
        Route::post('logout', [AuthController::class, 'logout']);

        // GET /api/auth/me → current user data
        Route::get('me', [AuthController::class, 'me']);
    });

    // ── Dashboard ────────────────────────────────────────
    // GET /api/dashboard → revenue stats, counts, recent activity
    Route::get('dashboard', [DashboardController::class, 'index']);

    // ── Clients CRUD ─────────────────────────────────────
    // GET    /api/clients           → list all clients
    // POST   /api/clients           → create a client
    // GET    /api/clients/{client}  → get one client
    // PUT    /api/clients/{client}  → update a client
    // DELETE /api/clients/{client}  → delete a client
    Route::apiResource('clients', ClientController::class);

    // ── Quotes CRUD ──────────────────────────────────────
    Route::apiResource('quotes', QuoteController::class);

    // POST /api/quotes/{quote}/convert → convert quote to invoice
    // Extra action outside the standard CRUD
    Route::post('quotes/{quote}/convert', [QuoteController::class, 'convert']);

    // POST /api/quotes/{quote}/send → mark as sent + fire n8n webhook
    Route::post('quotes/{quote}/send', [QuoteController::class, 'send']);

    // ── Invoices CRUD ────────────────────────────────────
    Route::apiResource('invoices', InvoiceController::class);

    // POST /api/invoices/{invoice}/send → email invoice via n8n
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send']);

    // GET /api/invoices/{invoice}/pdf → download invoice PDF
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf']);

    // POST /api/invoices/{invoice}/payment-link → generate Stripe link
    Route::post('invoices/{invoice}/payment-link', [InvoiceController::class, 'generatePaymentLink']);

});

// ════════════════════════════════════════════════════════
// STRIPE WEBHOOK — no auth (Stripe signs requests differently)
// Stripe sends a POST request to this URL when payment happens
// We verify the Stripe signature instead of Sanctum token
// ════════════════════════════════════════════════════════
Route::post('webhooks/stripe', [InvoiceController::class, 'stripeWebhook']);