<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'quote_number' => $this->quote_number,  // QUO-2025-001
            'status'       => $this->status,         // draft|sent|approved|rejected
            'status_label' => $this->status_label,   // "Draft", "Sent"...

            // Amounts — cast to float by the model
            'subtotal'     => (float) $this->subtotal,
            'tax_rate'     => (float) $this->tax_rate,
            'total'        => (float) $this->total,

            'notes'        => $this->notes,
            'valid_until'  => $this->valid_until?->toDateString(),  // nullable date

            // Computed — from model accessor
            'is_converted' => $this->is_converted,  // bool: has an invoice?

            // Nested: client data — only if relation was loaded
            // In index(): we don't load client (too many queries)
            // In show(): we load client (need full details)
            'client'       => new ClientResource($this->whenLoaded('client')),

            // Nested: line items — only if loaded
            'items'        => InvoiceItemResource::collection(
                                $this->whenLoaded('items')
                              ),

            'created_at'   => $this->created_at->toDateString(),
            'updated_at'   => $this->updated_at->toDateString(),
        ];
    }
}