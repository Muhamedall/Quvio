<?php

namespace App\Http\Controllers\Api;

// ============================================================
// SettingsController.php  —  Laravel 13
//
// ENDPOINTS:
//   GET    /api/settings/profile          → get current user profile
//   PUT    /api/settings/profile          → update name + email
//   PUT    /api/settings/password         → change password
//   GET    /api/settings/branding         → get branding settings
//   PUT    /api/settings/branding         → update branding
//
// BRANDING is stored in the users table as JSON column.
// It contains: company_name, company_address, company_phone,
//              company_email, company_website, invoice_notes
// These are used on the invoice/quote PDF header.
// ============================================================

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    // GET /api/settings/profile
    public function getProfile(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->only([
                'id', 'name', 'email', 'created_at',
            ]),
        ]);
    }

    // PUT /api/settings/profile
    // Update name and/or email
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user'    => $user->only(['id', 'name', 'email']),
        ]);
    }

    // PUT /api/settings/password
    // Change password — requires current password verification
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();

        // Verify current password
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
                'errors'  => ['current_password' => ['Current password is incorrect.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    // GET /api/settings/branding
    // Returns branding settings stored as JSON on the user
    public function getBranding(Request $request): JsonResponse
    {
        $user     = $request->user();
        $branding = $user->branding ?? [];

        return response()->json([
            'branding' => array_merge([
                'company_name'    => '',
                'company_address' => '',
                'company_phone'   => '',
                'company_email'   => '',
                'company_website' => '',
                'invoice_notes'   => 'Thank you for your business.',
                'invoice_prefix'  => 'INV',
                'quote_prefix'    => 'QUO',
            ], $branding),
        ]);
    }

    // PUT /api/settings/branding
    public function updateBranding(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name'    => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_phone'   => ['nullable', 'string', 'max:50'],
            'company_email'   => ['nullable', 'email',  'max:255'],
            'company_website' => ['nullable', 'string', 'max:255'],
            'invoice_notes'   => ['nullable', 'string', 'max:1000'],
            'invoice_prefix'  => ['nullable', 'string', 'max:10'],
            'quote_prefix'    => ['nullable', 'string', 'max:10'],
        ]);

        $request->user()->update(['branding' => $validated]);

        return response()->json([
            'message'  => 'Branding updated successfully.',
            'branding' => $validated,
        ]);
    }
}