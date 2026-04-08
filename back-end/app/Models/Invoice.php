<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'client_id', 'quote_id', 'invoice_number',
        'status', 'subtotal', 'tax_rate', 'total',
        'due_date', 'notes', 'stripe_payment_intent_id',
        'stripe_link', 'paid_at', 'uuid',
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

    // Auto-generate UUID on creation
    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->uuid)) {
                $invoice->uuid = (string) Str::uuid();
            }
        });
    }

    // ── Route model binding — use uuid instead of id ──────
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ── Relationships ─────────────────────────────────────
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function quote(): BelongsTo  { return $this->belongsTo(Quote::class); }
    public function items(): HasMany    { return $this->hasMany(InvoiceItem::class); }

    // ── Scopes ────────────────────────────────────────────
    public function scopeUnpaid(Builder $q): Builder  { return $q->where('status', 'unpaid'); }
    public function scopePaid(Builder $q): Builder    { return $q->where('status', 'paid'); }
    public function scopeOverdue(Builder $q): Builder { return $q->where('status', 'overdue'); }

    // ── generateNumber — FIXED (MAX not COUNT) ────────────
    public static function generateNumber(int $userId): string
    {
        $year   = date('Y');
        $prefix = 'INV-' . $year . '-';

        $last = static::where('user_id', $userId)
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByRaw(
                'CAST(SUBSTRING(invoice_number, ?, 3) AS UNSIGNED) DESC',
                [strlen($prefix) + 1]
            )
            ->value('invoice_number');

        $next = $last
            ? (int) substr($last, strlen($prefix)) + 1
            : 1;

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    // ── Business methods ──────────────────────────────────
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $tax      = $subtotal * ($this->tax_rate / 100);
        $this->updateQuietly(['subtotal' => $subtotal, 'total' => $subtotal + $tax]);
    }

    public function markAsPaid(string $paymentIntentId): void
    {
        $this->update([
            'status'                   => 'paid',
            'paid_at'                  => now(),
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);
    }

    // ── Accessors ─────────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'unpaid'  => 'Unpaid',
            'paid'    => 'Paid',
            'overdue' => 'Overdue',
            default   => ucfirst($this->status),
        };
    }

    public function getDaysUntilDueAttribute(): int
    {
        return (int) now()->diffInDays($this->due_date, false);
    }
}
