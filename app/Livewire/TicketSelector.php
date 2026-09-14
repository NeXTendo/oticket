<?php

namespace App\Livewire;

use App\Models\Event;
use App\Services\BookingService;
use Livewire\Component;

class TicketSelector extends Component
{
    public Event $event;

    /** @var array<int,int> ticket_type_id => quantity */
    public array $quantities = [];

    public ?string $errorMessage = null;

    public function mount(Event $event): void
    {
        $this->event = $event->load(['ticketTypes' => fn ($q) => $q->where('is_active', true)]);

        foreach ($this->event->ticketTypes as $ticketType) {
            $this->quantities[$ticketType->id] = 0;
        }
    }

    public function increment(int $ticketTypeId): void
    {
        $max = $this->event->ticketTypes->find($ticketTypeId)?->max_per_order ?? 10;
        $this->quantities[$ticketTypeId] = min($max, ($this->quantities[$ticketTypeId] ?? 0) + 1);
    }

    public function decrement(int $ticketTypeId): void
    {
        $this->quantities[$ticketTypeId] = max(0, ($this->quantities[$ticketTypeId] ?? 0) - 1);
    }

    public function getTotalProperty(): float
    {
        $total = 0;
        foreach ($this->event->ticketTypes as $ticketType) {
            $total += $ticketType->price * ($this->quantities[$ticketType->id] ?? 0);
        }

        return $total;
    }

    public function proceedToCheckout(BookingService $bookingService)
    {
        $this->errorMessage = null;

        $selected = array_filter($this->quantities, fn ($qty) => $qty > 0);

        if (empty($selected)) {
            $this->errorMessage = 'Select at least one ticket.';

            return;
        }

        if (! auth()->check()) {
            return redirect()->route('login', ['redirect' => url()->current()]);
        }

        try {
            $order = $bookingService->createPendingOrder(auth()->user(), $selected);

            return redirect()->route('checkout', $order->order_number);
        } catch (\RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.ticket-selector');
    }
}
