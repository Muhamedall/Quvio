<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'client_id', 'quote_id', 'invoice_number',
        'status', 'subtotal', 'tax_rate', 'total', 'due_date',
        'notes', 'stripe_payment_intent_id', 'stripe_link', 'paid_at',
    ];

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

    // ── RELATIONSHIPS ─────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // ── QUERY SCOPES ──────────────────────────────────────

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

    public function scopeDuePastDate(Builder $query): Builder
    {
        return $query
            ->where('status', 'unpaid')
            ->whereDate('due_date', '<', now());
    }

    // ── BUSINESS LOGIC ────────────────────────────────────

    // ✅ FIXED: uses max() instead of count() — never duplicates
    public static function generateNumber(int $userId): string
    {
        $year   = date('Y');
        $prefix = 'INV-' . $year . '-';

        // Find the highest existing invoice number this year
        $last = static::where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->where('invoice_number', 'like', $prefix . '%')
            ->max('invoice_number');

        if ($last) {
            // Extract numeric suffix: "INV-2026-009" → 9
            $lastNum = (int) substr($last, strlen($prefix));
            $next    = $lastNum + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $tax      = $subtotal * ($this->tax_rate / 100);

        $this->updateQuietly([
            'subtotal' => $subtotal,
            'total'    => $subtotal + $tax,
        ]);
    }

    public function markAsPaid(string $paymentIntentId): void
    {
        $this->update([
            'status'                   => 'paid',
            'paid_at'                  => now(),
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'unpaid' && $this->due_date->isPast();
    }

    // ── ACCESSORS ─────────────────────────────────────────

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

    protected function daysUntilDue(): Attribute
    {
        return Attribute::make(
            get: fn (): int => (int) now()->diffInDays($this->due_date, false)
        );
    }
}
