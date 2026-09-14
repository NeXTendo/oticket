<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Payment;
use App\Services\BookingService;
use App\Services\LipilaClient;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Checkout extends Component
{
    public Order $order;

    public string $phoneNumber = '';

    public bool $paymentInitiated = false;

    public ?string $errorMessage = null;

    public function mount(Order $order): void
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if ($order->isExpired()) {
            $order->update(['status' => 'expired']);
        }

        $this->order = $order->load('items.ticketType', 'event');
    }

    public function initiatePayment(LipilaClient $lipila): void
    {
        $this->errorMessage = null;

        if ($this->order->status !== 'pending_payment') {
            $this->errorMessage = 'This order is no longer awaiting payment.';

            return;
        }

        if (! preg_match('/^26[07]\d{8}$/', $this->phoneNumber)) {
            $this->errorMessage = 'Enter a valid Zambian mobile number, e.g. 260977123456.';

            return;
        }

        try {
            $lipila->createMobileCollection(
                referenceId: $this->order->order_number,
                amount: (float) $this->order->total,
                accountNumber: $this->phoneNumber,
                narration: "Tickets for {$this->order->event->name}",
            );

            $this->paymentInitiated = true;
        } catch (\RuntimeException $e) {
            Log::error('Lipila collection failed', ['order' => $this->order->order_number, 'error' => $e->getMessage()]);
            $this->errorMessage = 'Could not start payment. Please try again in a moment.';
        }
    }

    public function checkStatus(): void
    {
        $this->order->refresh();

        if ($this->order->status === 'paid') {
            $this->redirect(route('orders.confirmation', $this->order->order_number));

            return;
        }

        if ($this->order->isExpired()) {
            $this->errorMessage = 'Your reservation expired before payment completed. Please start again.';
            $this->paymentInitiated = false;
        }
    }

    /**
     * Dev-mode only: instantly confirm the order as paid, bypassing Lipila.
     */
    public function simulatePaymentSuccess(BookingService $bookingService): void
    {
        if (! app()->environment('local')) {
            abort(403);
        }

        Payment::firstOrCreate(
            ['order_id' => $this->order->id, 'provider' => 'lipila'],
            [
                'provider_reference' => 'DEV-SIM',
                'method' => 'mobile_money',
                'amount' => $this->order->total,
                'currency' => 'ZMW',
                'status' => 'completed',
                'gateway_payload' => ['simulated' => true],
                'paid_at' => now(),
            ]
        );

        $bookingService->confirmPaidOrder($this->order->fresh(['items.ticketType']));

        $this->redirect(route('orders.confirmation', $this->order->order_number));
    }

    public function render()
    {
        return view('livewire.checkout');
    }
}
