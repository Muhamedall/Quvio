<?php

namespace App\Services;

// ============================================================
// AiInvoiceService.php
//
// Uses Google Gemini (free tier) to generate professional
// invoice line items from a short description.
//
// MODEL: gemini-2.5-flash
//   - Free tier: 1,500 requests/day, no credit card
//   - Returns JSON → we parse and return structured items
//
// USAGE:
//   $items = AiInvoiceService::generateItems('React dev 3 days', 'EUR');
//   // returns array of [description, quantity, unit_price, subtotal]
// ============================================================

use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;

class AiInvoiceService
{
    // Main method — called by AiController
    public static function generateItems(
        string $description,
        string $currency  = 'EUR',
        int    $maxItems  = 4
    ): array {
        $prompt = self::buildPrompt($description, $currency, $maxItems);

        try {
            // ── Call Gemini 2.5 Flash (free tier) ────────────
            // generativeModel() is the correct v2.0 API
            // geminiFlash() was REMOVED in package v2.0
            $result = Gemini::generativeModel(model: 'gemini-2.5-flash')
                ->generateContent($prompt);

            $raw = $result->text();

            return self::parseResponse($raw);

        } catch (\Throwable $e) {
            Log::error('Gemini AI error', [
                'message'     => $e->getMessage(),
                'description' => $description,
            ]);

            // Never crash the app if AI fails — return empty array
            // Angular will show a friendly error message
            throw new \RuntimeException(
                'AI service temporarily unavailable. Please try again.'
            );
        }
    }

    // ── Build the prompt ──────────────────────────────────
    // Clear, structured prompt = better JSON output from Gemini
    private static function buildPrompt(
        string $description,
        string $currency,
        int    $maxItems
    ): string {
        return <<<PROMPT
You are a professional freelance invoice assistant.

The user described a project or service:
"{$description}"

Generate {$maxItems} professional invoice line items for this work.
Use realistic freelance market rates in {$currency}.

RULES:
- Return ONLY a valid JSON array, no explanation, no markdown, no backticks
- Each item must have exactly these fields: description, quantity, unit_price
- description: professional service name (max 60 chars, in English)
- quantity: realistic number (hours, days, units) as a number
- unit_price: realistic market rate as a number (no currency symbol)
- Think like an experienced freelancer pricing their services
- Break down the work into logical billable items

EXAMPLE OUTPUT FORMAT:
[
  {"description": "UI/UX Design & Wireframes", "quantity": 1, "unit_price": 1200},
  {"description": "Frontend Development (React)", "quantity": 3, "unit_price": 800},
  {"description": "Code Review & Testing", "quantity": 4, "unit_price": 100},
  {"description": "Technical Documentation", "quantity": 1, "unit_price": 300}
]

Now generate items for: "{$description}"
Return ONLY the JSON array:
PROMPT;
    }

    // ── Parse the Gemini response ─────────────────────────
    // Gemini sometimes wraps JSON in ```json ... ``` — strip that
    private static function parseResponse(string $raw): array
    {
        // Remove markdown code fences if present
        $clean = preg_replace('/```(?:json)?\s*/i', '', $raw);
        $clean = preg_replace('/```\s*/i', '', $clean);
        $clean = trim($clean);

        $items = json_decode($clean, true);

        if (! is_array($items) || empty($items)) {
            throw new \RuntimeException('AI returned invalid response. Please try again.');
        }

        // Validate and sanitize each item
        return array_map(function (array $item): array {
            $qty   = max(0.5, (float) ($item['quantity']   ?? 1));
            $price = max(0,   (float) ($item['unit_price'] ?? 0));

            return [
                'description' => substr(trim($item['description'] ?? 'Service'), 0, 60),
                'quantity'    => $qty,
                'unit_price'  => $price,
                'subtotal'    => round($qty * $price, 2),
            ];
        }, array_slice($items, 0, 6)); // max 6 items
    }
}