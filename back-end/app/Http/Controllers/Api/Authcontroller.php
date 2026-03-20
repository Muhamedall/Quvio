<?php

namespace App\Http\Controllers\Api;

// ============================================================
// AuthController.php  —  Laravel 13
//
// FOUR ENDPOINTS:
//   POST   /api/auth/register  → create account + return token
//   POST   /api/auth/login     → verify credentials + return token
//   POST   /api/auth/logout    → revoke current token
//   GET    /api/auth/me        → return current user data
//
// HOW SANCTUM TOKEN AUTH WORKS:
//   1. User registers or logs in
//   2. Laravel creates a "personal access token" string
//   3. We return it in the JSON response
//   4. Angular stores it in localStorage
//   5. Angular sends it in every request:
//      Authorization: Bearer <token>
//   6. Laravel's auth:sanctum middleware reads the header,
//      finds the token in personal_access_tokens table,
//      loads the user → auth()->user() is now available
//
// RESPONSE FORMAT:
//   We always return the same shape:
//   {
//     "user":  { id, name, email, ... },
//     "token": "1|abc123xyz..."
//   }
//   Angular's AuthService expects exactly this shape.
//
// WHAT IS JsonResponse?
//   Laravel's response()->json() returns a JsonResponse object.
//   It automatically:
//     - Sets Content-Type: application/json header
//     - Converts arrays to JSON
//     - Sets the HTTP status code
// ============================================================

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // ── REGISTER ─────────────────────────────────────────
    // POST /api/auth/register
    //
    // RegisterRequest auto-validates before this runs.
    // If validation fails → 422 JSON error sent automatically.
    // If passes → user is created and token returned.
    public function register(RegisterRequest $request): JsonResponse
    {
        // validated() returns only the fields that passed validation
        // The 'password' cast in the User model auto-hashes it
        // No manual Hash::make() needed in Laravel 13
        $user = User::create($request->validated());

        // Create a Sanctum personal access token
        // 'auth-token' is just a name/label for the token
        // plainTextToken = the actual string Angular will store
        $token = $user->createToken('auth-token')->plainTextToken;

        // Return 201 Created with user + token
        // Angular's AuthResponse interface expects this exact shape
        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    // ── LOGIN ─────────────────────────────────────────────
    // POST /api/auth/login
    //
    // Auth::attempt() checks email + password against the database.
    // It automatically handles bcrypt comparison.
    public function login(LoginRequest $request): JsonResponse
    {
        // attempt() returns true if credentials are correct
        // It loads the user into the auth session internally
        if (! Auth::attempt($request->only('email', 'password'))) {
            // 401 Unauthorized — wrong email or password
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Auth::user() is now available after successful attempt()
        /** @var User $user */
        $user = Auth::user();

        // Revoke all previous tokens for this user
        // This prevents multiple active sessions
        // (remove this line if you want to allow multiple devices)
        $user->tokens()->delete();

        // Create a fresh token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    // ── LOGOUT ───────────────────────────────────────────
    // POST /api/auth/logout
    // Protected by auth:sanctum middleware (see api.php)
    //
    // currentAccessToken() = the token used in THIS request
    // delete() = removes it from personal_access_tokens table
    // After this, that token string is invalid forever
    public function logout(Request $request): JsonResponse
    {
        // Delete only the current token (this device only)
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    // ── ME ───────────────────────────────────────────────
    // GET /api/auth/me
    // Protected by auth:sanctum middleware
    //
    // Returns the currently authenticated user's data.
    // Angular calls this on app startup to restore the session.
    // Also used to refresh user data after profile updates.
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
