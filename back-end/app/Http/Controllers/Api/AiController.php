<?php

namespace App\Http\Controllers\Api;

// ============================================================
// AiController.php
//
// ENDPOINT:
//   POST /api/ai/generate-items
//
// REQUEST BODY:
//   { "description": "React development 3 days", "currency": "EUR" }
//
// RESPONSE:
//   {
//     "items": [
//       { "description": "Frontend Development (React)", "quantity": 3, "unit_price": 800, "subtotal": 2400 },
//       ...
//     ]
//   }
//
// RATE LIMIT: 10 requests/minute per user (protects Gemini free tier)
// ============================================================

use App\Http\Controllers\Controller;
use App\Services\AiInvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function generateItems(Request $request): JsonResponse
    {
        // Validate input
        $request->validate([
            'description' => ['required', 'string', 'min:3', 'max:300'],
            'currency'    => ['nullable', 'string', 'in:EUR,USD,GBP,MAD'],
        ]);

        $description = trim($request->input('description'));
        $currency    = $request->input('currency', 'EUR');

        try {
            $items = AiInvoiceService::generateItems($description, $currency);

            return response()->json([
                'items'   => $items,
                'message' => 'Items generated successfully.',
            ]);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'items'   => [],
            ], 503); // 503 = service temporarily unavailable
        }
    }
}