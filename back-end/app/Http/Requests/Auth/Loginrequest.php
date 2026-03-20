<?php

namespace App\Http\Requests\Auth;

// ============================================================
// LoginRequest.php
//
// Simple validation for the login form.
// Just checks fields are present and correct format.
// The actual credential check happens in the controller.
// ============================================================

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // anyone can attempt login
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'Email address is required.',
            'email.email'       => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
        ];
    }
}