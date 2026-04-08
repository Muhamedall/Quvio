<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'client_id', 'quote_number', 'status',
        'subtotal', 'tax_rate', 'total', 'notes', 'valid_until', 'uuid',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'    => 'decimal:2',
            'tax_rate'    => 'decimal:2',
            'total'       => 'decimal:2',
            'valid_until' => 'date',
        ];
    }

    // Auto-generate UUID on creation
    protected static function booted(): void
    {
        static::creating(function (Quote $quote) {
            if (empty($quote->uuid)) {
                $quote->uuid = (string) Str::uuid();
            }
        });
    }

    // ── Route model binding — use uuid instead of id ──────
    // /quotes/abc123... instead of /quotes/16
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ── Relationships ─────────────────────────────────────
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function items(): HasMany    { return $this->hasMany(InvoiceItem::class); }
    public function invoice(): HasOne   { return $this->hasOne(Invoice::class); }

    // ── Scopes ────────────────────────────────────────────
    public function scopeDraft(Builder $q): Builder    { return $q->where('status', 'draft'); }
    public function scopeSent(Builder $q): Builder     { return $q->where('status', 'sent'); }
    public function scopeApproved(Builder $q): Builder { return $q->where('status', 'approved'); }
    public function scopeRejected(Builder $q): Builder { return $q->where('status', 'rejected'); }

    // ── generateNumber — FIXED (MAX not COUNT) ────────────
    public static function generateNumber(int $userId): string
    {
        $year   = date('Y');
        $prefix = 'QUO-' . $year . '-';

        $last = static::where('user_id', $userId)
            ->where('quote_number', 'like', $prefix . '%')
            ->orderByRaw(
                'CAST(SUBSTRING(quote_number, ?, 3) AS UNSIGNED) DESC',
                [strlen($prefix) + 1]
            )
            ->value('quote_number');

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

    public function convertToInvoice(): Invoice
    {
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

        $this->update(['status' => 'approved']);

        return $invoice->load(['client', 'items']);
    }

    // ── Accessors ─────────────────────────────────────────
    public function getIsConvertedAttribute(): bool
    {
        return $this->invoice()->exists();
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'Draft',
            'sent'     => 'Sent',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default    => ucfirst($this->status),
        };
    }
}
