<?php

namespace App\Models;

// ============================================================
// InvoiceItem.php  —  Laravel 13
//
// CRITICAL LARAVEL 13 FIX — boot() → booted()
//
// THE PROBLEM WITH boot() IN LARAVEL 13:
//   In our event hooks (saved, deleted), we call:
//     $item->invoice?->recalculateTotals()
//   This creates a new Invoice model instance inside the event.
//
//   Laravel 13 added a restriction:
//   "New Eloquent model instances can't be created during boot()"
//
//   The old code used boot() — WILL BREAK in Laravel 13.
//
// THE FIX — use booted() instead:
//   boot()    → runs DURING model class initialization
//              → model class is still being set up
//              → creating other model instances here is UNSAFE
//
//   booted()  → runs AFTER model class is fully initialized
//              → all traits, casts, relationships are ready
//              → creating other model instances here is SAFE ✅
//
// RULE FOR LARAVEL 13:
//   boot()   → only for registering global scopes, macros
//   booted() → for model events (creating, created, saved, deleted...)
// ============================================================

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
#[Fillable([ 'invoice_id',
        'quote_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',])]


class InvoiceItem extends Model
{
    use HasFactory;

  

    // ── Casts — Laravel 13 method style ──────────────────
    protected function casts(): array
    {
        return [
            'quantity'   => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal'   => 'decimal:2',
        ];
    }

    // ══════════════════════════════════════════════════════
    // BOOTED — Laravel 13 correct place for model events
    //
    // booted() fires AFTER the model class is fully initialized.
    // This is the correct place to register model event hooks
    // in Laravel 13 — especially when the hooks need to
    // instantiate or query other models.
    // ══════════════════════════════════════════════════════
    protected static function booted(): void
    {
        // BEFORE creating a new item → calculate subtotal
        // No need to manually set subtotal in the controller
        static::creating(function (self $item): void {
            $item->subtotal = $item->quantity * $item->unit_price;
        });

        // BEFORE updating an item → recalculate subtotal
        static::updating(function (self $item): void {
            $item->subtotal = $item->quantity * $item->unit_price;
        });

        // AFTER saving (create or update) → update parent totals
        // The ?-> null-safe operator handles the nullable FK gracefully
        static::saved(function (self $item): void {
            // If this item belongs to an invoice → update invoice total
            if ($item->invoice_id) {
                $item->invoice?->recalculateTotals();
            }
            // If this item belongs to a quote → update quote total
            if ($item->quote_id) {
                $item->quote?->recalculateTotals();
            }
        });

        // AFTER deleting an item → update parent totals
        static::deleted(function (self $item): void {
            if ($item->invoice_id) {
                $item->invoice?->recalculateTotals();
            }
            if ($item->quote_id) {
                $item->quote?->recalculateTotals();
            }
        });
    }

    // ══════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════

    // This item belongs to an Invoice (or null if it's a quote item)
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // This item belongs to a Quote (or null if it's an invoice item)
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}