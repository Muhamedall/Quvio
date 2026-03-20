<?php

namespace App\Http\Controllers\Api;

// ============================================================
// InvoiceController.php  —  Laravel 13
//
// ENDPOINTS:
//   GET    /api/invoices                         → index()
//   POST   /api/invoices                         → store()
//   GET    /api/invoices/{id}                    → show()
//   PUT    /api/invoices/{id}                    → update()
//   DELETE /api/invoices/{id}                    → destroy()
//   POST   /api/invoices/{id}/send               → send()      n8n email
//   GET    /api/invoices/{id}/pdf                → pdf()       DomPDF
//   POST   /api/invoices/{id}/payment-link       → generatePaymentLink() Stripe
//   POST   /api/webhooks/stripe                  → stripeWebhook() Stripe event
// ============================================================

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class InvoiceController extends Controller
{
    // GET /api/invoices
    // List all invoices with client + optional status filter
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->user()
            ->invoices()
            ->with('client')
            ->latest();

        // Optional filter: GET /api/invoices?status=unpaid
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return InvoiceResource::collection($query->get());
    }

    // POST /api/invoices
    // Create an invoice directly (not from a quote)
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = DB::transaction(function () use ($request) {

            $invoice = Invoice::create([
                'user_id'        => $request->user()->id,
                'client_id'      => $request->client_id,
                'quote_id'       => $request->quote_id,
                'invoice_number' => Invoice::generateNumber($request->user()->id),
                'status'         => 'unpaid',
                'tax_rate'       => $request->tax_rate ?? 0,
                'subtotal'       => 0,
                'total'          => 0,
                'due_date'       => $request->due_date,
                'notes'          => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'subtotal'    => 0, // booted() calculates this
                ]);
            }

            return $invoice;
        });

        $invoice->load(['client', 'items']);

        return (new InvoiceResource($invoice))
            ->response()
            ->setStatusCode(201);
    }

    // GET /api/invoices/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $invoice = $request->user()
            ->invoices()
            ->with(['client', 'items', 'quote'])
            ->findOrFail($id);

        return (new InvoiceResource($invoice))->response();
    }

    // PUT /api/invoices/{id}
    // Can only update unpaid invoices
    public function update(UpdateInvoiceRequest $request, int $id): JsonResponse
    {
        $invoice = $request->user()
            ->invoices()
            ->findOrFail($id);

        if ($invoice->status === 'paid') {
            return response()->json([
                'message' => 'Paid invoices cannot be modified.',
            ], 422);
        }

        $invoice = DB::transaction(function () use ($request, $invoice) {

            $invoice->update($request->only([
                'client_id', 'tax_rate', 'due_date', 'notes',
            ]));

            if ($request->has('items')) {
                $invoice->items()->delete();

                foreach ($request->items as $item) {
                    $invoice->items()->create([
                        'description' => $item['description'],
                        'quantity'    => $item['quantity'],
                        'unit_price'  => $item['unit_price'],
                        'subtotal'    => 0,
                    ]);
                }
            }

            return $invoice;
        });

        $invoice->load(['client', 'items']);

        return (new InvoiceResource($invoice))->response();
    }

    // DELETE /api/invoices/{id}
    public function destroy(Request $request, int $id): JsonResponse
    {
        $invoice = $request->user()
            ->invoices()
            ->findOrFail($id);

        if ($invoice->status === 'paid') {
            return response()->json([
                'message' => 'Paid invoices cannot be deleted.',
            ], 422);
        }

        $invoice->delete();

        return response()->json(null, 204);
    }

    // POST /api/invoices/{id}/send
    // Email the invoice to the client via n8n webhook
    public function send(Request $request, int $id): JsonResponse
    {
        $invoice = $request->user()
            ->invoices()
            ->with('client')
            ->findOrFail($id);

        $webhookUrl = config('services.n8n.invoice_created_url');

        if ($webhookUrl) {
            Http::withoutThrowing()->post($webhookUrl, [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_name'    => $invoice->client->name,
                'client_email'   => $invoice->client->email,
                'total'          => $invoice->total,
                'due_date'       => $invoice->due_date->toDateString(),
                'stripe_link'    => $invoice->stripe_link,
            ]);
        }

        return response()->json([
            'message' => 'Invoice sent successfully.',
        ]);
    }

    // GET /api/invoices/{id}/pdf
    // Generate and return invoice PDF using DomPDF
    public function pdf(Request $request, int $id): Response
    {
        $invoice = $request->user()
            ->invoices()
            ->with(['client', 'items', 'quote'])
            ->findOrFail($id);

        // Pdf::loadView() renders a Blade template → PDF
        // The template lives at resources/views/pdf/invoice.blade.php
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'user'    => $request->user(),
        ]);

        // download() = browser downloads the file
        // stream() = browser opens PDF in a new tab
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    // POST /api/invoices/{id}/payment-link
    // Create a Stripe Payment Link and attach it to the invoice
    public function generatePaymentLink(Request $request, int $id): JsonResponse
    {
        $invoice = $request->user()
            ->invoices()
            ->with('client')
            ->findOrFail($id);

        if ($invoice->status === 'paid') {
            return response()->json([
                'message' => 'Invoice is already paid.',
            ], 422);
        }

        // Initialize Stripe client with secret key from config
        $stripe = new StripeClient(config('services.stripe.secret'));

        // Step 1: Create a Stripe Price (amount in cents, not euros)
        // Stripe always works in the smallest currency unit
        // €150.00 → 15000 cents
        $price = $stripe->prices->create([
            'unit_amount' => (int) ($invoice->total * 100),
            'currency'    => 'eur',
            'product_data' => [
                'name' => "Invoice {$invoice->invoice_number}",
            ],
        ]);

        // Step 2: Create a Payment Link from the price
        $paymentLink = $stripe->paymentLinks->create([
            'line_items' => [[
                'price'    => $price->id,
                'quantity' => 1,
            ]],
            // Metadata lets us find this invoice in the webhook
            'metadata' => [
                'invoice_id' => $invoice->id,
            ],
        ]);

        // Step 3: Save the link to the invoice
        $invoice->update([
            'stripe_link' => $paymentLink->url,
        ]);

        return response()->json([
            'stripe_link' => $paymentLink->url,
            'message'     => 'Payment link generated successfully.',
        ]);
    }

    // POST /api/webhooks/stripe
    // Stripe calls this URL when a payment is completed
    // This route is PUBLIC — no auth:sanctum middleware
    // But we verify Stripe's signature to prevent fake requests
    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        // Verify the request actually came from Stripe
        // If signature is wrong → throws SignatureVerificationException
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed.', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        // Handle the payment_intent.succeeded event
        // This fires when the client successfully pays
        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;

            // Find the invoice by Stripe's payment intent ID
            // or by metadata we set when creating the payment link
            $invoice = Invoice::where(
                'stripe_payment_intent_id',
                $paymentIntent->id
            )->first();

            if ($invoice) {
                // Mark the invoice as paid — method on the Invoice model
                $invoice->markAsPaid($paymentIntent->id);

                // Fire n8n webhook to notify the user (optional)
                $webhookUrl = config('services.n8n.invoice_paid_url');
                if ($webhookUrl) {
                    Http::withoutThrowing()->post($webhookUrl, [
                        'invoice_id'     => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'amount'         => $invoice->total,
                    ]);
                }
            }
        }

        // Always return 200 to Stripe, even if we didn't process the event
        // Otherwise Stripe will retry the webhook repeatedly
        return response()->json(['message' => 'Webhook received.']);
    }
}
