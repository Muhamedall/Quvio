<?php

namespace App\Http\Requests\Quote;

use Illuminate\Foundation\Http\FormRequest;

// ============================================================
// StoreQuoteRequest.php
// POST /api/quotes
//
// A quote is sent with:
//   - client_id, tax_rate, notes, valid_until
//   - items[] array of line items
//
// ARRAY VALIDATION in Laravel:
//   'items'             = the items array must exist and have items
//   'items.*'           = each element in the array
//   'items.*.description' = the description field of each item
//   'items.*.quantity'    = quantity of each item
//   'items.*.unit_price'  = price of each item
// ============================================================

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'   => ['required', 'integer', 'exists:clients,id'],
            'tax_rate'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'       => ['nullable', 'string'],
            'valid_until' => ['nullable', 'date', 'after:today'],

            // Items array — must have at least 1 item
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.description'    => ['required', 'string', 'max:500'],
            'items.*.quantity'       => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'     => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.exists'           => 'Selected client does not exist.',
            'items.required'             => 'At least one item is required.',
            'items.min'                  => 'At least one item is required.',
            'items.*.description.required' => 'Each item must have a description.',
            'items.*.quantity.required'    => 'Each item must have a quantity.',
            'items.*.unit_price.required'  => 'Each item must have a price.',
        ];
    }
}