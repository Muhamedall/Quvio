<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'invoice_number' => $this->invoice_number,  // INV-2025-001
            'status'         => $this->status,           // unpaid|paid|overdue
            'status_label'   => $this->status_label,     // "Unpaid"...

            // Amounts
            'subtotal'       => (float) $this->subtotal,
            'tax_rate'       => (float) $this->tax_rate,
            'total'          => (float) $this->total,

            // Dates
            'due_date'       => $this->due_date->toDateString(),
            'paid_at'        => $this->paid_at?->toDateTimeString(),  // null until paid

            // Days until payment due (negative = overdue)
            'days_until_due' => $this->days_until_due,

            // Stripe
            'stripe_link'    => $this->stripe_link,  // null until generated

            'notes'          => $this->notes,

            // Nested resources
            'client'         => new ClientResource($this->whenLoaded('client')),
            'quote'          => new QuoteResource($this->whenLoaded('quote')),
            'items'          => InvoiceItemResource::collection(
                                  $this->whenLoaded('items')
                                ),

            'created_at'     => $this->created_at->toDateString(),
            'updated_at'     => $this->updated_at->toDateString(),
        ];
    }
}