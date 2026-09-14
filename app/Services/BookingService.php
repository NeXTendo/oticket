<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class BookingService
{
    /**
     * Step 1: customer picks quantities per ticket type. This creates a
     * pending order and places short-lived Redis holds so nobody else can
     * buy the same inventory while this customer is paying.
     *
     * @param  array<int,int>  $quantities  [ticket_type_id => qty]
     */
    public function createPendingOrder(User $user, array $quantities, int $holdMinutes = 10): Order
    {
        if (empty($quantities)) {
            throw new RuntimeException('No tickets selected.');
        }

        $ticketTypes = TicketType::whereIn('id', array_keys($quantities))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $eventIds = $ticketTypes->pluck('event_id')->unique();
        if ($eventIds->count() > 1) {
            throw new RuntimeException('An order can only contain tickets for one event.');
        }

        return DB::transaction(function () use ($user, $quantities, $ticketTypes, $holdMinutes) {
            $holdSuffix = (string) Str::uuid();

            $order = Order::create([
                'user_id' => $user->id,
                'event_id' => $ticketTypes->first()->event_id,
                'subtotal' => 0,
                'platform_commission' => 0,
                'processing_fee' => 0,
                'total' => 0,
                'status' => 'pending_payment',
                'reserved_until' => now()->addMinutes($holdMinutes),
            ]);

            foreach ($quantities as $ticketTypeId => $qty) {
                if ($qty < 1) {
                    continue;
                }

                /** @var TicketType $ticketType */
                $ticketType = $ticketTypes[$ticketTypeId];

                if ($qty > $ticketType->max_per_order) {
                    throw new RuntimeException("Max {$ticketType->max_per_order} per order for {$ticketType->name}.");
                }

                $held = $ticketType->reserve($qty, $holdSuffix, ttlSeconds: $holdMinutes * 60);
                if (! $held) {
                    throw new RuntimeException("Not enough '{$ticketType->name}' tickets available.");
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => $qty,
                    'unit_price' => $ticketType->price,
                ]);
            }

            $order->load('items', 'event.organizer');
            $order->recalculateTotals();
            $order->save();

            // Remember the hold suffix so we can release it if the order
            // expires or payment fails.
            $order->update(['hold_reference' => $holdSuffix]);

            return $order->fresh(['items.ticketType', 'event.organizer']);
        });
    }

    /**
     * Step 3 (after Lipila confirms payment via webhook/callback): mark the
     * order paid, generate one Ticket row per unit purchased, and hand
     * back the order so the caller can dispatch email delivery.
     */
    public function confirmPaidOrder(Order $order): Order
    {
        if ($order->status === 'paid') {
            return $order; // idempotent: webhook may fire more than once
        }

        if ($order->isExpired()) {
            throw new RuntimeException('Cannot confirm an expired order.');
        }

        return DB::transaction(function () use ($order) {
            $order->update(['status' => 'paid']);

            foreach ($order->items as $item) {
                $item->ticketType()->increment('quantity_sold', $item->quantity);

                for ($i = 0; $i < $item->quantity; $i++) {
                    Ticket::create([
                        'order_item_id' => $item->id,
                        'event_id' => $order->event_id,
                        'ticket_type_id' => $item->ticket_type_id,
                        'user_id' => $order->user_id,
                        'holder_name' => $order->user->name,
                        'status' => 'valid',
                    ]);
                }

                // Now that the sale is final, release the Redis hold â€” the
                // sold count above is the permanent record from here on.
                $item->ticketType->releaseHold($order->hold_reference);
            }

            return $order->fresh(['tickets', 'items.ticketType']);
        });
    }

    /**
     * Called by a scheduled job for orders whose reserved_until has passed
     * and which never got paid: release inventory back into the pool.
     */
    public function expireOrder(Order $order): void
    {
        if ($order->status !== 'pending_payment') {
            return;
        }

        foreach ($order->items as $item) {
            $item->ticketType->releaseHold($order->hold_reference);
        }

        $order->update(['status' => 'expired']);
    }
}
