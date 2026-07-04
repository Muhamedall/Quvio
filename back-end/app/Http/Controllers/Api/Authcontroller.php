<?php

namespace App\Http\Controllers\Api;


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
   
    public function register(RegisterRequest $request): JsonResponse
    {
       
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
