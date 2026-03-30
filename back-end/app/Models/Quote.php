<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'client_id', 'quote_number', 'status',
        'subtotal', 'tax_rate', 'total', 'notes', 'valid_until',
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

    // ── RELATIONSHIPS ─────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    // ── QUERY SCOPES ──────────────────────────────────────

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

    // ── BUSINESS LOGIC ────────────────────────────────────

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $tax      = $subtotal * ($this->tax_rate / 100);

        $this->updateQuietly([
            'subtotal' => $subtotal,
            'total'    => $subtotal + $tax,
        ]);
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

    // ✅ FIXED: uses max() instead of count() — never duplicates
    public static function generateNumber(int $userId): string
    {
        $year   = date('Y');
        $prefix = 'QUO-' . $year . '-';

        $last = static::where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->where('quote_number', 'like', $prefix . '%')
            ->max('quote_number');

        if ($last) {
            $lastNum = (int) substr($last, strlen($prefix));
            $next    = $lastNum + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    // ── ACCESSORS ─────────────────────────────────────────

    protected function isConverted(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->invoice()->exists()
        );
    }

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