<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'event_id', 'subtotal',
        'platform_commission', 'processing_fee', 'total',
        'status', 'reserved_until', 'hold_reference',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'reserved_until' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number ??= 'ORD-'.strtoupper(Str::random(8));
        });
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasManyThrough(Ticket::class, OrderItem::class);
    }

    public function isExpired(): bool
    {
        return $this->status === 'pending_payment'
            && $this->reserved_until
            && $this->reserved_until->isPast();
    }

    /**
     * Recalculate commission / processing fee / total from the organizer's
     * commission_rate. The organizer absorbs the commission (deducted from
     * their payout); the customer pays subtotal + processing fee only.
     */
    public function recalculateTotals(float $processingFeeAmount = 0): void
    {
        $organizer = $this->event->organizer;

        $this->subtotal = $this->items->sum(fn ($item) => $item->unit_price * $item->quantity);
        $this->platform_commission = round($this->subtotal * ($organizer->commission_rate / 100), 2);
        $this->processing_fee = round($processingFeeAmount, 2);
        $this->total = $this->subtotal + $this->processing_fee; // commission is deducted from organizer payout, not added to customer total
    }
}
