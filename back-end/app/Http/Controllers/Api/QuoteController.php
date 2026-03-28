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
    public function index(Request $request): AnonymousResourceCollection
    {
        $quotes = $request->user()
            ->quotes()
            ->with('client')
            ->latest()
            ->get();

        return QuoteResource::collection($quotes);
    }

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

    public function show(Request $request, int $id): JsonResponse
    {
        $quote = $request->user()
            ->quotes()
            ->with(['client', 'items'])
            ->findOrFail($id);

        return (new QuoteResource($quote))->response();
    }

    public function update(UpdateQuoteRequest $request, int $id): JsonResponse
    {
        $quote = $request->user()
            ->quotes()
            ->findOrFail($id);

        if (! in_array($quote->status, ['draft'])) {
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

    public function destroy(Request $request, int $id): JsonResponse
    {
        $quote = $request->user()
            ->quotes()
            ->findOrFail($id);

        if ($quote->is_converted) {
            return response()->json([
                'message' => 'Cannot delete a quote that has been converted to an invoice.',
            ], 422);
        }

        $quote->delete();

        return response()->json(null, 204);
    }

    public function convert(Request $request, int $id): JsonResponse
    {
        $quote = $request->user()
            ->quotes()
            ->with('items')
            ->findOrFail($id);

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
            'invoice' => $invoice,
        ], 201);
    }

    public function send(Request $request, int $id): JsonResponse
    {
        $quote = $request->user()
            ->quotes()
            ->with('client')
            ->findOrFail($id);

        if (! in_array($quote->status, ['draft', 'sent'])) {
            return response()->json([
                'message' => 'Only draft or sent quotes can be sent.',
            ], 422);
        }

        $quote->update(['status' => 'sent']);

        // ✅ Laravel 13 fix — use WebhookService instead of Http::withoutThrowing()
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
}
