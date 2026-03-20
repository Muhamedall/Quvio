<?php

namespace App\Models;

// ============================================================
// Invoice.php  —  Laravel 13
//
// WHAT CHANGED FROM OLDER LARAVEL:
//   ✅ casts() as a method
//   ✅ Accessors use Attribute::make() style
// ============================================================

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
#[Fillable(['user_id',
        'client_id',
        'quote_id',
        'invoice_number',
        'status',
        'subtotal',
        'tax_rate',
        'total',
        'due_date',
        'notes',
        'stripe_payment_intent_id',
        'stripe_link',
        'paid_at',])]


class Invoice extends Model
{
    use HasFactory;

   

    // ── Casts — Laravel 13 method style ──────────────────
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'total'    => 'decimal:2',
            'due_date' => 'date',
            'paid_at'  => 'datetime',
        ];
    }

    // ══════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════════════════

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Client who receives this invoice
    // Usage: $invoice->client->email
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // The quote this invoice was created from (nullable)
    // Usage: $invoice->quote → Quote or null
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    // Line items on this invoice
    // Usage: $invoice->items
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // ══════════════════════════════════════════════════════
    // QUERY SCOPES
    // ══════════════════════════════════════════════════════

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'overdue');
    }

    // Invoices that are past due_date and still unpaid
    // Used by the scheduled job to mark overdue invoices
    // Usage: Invoice::duePastDate()->get()
    public function scopeDuePastDate(Builder $query): Builder
    {
        return $query
            ->where('status', 'unpaid')
            ->whereDate('due_date', '<', now());
    }

    // ══════════════════════════════════════════════════════
    // BUSINESS LOGIC METHODS
    // ══════════════════════════════════════════════════════

    // Generate invoice number: INV-2025-001
    public static function generateNumber(int $userId): string
    {
        $year  = date('Y');
        $count = static::where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->count();

        return 'INV-' . $year . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    // Recalculate totals from all items
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $tax      = $subtotal * ($this->tax_rate / 100);

        $this->updateQuietly([
            'subtotal' => $subtotal,
            'total'    => $subtotal + $tax,
        ]);
    }

    // Mark invoice as paid — called by Stripe webhook controller
    // Usage: $invoice->markAsPaid($paymentIntentId)
    public function markAsPaid(string $paymentIntentId): void
    {
        $this->update([
            'status'                   => 'paid',
            'paid_at'                  => now(),
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);
    }

    // Check if past due date and still unpaid
    // Used by the daily overdue scheduled job
    public function isOverdue(): bool
    {
        return $this->status === 'unpaid'
            && $this->due_date->isPast();
    }

    // ══════════════════════════════════════════════════════
    // ACCESSORS — Laravel 13 Attribute::make() style
    // ══════════════════════════════════════════════════════

    // Human-readable status: "Unpaid", "Paid", "Overdue"
    // Usage: $invoice->status_label
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn (): string => match($this->status) {
                'unpaid'  => 'Unpaid',
                'paid'    => 'Paid',
                'overdue' => 'Overdue',
                default   => ucfirst($this->status),
            }
        );
    }

    // Days until payment is due (negative = already overdue)
    // Usage: $invoice->days_until_due → 14 or -3
    protected function daysUntilDue(): Attribute
    {
        return Attribute::make(
            get: fn (): int => (int) now()->diffInDays($this->due_date, false)
        );
    }
}