<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Redis;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'name', 'description', 'price',
        'quantity_total', 'quantity_sold', 'quantity_reserved',
        'max_per_order', 'sales_start_at', 'sales_end_at', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sales_start_at' => 'datetime',
        'sales_end_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Tickets actually left to sell right now (DB total minus sold minus
     * whatever is currently held in Redis by other in-progress checkouts).
     */
    public function availableQuantity(): int
    {
        $redisHeld = (int) Redis::get("ticket_type:{$this->id}:held") ?: 0;

        return max(0, $this->quantity_total - $this->quantity_sold - $redisHeld);
    }

    /**
     * Attempt to place a short-lived hold on $qty seats using a Redis
     * counter with a TTL. Returns true if the hold succeeded.
     *
     * NOTE: this is a simple counter-based hold suitable for
     * general-admission tickets (no seat map). Reserved seating (Phase 2)
     * needs per-seat locks instead of a single counter.
     */
    public function reserve(int $qty, string $holdKeySuffix, int $ttlSeconds = 600): bool
    {
        $key = "ticket_type:{$this->id}:held";
        $lockKey = "ticket_type:{$this->id}:lock";

        // Naive optimistic lock; swap for a proper Redis Lua script /
        // RedLock if you see contention under load.
        $acquired = Redis::set($lockKey, 1, 'EX', 5, 'NX');
        if (! $acquired) {
            return false;
        }

        try {
            if ($this->availableQuantity() < $qty) {
                return false;
            }

            Redis::incrby($key, $qty);
            Redis::expire($key, $ttlSeconds);

            // Track this specific hold so it can be released independently
            // (e.g. order X reserved 2 seats) if the order expires.
            Redis::setex("order_hold:{$holdKeySuffix}:{$this->id}", $ttlSeconds, $qty);

            return true;
        } finally {
            Redis::del($lockKey);
        }
    }

    public function releaseHold(string $holdKeySuffix): void
    {
        $heldKey = "order_hold:{$holdKeySuffix}:{$this->id}";
        $qty = (int) Redis::get($heldKey) ?: 0;

        if ($qty > 0) {
            Redis::decrby("ticket_type:{$this->id}:held", $qty);
            Redis::del($heldKey);
        }
    }
}
