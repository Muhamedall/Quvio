<?php

namespace App\Models;

// ============================================================
// Client.php  —  Laravel 13
// ============================================================

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
#[Fillable(['user_id',
        'name',
        'email',
        'phone',
        'company',
        'address',])]

class Client extends Model

{
    use HasFactory;

   

    // No casts needed — all fields are strings or nullable strings

    // ══════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════

    // This client belongs to one User (the freelancer who owns it)
    // Usage: $client->user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // One client can have many Quotes
    // Usage: $client->quotes
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    // One client can have many Invoices
    // Usage: $client->invoices
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // ══════════════════════════════════════════════════════
    // ACCESSORS — Laravel 13 Attribute::make() style
    // ══════════════════════════════════════════════════════

    // Show company name if exists, otherwise personal name
    // Usage: $client->display_name → "Acme Corp" or "John Doe"
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->company ?? $this->name
        );
    }

    // First letter for avatar circle
    // Usage: $client->initials → "A"
    protected function initials(): Attribute
    {
        return Attribute::make(
            get: fn (): string => strtoupper(substr($this->company ?? $this->name, 0, 1))
        );
    }
}