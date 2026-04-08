<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quote\StoreQuoteRequest;
use App\Http\Requests\Quote\UpdateQuoteRequest;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    // ── Helper: find quote by UUID scoped to user ─────────
    private function findQuote(Request $request, string $uuid): Quote
    {
        return $request->user()
            ->quotes()
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    // GET /api/quotes
    public function index(Request $request): AnonymousResourceCollection
    {
        $quotes = $request->user()
            ->quotes()
            ->with('client')
            ->latest()
            ->get();

        return QuoteResource::collection($quotes);
    }

    // POST /api/quotes
    public function store(StoreQuoteRequest $request): JsonResponse
    {
        $quote = DB::transaction(function () use ($request) {
            $quote = Quote::create([
                'user_id'      => $request->user()->id,
                'client_id'    => $request->client_id,
                'quote_number' => Quote::generateNumber($request->user()->id),
                'status'       => 'draft',
                'tax_rate'     => $request->tax_rate ?? 0,
                'subtotal'     => 0,
                'total'        => 0,
                'notes'        => $request->notes,
                'valid_until'  => $request->valid_until,
            ]);

            foreach ($request->items as $item) {
                $quote->items()->create([
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'subtotal'    => 0,
                ]);
            }

            return $quote;
        });

        $quote->load(['client', 'items']);

        return (new QuoteResource($quote))
            ->response()
            ->setStatusCode(201);
    }

    // GET /api/quotes/{uuid}
    public function show(Request $request, string $uuid): JsonResponse
    {
        $quote = $request->user()
            ->quotes()
            ->with(['client', 'items'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return (new QuoteResource($quote))->response();
    }

    // PUT /api/quotes/{uuid}
    public function update(UpdateQuoteRequest $request, string $uuid): JsonResponse
    {
        $quote = $this->findQuote($request, $uuid);

        if ($quote->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft quotes can be edited.',
            ], 422);
        }

        $quote = DB::transaction(function () use ($request, $quote) {
            $quote->update($request->only([
                'client_id', 'tax_rate', 'notes', 'valid_until',
            ]));

            if ($request->has('items')) {
                $quote->items()->delete();
                foreach ($request->items as $item) {
                    $quote->items()->create([
                        'description' => $item['description'],
                        'quantity'    => $item['quantity'],
                        'unit_price'  => $item['unit_price'],
                        'subtotal'    => 0,
                    ]);
                }
            }

            return $quote;
        });

        $quote->load(['client', 'items']);

        return (new QuoteResource($quote))->response();
    }

    // DELETE /api/quotes/{uuid}
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $quote = $this->findQuote($request, $uuid);

        if ($quote->is_converted) {
            return response()->json([
                'message' => 'Cannot delete a converted quote.',
            ], 422);
        }

        $quote->delete();

        return response()->json(null, 204);
    }

    // POST /api/quotes/{uuid}/send
    public function send(Request $request, string $uuid): JsonResponse
    {
        $quote = $request->user()
            ->quotes()
            ->with('client')
            ->where('uuid', $uuid)
            ->firstOrFail();

        if (! in_array($quote->status, ['draft', 'sent'])) {
            return response()->json([
                'message' => 'Only draft or sent quotes can be sent.',
            ], 422);
        }

        $quote->update(['status' => 'sent']);

        WebhookService::fire(config('services.n8n.quote_created_url'), [
            'quote_id'     => $quote->id,
            'quote_number' => $quote->quote_number,
            'client_name'  => $quote->client->name,
            'client_email' => $quote->client->email,
            'company_name' => $request->user()->branding['company_name']
                              ?? $request->user()->name,
            'total'        => number_format((float) $quote->total, 2, '.', ''),
            'valid_until'  => $quote->valid_until
                              ? $quote->valid_until->toDateString()
                              : 'No expiry',
        ]);

        $quote->load(['client', 'items']);

        return response()->json([
            'message' => 'Quote sent successfully.',
            'quote'   => new QuoteResource($quote),
        ]);
    }

    // POST /api/quotes/{uuid}/convert
    public function convert(Request $request, string $uuid): JsonResponse
    {
        $quote = $request->user()
            ->quotes()
            ->with('items')
            ->where('uuid', $uuid)
            ->firstOrFail();

        if (! in_array($quote->status, ['sent', 'approved', 'draft'])) {
            return response()->json([
                'message' => 'This quote cannot be converted.',
            ], 422);
        }

        if ($quote->is_converted) {
            return response()->json([
                'message' => 'This quote has already been converted to an invoice.',
            ], 422);
        }

        $invoice = $quote->convertToInvoice();

        return response()->json([
            'message' => 'Quote converted to invoice successfully.',
            'invoice' => [
                'id'   => $invoice->id,
                'uuid' => $invoice->uuid,
            ],
        ], 201);
    }
}
