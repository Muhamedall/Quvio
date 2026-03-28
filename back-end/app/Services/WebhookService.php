<?php

namespace App\Services;

// ============================================================
// WebhookService.php  —  Laravel 13
//
// WHY THIS EXISTS:
//   Http::withoutThrowing() was REMOVED in Laravel 13.
//   Use this service everywhere you fire n8n webhooks.
//
// USAGE:
//   use App\Services\WebhookService;
//   WebhookService::fire(config('services.n8n.quote_created_url'), [...]);
// ============================================================

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public static function fire(?string $url, array $data): void
    {
        if (empty($url)) return;

        try {
            Http::timeout(5)->post($url, $data);
        } catch (\Throwable $e) {
            Log::warning('Webhook failed — n8n may be down.', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
