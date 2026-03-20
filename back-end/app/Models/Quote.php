<?php

namespace App\Models;

// ============================================================
// Quote.php  —  Laravel 13
//
// WHAT CHANGED FROM OLDER LARAVEL:
//
//   ✅ casts() as a method (not $casts property)
//
//   ✅ Accessors use Attribute::make() style
//
//   ✅ NO boot() that creates model instances
//      Laravel 13 restricts creating model instances inside boot().
//      Our convertToInvoice() creates Invoice and InvoiceItem
//      instances — but this happens in a REGULAR METHOD, not boot(),
//      so it's fine here.
// ============================================================

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\Fillable;
#[Fillable(['user_id',
        'client_id',
        'quote_number',
        'status',
        'subtotal',
        'tax_rate',
        'total',
        'notes',
        'valid_until',])]

class Quote extends Model
{
    use HasFactory;

   

    // ── Casts — Laravel 13 method style ──────────────────
    protected function casts(): array
    {
        return [
            'subtotal'    => 'decimal:2',
            'tax_rate'    => 'decimal:2',
            'total'       => 'decimal:2',
            'valid_until' => 'date',
        ];
    }

    // ══════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // Line items belonging to this quote
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // The invoice this quote was converted to (if any)
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    // ══════════════════════════════════════════════════════
    // QUERY SCOPES
    // Reusable WHERE clauses — called as methods on the query builder
    // Usage: Quote::approved()->get()
    //        auth()->user()->quotes()->sent()->latest()->get()
    // ══════════════════════════════════════════════════════

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    // ══════════════════════════════════════════════════════
    // BUSINESS LOGIC METHODS
    // ══════════════════════════════════════════════════════

    // Recalculate totals from items — called after items are saved
    // Usage: $quote->recalculateTotals()
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $tax      = $subtotal * ($this->tax_rate / 100);

        // updateQuietly() = update without firing model events
        // Prevents infinite loops if events also trigger recalculate
        $this->updateQuietly([
            'subtotal' => $subtotal,
            'total'    => $subtotal + $tax,
        ]);
    }

    // Convert this quote to an Invoice
    // Copies all data + items, marks quote as approved
    // Returns the newly created Invoice with client + items loaded
    //
    // NOTE: This creates Invoice and InvoiceItem instances
    // but this is a regular method, NOT boot() — so it's
    // perfectly fine in Laravel 13.
    //
    // Usage: $invoice = $quote->convertToInvoice();
    public function convertToInvoice(): Invoice
    {
        // Create the invoice from this quote's data
        $invoice = Invoice::create([
            'user_id'        => $this->user_id,
            'client_id'      => $this->client_id,
            'quote_id'       => $this->id,
            'invoice_number' => Invoice::generateNumber($this->user_id),
            'status'         => 'unpaid',
            'subtotal'       => $this->subtotal,
            'tax_rate'       => $this->tax_rate,
            'total'          => $this->total,
            'due_date'       => now()->addDays(30),
            'notes'          => $this->notes,
        ]);

        // Copy all items from quote → invoice
        foreach ($this->items as $item) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'quote_id'    => null,
                'description' => $item->description,
                'quantity'    => $item->quantity,
                'unit_price'  => $item->unit_price,
                'subtotal'    => $item->subtotal,
            ]);
        }

        // Mark this quote as approved
        $this->update(['status' => 'approved']);

        return $invoice->load(['client', 'items']);
    }

    // Generate the next quote number for this user
    // Format: QUO-2025-001
    // Usage: Quote::generateNumber(auth()->id())
    public static function generateNumber(int $userId): string
    {
        $year  = date('Y');
        $count = static::where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->count();

        return 'QUO-' . $year . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    // ══════════════════════════════════════════════════════
    // ACCESSORS — Laravel 13 Attribute::make() style
    // ══════════════════════════════════════════════════════

    // Has this quote already been converted to an invoice?
    // Usage: $quote->is_converted → true/false
    protected function isConverted(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->invoice()->exists()
        );
    }

    // Human-readable status label
    // Usage: $quote->status_label → "Approved"
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn (): string => match($this->status) {
                'draft'    => 'Draft',
                'sent'     => 'Sent',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                default    => ucfirst($this->status),
            }
        );
    }
}