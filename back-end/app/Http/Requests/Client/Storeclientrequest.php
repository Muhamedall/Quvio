<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

// ============================================================
// StoreClientRequest.php
// Validates data when CREATING a new client
// POST /api/clients
// ============================================================

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth:sanctum already ensures user is logged in
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ];
    }
}