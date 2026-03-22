<?php

// ============================================================
// config/services.php
//
// Third-party service credentials and URLs.
// All values come from .env — never hardcode here.
//
// Access in controllers:
//   config('services.stripe.secret')
//   config('services.n8n.invoice_created_url')
// ============================================================

return [

    // ── Laravel defaults (keep these) ────────────────────
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ── Stripe ───────────────────────────────────────────
    'stripe' => [
        'key'            => env('STRIPE_KEY'),            // pk_test_...
        'secret'         => env('STRIPE_SECRET'),         // sk_test_...
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'), // whsec_...
    ],

    // ── n8n Webhooks ──────────────────────────────────────
    // Each webhook URL corresponds to an automation workflow in n8n.
    // Set these in .env after setting up your n8n workflows.
    'n8n' => [
        // Fires when a quote is sent to a client
        'quote_created_url'   => env('N8N_QUOTE_CREATED_URL'),

        // Fires when a quote is approved by client
        'quote_approved_url'  => env('N8N_QUOTE_APPROVED_URL'),

        // Fires when an invoice is created/sent
        'invoice_created_url' => env('N8N_INVOICE_CREATED_URL'),

        // Fires when an invoice is paid (Stripe webhook → this)
        'invoice_paid_url'    => env('N8N_INVOICE_PAID_URL'),

        // Fires when an invoice becomes overdue (scheduled job)
        'invoice_overdue_url' => env('N8N_INVOICE_OVERDUE_URL'),
    ],

];
