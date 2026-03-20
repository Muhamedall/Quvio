<?php

namespace App\Http\Controllers\Api;

// ============================================================
// QuoteController.php  —  Laravel 13
//
// ENDPOINTS:
//   GET    /api/quotes              → index()   list all
//   POST   /api/quotes              → store()   create with items
//   GET    /api/quotes/{id}         → show()    get one with items
//   PUT    /api/quotes/{id}         → update()  update + sync items
//   DELETE /api/quotes/{id}         → destroy()
//   POST   /api/quotes/{id}/convert → convert() → creates Invoice
//   POST   /api/quotes/{id}/send    → send()    → fires n8n webhook
//
// KEY PATTERN — SYNC ITEMS:
//   When updating a quote's items, we:
//   1. Delete all existing items for this quote
//   2. Create the new items from the request
//   This is simpler than diffing which items changed/added/removed.
//   The model's booted() hook recalculates totals automatically.
// ============================================================

use App\Http\Controllers\Controller;
use App\Http\Requests\Quote\StoreQuoteRequest;
use App\Http\Requests\Quote\UpdateQuoteRequest;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class QuoteController extends Controller
{
    // GET /api/quotes
    // List all quotes with client name (for the table display)
    public function index(Request $request): AnonymousResourceCollection
    {
        $quotes = $request->user()
            ->quotes()
            ->with('client')    // eager load client — avoids N+1
            ->latest()          // newest first
            ->get();

        return QuoteResource::collection($quotes);
    }

    // POST /api/quotes
    // Create a quote + its items in one transaction
    public function store(StoreQuoteRequest $request): JsonResponse
    {
        // DB::transaction() = if anything fails, everything rolls back
        // No partial data in the database
        $quote = DB::transaction(function () use ($request) {

            // Step 1: Create the quote
            $quote = Quote::create([
                'user_id'      => $request->user()->id,
                'client_id'    => $request->client_id,
                'quote_number' => Quote::generateNumber($request->user()->id),
                'status'       => 'draft',
                'tax_rate'     => $request->tax_rate ?? 0,
                'subtotal'     => 0, // will be recalculated after items
                'total'        => 0,
                'notes'        => $request->notes,
                'valid_until'  => $request->valid_until,
            ]);

            // Step 2: Create each item
            // The InvoiceItem booted() hook auto-calculates subtotal
            // and calls quote->recalculateTotals() after each save
            foreach ($request->items as $item) {
                $quote->items()->create([
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'subtotal'    => 0, // booted() will set this
                ]);
            }

            return $quote;
        });

        // Load relationships before returning
        $quote->load(['client', 'items']);

        return (new QuoteResource($quote))
            ->response()
            ->setStatusCode(201);
    }

    // GET /api/quotes/{id}
    // Get one quote with all details: client + items
    public function show(Request $request, int $id): JsonResponse
    {
        $quote = $request->user()
            ->quotes()
            ->with(['client', 'items'])
            ->findOrFail($id);

        return (new QuoteResource($quote))->response();
    }

    // PUT /api/quotes/{id}
    // Update quote — replace all items (sync pattern)
    public function update(UpdateQuoteRequest $request, int $id): JsonResponse
    {
        $quote = $request->user()
            ->quotes()
            ->findOrFail($id);

        // Only draft quotes can be edited
        // Sent/approved/rejected quotes are locked
        if (! in_array($quote->status, ['draft'])) {
            return response()->json([
                'message' => 'Only draft quotes can be edited.',
            ], 422);
        }

        $quote = DB::transaction(function () use ($request, $quote) {

            // Update quote fields
            $quote->update($request->only([
                'client_id', 'tax_rate', 'notes', 'valid_until',
            ]));

            // Sync items only if items were provided in the request
            if ($request->has('items')) {
                // Delete all existing items for this quote
                $quote->items()->delete();

                // Create the new items
                foreach ($request->items as $item) {
                    $quote->items()->create([
                        'description' => $item['description'],
                        'quantity'    => $item['quantity'],
                        'unit_price'  => $item['unit_price'],
                        'subtotal'    => 0, // booted() calculates this
                    ]);
                }
            }

            return $quote;
        });

        $quote->load(['client', 'items']);

        return (new QuoteResource($quote))->response();
    }

    // DELETE /api/quotes/{id}
    public function destroy(Request $request, int $id): JsonResponse
    {
        $quote = $request->user()
            ->quotes()
            ->findOrFail($id);

        // Prevent deleting if already converted to invoice
        if ($quote->is_converted) {
            return response()->json([
                'message' => 'Cannot delete a quote that has been converted to an invoice.',
            ], 422);
        }

        $quote->delete(); // cascades to items (see migration)

        return response()->json(null, 204);
    }

    // POST /api/quotes/{id}/convert
    // Convert an approved or sent quote into an Invoice
    // The convertToInvoice() method lives on the Quote model
    public function convert(Request $request, int $id): JsonResponse
    {
        $quote = $request->user()
            ->quotes()
            ->with('items')  // need items to copy to invoice
            ->findOrFail($id);

        // Can only convert sent or approved quotes
        if (! in_array($quote->status, ['sent', 'approved', 'draft'])) {
            return response()->json([
                'message' => 'This quote cannot be converted.',
            ], 422);
        }

        // Already converted?
        if ($quote->is_converted) {
            return response()->json([
                'message' => 'This quote has already been converted to an invoice.',
            ], 422);
        }

        // The model method handles everything — creates invoice + copies items
        $invoice = $quote->convertToInvoice();

        return response()->json([
            'message' => 'Quote converted to invoice successfully.',
            'invoice' => $invoice,
        ], 201);
    }

    // POST /api/quotes/{id}/send
    // Mark quote as 'sent' and fire n8n webhook to email it to client
    public function send(Request $request, int $id): JsonResponse
    {
        $quote = $request->user()
            ->quotes()
            ->with('client')
            ->findOrFail($id);

        if ($quote->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft quotes can be sent.',
            ], 422);
        }

        // Update status to sent
        $quote->update(['status' => 'sent']);

        // Fire n8n webhook — sends quote PDF by email to client
        // We use Http::post() which is Laravel's HTTP client
        // withoutThrowing() = don't fail if n8n is down
        $webhookUrl = config('services.n8n.quote_created_url');

        if ($webhookUrl) {
            Http::withoutThrowing()->post($webhookUrl, [
                'quote_id'     => $quote->id,
                'quote_number' => $quote->quote_number,
                'client_name'  => $quote->client->name,
                'client_email' => $quote->client->email,
                'total'        => $quote->total,
                'valid_until'  => $quote->valid_until?->toDateString(),
            ]);
        }

        return response()->json([
            'message' => 'Quote sent successfully.',
            'quote'   => new QuoteResource($quote),
        ]);
    }
}
