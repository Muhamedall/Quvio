<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\WebhookService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class InvoiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->user()
            ->invoices()
            ->with('client')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return InvoiceResource::collection($query->get());
    }

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
                    'subtotal'    => 0,
                ]);
            }

            return $invoice;
        });

        $invoice->load(['client', 'items']);

        return (new InvoiceResource($invoice))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $invoice = $request->user()
            ->invoices()
            ->with(['client', 'items', 'quote'])
            ->findOrFail($id);

        return (new InvoiceResource($invoice))->response();
    }

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

    public function send(Request $request, int $id): JsonResponse
    {
        $invoice = $request->user()
            ->invoices()
            ->with('client')
            ->findOrFail($id);

        WebhookService::fire(config('services.n8n.invoice_created_url'), [
            'invoice_id'     => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'client_name'    => $invoice->client->name,
            'client_email'   => $invoice->client->email,
            'company_name'   => $request->user()->branding['company_name'] ?? $request->user()->name,
            'total'          => number_format((float) $invoice->total, 2, '.', ''),
            'due_date'       => $invoice->due_date->toDateString(),
            'stripe_link'    => $invoice->stripe_link ?? '',
        ]);

        return response()->json([
            'message' => 'Invoice sent successfully.',
        ]);
    }

    public function pdf(Request $request, int $id): Response
    {
        $invoice = $request->user()
            ->invoices()
            ->with(['client', 'items', 'quote'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'user'    => $request->user(),
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isRemoteEnabled', false)
        ->setOption('defaultFont', 'sans-serif')
        ->setOption('dpi', 150);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

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

        $stripe = new StripeClient(config('services.stripe.secret'));

        $price = $stripe->prices->create([
            'unit_amount'  => (int) ($invoice->total * 100),
            'currency'     => 'eur',
            'product_data' => [
                'name' => "Invoice {$invoice->invoice_number}",
            ],
        ]);

        $paymentLink = $stripe->paymentLinks->create([
            'line_items' => [[
                'price'    => $price->id,
                'quantity' => 1,
            ]],
            'metadata' => [
                'invoice_id' => $invoice->id,
            ],
        ]);

        $invoice->update(['stripe_link' => $paymentLink->url]);

        return response()->json([
            'stripe_link' => $paymentLink->url,
            'message'     => 'Payment link generated successfully.',
        ]);
    }

    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed.', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        // ✅ Support checkout.session.completed instead of payment_intent.succeeded
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $invoiceId = $session->metadata->invoice_id ?? null;

            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice) {
                    $invoice->markAsPaid($session->payment_intent);

                    WebhookService::fire(config('services.n8n.invoice_paid_url'), [
                        'invoice_id'     => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'amount'         => $invoice->total,
                    ]);
                } else {
                    Log::warning('Invoice not found for webhook', ['invoice_id' => $invoiceId]);
                }
            } else {
                Log::warning('Invoice ID missing in Stripe metadata');
            }
        }

        return response()->json(['message' => 'Webhook received.']);
    }
}