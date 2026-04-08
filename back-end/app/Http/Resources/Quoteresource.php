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
            'uuid'         => $this->uuid,          
            'quote_number' => $this->quote_number,
            'status'       => $this->status,
            'status_label' => $this->status_label,
            'tax_rate'     => (float) $this->tax_rate,
            'subtotal'     => (float) $this->subtotal,
            'total'        => (float) $this->total,
            'notes'        => $this->notes,
            'valid_until'  => $this->valid_until?->toDateString(),
            'is_converted' => $this->is_converted,
            'created_at'   => $this->created_at->toDateString(),
            'updated_at'   => $this->updated_at->toDateString(),

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
        ];
    }
}