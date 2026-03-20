<?php

namespace App\Http\Requests\Auth;

// ============================================================
// RegisterRequest.php
//
// WHY FORM REQUESTS INSTEAD OF VALIDATING IN CONTROLLER?
//
//   WITHOUT Form Request (messy controller):
//     public function register(Request $request) {
//         $request->validate([...rules...]);
//         // controller is now doing validation + business logic
//     }
//
//   WITH Form Request (clean controller):
//     public function register(RegisterRequest $request) {
//         // Laravel auto-validates BEFORE the method runs
//         // If validation fails → 422 response sent automatically
//         // Controller only runs if validation passes ✅
//     }
//
//   Benefits:
//   - Controller stays clean and focused on business logic
//   - Validation rules are reusable and testable
//   - Error messages are consistent and automatic
//
// HOW IT WORKS IN LARAVEL 13:
//   1. Route hit → Laravel sees RegisterRequest type hint
//   2. authorize() runs → returns true (anyone can register)
//   3. rules() runs → validates the request data
//   4. If fails → 422 JSON response sent automatically
//   5. If passes → controller method runs with validated data
// ============================================================

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    // authorize() decides if the current user CAN make this request
    // For registration: anyone can register → always return true
    // For other requests: you could check roles/permissions here
    public function authorize(): bool
    {
        return true;
    }

    // rules() defines the validation rules for each field
    public function rules(): array
    {
        return [
            // 'name' must exist and be a string, max 255 chars
            'name'     => ['required', 'string', 'max:255'],

            // 'email' must be a valid email format,
            // unique:users = no duplicate emails in the users table
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],

            // 'password' must be at least 8 chars
            // confirmed = must match 'password_confirmation' field
            // This is the Laravel convention — Angular sends both fields
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    // messages() customizes the error messages (optional but cleaner)
    public function messages(): array
    {
        return [
            'email.unique'        => 'This email is already registered.',
            'password.confirmed'  => 'Passwords do not match.',
            'password.min'        => 'Password must be at least 8 characters.',
        ];
    }
}