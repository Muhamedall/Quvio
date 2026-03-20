<?php

namespace App\Http\Resources;

// ============================================================
// ClientResource.php
//
// WHAT IS AN API RESOURCE?
//   A Resource transforms a Model into a specific JSON shape.
//   Instead of returning the raw Eloquent model (which exposes
//   ALL columns including internal ones), we control exactly
//   what fields Angular receives.
//
// WHY NOT JUST return $client?
//   Raw model return includes everything in the database.
//   A Resource lets you:
//   - Rename fields (snake_case DB → camelCase? or keep consistent)
//   - Add computed fields (display_name, initials)
//   - Hide internal fields (user_id, pivot data)
//   - Nest related resources (client → quotes count)
//   - Control null handling
//
// USAGE IN CONTROLLER:
//   return new ClientResource($client);           // single
//   return ClientResource::collection($clients);  // list
//
// ANGULAR RECEIVES:
//   {
//     "data": {
//       "id": 1,
//       "name": "John Doe",
//       "email": "john@doe.com",
//       ...
//     }
//   }
//   For collections: { "data": [...] }
// ============================================================

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'email'        => $this->email,
            'phone'        => $this->phone,          // nullable
            'company'      => $this->company,        // nullable
            'address'      => $this->address,        // nullable

            // Computed accessors from Client model
            'display_name' => $this->display_name,   // company ?? name
            'initials'     => $this->initials,       // first letter

            // Count related records — only loaded when needed
            // whenLoaded() = only include if the relation was eager loaded
            // Prevents N+1 queries
            'quotes_count'   => $this->whenLoaded('quotes', fn () => $this->quotes->count()),
            'invoices_count' => $this->whenLoaded('invoices', fn () => $this->invoices->count()),

            'created_at' => $this->created_at->toDateString(),
            'updated_at' => $this->updated_at->toDateString(),
        ];
    }
}