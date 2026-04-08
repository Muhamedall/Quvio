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
            'uuid'           => $this->uuid,         
            'invoice_number' => $this->invoice_number,
            'status'         => $this->status,
            'status_label'   => $this->status_label,
            'tax_rate'       => (float) $this->tax_rate,
            'subtotal'       => (float) $this->subtotal,
            'total'          => (float) $this->total,
            'due_date'       => $this->due_date?->toDateString(),
            'paid_at'        => $this->paid_at?->toDateString(),
            'days_until_due' => $this->days_until_due,
            'notes'          => $this->notes,
            'stripe_link'    => $this->stripe_link,

            'client' => $this->whenLoaded('client', fn () => [
                'id'      => $this->client->id,
                'name'    => $this->client->name,
                'email'   => $this->client->email,
                'phone'   => $this->client->phone,
                'company' => $this->client->company,
                'address' => $this->client->address,
            ]),

            'items' => $this->whenLoaded('items',
                fn () => InvoiceItemResource::collection($this->items)
            ),

            'quote' => $this->whenLoaded('quote', fn () => $this->quote ? [
                'id'           => $this->quote->id,
                'uuid'         => $this->quote->uuid,
                'quote_number' => $this->quote->quote_number,
            ] : null),

            'created_at' => $this->created_at->toDateString(),
        ];
    }
}