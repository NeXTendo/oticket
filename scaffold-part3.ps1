<#
  Adds the Livewire checkout flow (browse event -> select tickets ->
  pay -> confirmation) on top of scaffold.ps1 + scaffold-part2.ps1.

  Requires: composer require livewire/livewire (already in the earlier
  install command).

  Usage:
    .\scaffold-part3.ps1
#>

$ErrorActionPreference = "Stop"

$folders = @(
    "app\Livewire",
    "resources\views\livewire",
    "resources\views\orders"
)

foreach ($folder in $folders) {
    New-Item -ItemType Directory -Force -Path $folder | Out-Null
}

Write-Host "Creating checkout flow files..." -ForegroundColor Cyan

$content = @'
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

'@
Set-Content -Path "app\Livewire\TicketSelector.php" -Value $content -Encoding UTF8
Write-Host "  created app\Livewire\TicketSelector.php"

$content = @'
<?php

namespace App\Livewire;

use App\Models\Order;
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
        // Ownership + state guards — don't let anyone pay someone else's
        // order, and don't let an expired order be paid.
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

    /**
     * Called by wire:poll in the view while waiting for the Lipila
     * webhook to land. Once the webhook flips the order to 'paid', this
     * redirects to the confirmation page.
     */
    public function checkStatus()
    {
        $this->order->refresh();

        if ($this->order->status === 'paid') {
            return redirect()->route('orders.confirmation', $this->order->order_number);
        }

        if ($this->order->isExpired()) {
            $this->errorMessage = 'Your reservation expired before payment completed. Please start again.';
            $this->paymentInitiated = false;
        }
    }

    public function render()
    {
        return view('livewire.checkout');
    }
}

'@
Set-Content -Path "app\Livewire\Checkout.php" -Value $content -Encoding UTF8
Write-Host "  created app\Livewire\Checkout.php"

$content = @'
<div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-1">{{ $event->name }}</h1>
    <p class="text-gray-500 mb-6">
        {{ $event->venue_name }} &middot; {{ $event->starts_at->format('d M Y, H:i') }}
    </p>

    @if ($errorMessage)
        <div class="bg-red-50 text-red-700 border border-red-200 rounded p-3 mb-4">
            {{ $errorMessage }}
        </div>
    @endif

    <div class="space-y-4">
        @foreach ($event->ticketTypes as $ticketType)
            @php $available = $ticketType->availableQuantity(); @endphp
            <div class="flex items-center justify-between border rounded-lg p-4">
                <div>
                    <div class="font-semibold">{{ $ticketType->name }}</div>
                    <div class="text-sm text-gray-500">K{{ number_format($ticketType->price, 2) }}</div>
                    <div class="text-xs text-gray-400">
                        {{ $available > 0 ? "$available left" : 'Sold out' }}
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        wire:click="decrement({{ $ticketType->id }})"
                        class="w-8 h-8 rounded-full border flex items-center justify-center"
                        @disabled(($quantities[$ticketType->id] ?? 0) <= 0)
                    >-</button>

                    <span class="w-6 text-center">{{ $quantities[$ticketType->id] ?? 0 }}</span>

                    <button
                        type="button"
                        wire:click="increment({{ $ticketType->id }})"
                        class="w-8 h-8 rounded-full border flex items-center justify-center"
                        @disabled($available <= 0)
                    >+</button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 flex items-center justify-between border-t pt-4">
        <div class="text-lg font-semibold">Total: K{{ number_format($this->total, 2) }}</div>
        <button
            type="button"
            wire:click="proceedToCheckout"
            wire:loading.attr="disabled"
            class="bg-black text-white px-6 py-2 rounded-lg font-medium disabled:opacity-50"
        >
            <span wire:loading.remove>Checkout</span>
            <span wire:loading>Reserving...</span>
        </button>
    </div>
</div>

'@
Set-Content -Path "resources\views\livewire\ticket-selector.blade.php" -Value $content -Encoding UTF8
Write-Host "  created resources\views\livewire\ticket-selector.blade.php"

$content = @'
<div class="max-w-md mx-auto p-6"
    @if ($paymentInitiated)
        wire:poll.5s="checkStatus"
    @endif
>
    <h1 class="text-xl font-bold mb-4">Checkout</h1>

    <div class="border rounded-lg p-4 mb-4 space-y-1">
        <div class="font-semibold">{{ $order->event->name }}</div>
        @foreach ($order->items as $item)
            <div class="flex justify-between text-sm text-gray-600">
                <span>{{ $item->quantity }} &times; {{ $item->ticketType->name }}</span>
                <span>K{{ number_format($item->unit_price * $item->quantity, 2) }}</span>
            </div>
        @endforeach
        <div class="flex justify-between text-sm text-gray-600 pt-2 border-t mt-2">
            <span>Processing fee</span>
            <span>K{{ number_format($order->processing_fee, 2) }}</span>
        </div>
        <div class="flex justify-between font-semibold pt-1">
            <span>Total</span>
            <span>K{{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    @if ($order->reserved_until)
        <p class="text-xs text-gray-400 mb-4">
            Reserved until {{ $order->reserved_until->format('H:i:s') }} — complete payment before then.
        </p>
    @endif

    @if ($errorMessage)
        <div class="bg-red-50 text-red-700 border border-red-200 rounded p-3 mb-4 text-sm">
            {{ $errorMessage }}
        </div>
    @endif

    @if (! $paymentInitiated)
        <label class="block text-sm font-medium mb-1">Mobile money number</label>
        <input
            type="tel"
            wire:model="phoneNumber"
            placeholder="260977123456"
            class="w-full border rounded-lg px-3 py-2 mb-4"
        >

        <button
            type="button"
            wire:click="initiatePayment"
            wire:loading.attr="disabled"
            class="w-full bg-black text-white py-3 rounded-lg font-medium disabled:opacity-50"
        >
            <span wire:loading.remove>Pay with Mobile Money</span>
            <span wire:loading>Sending prompt...</span>
        </button>
    @else
        <div class="text-center py-6">
            <p class="font-medium mb-1">Check your phone</p>
            <p class="text-sm text-gray-500">
                Approve the payment prompt on {{ $phoneNumber }}. This page updates automatically once payment is confirmed.
            </p>
        </div>
    @endif
</div>

'@
Set-Content -Path "resources\views\livewire\checkout.blade.php" -Value $content -Encoding UTF8
Write-Host "  created resources\views\livewire\checkout.blade.php"

$content = @'
@php
    $order->load('tickets.ticketType');
@endphp

<div class="max-w-md mx-auto p-6 text-center">
    <h1 class="text-2xl font-bold mb-2">You're going! 🎉</h1>
    <p class="text-gray-500 mb-6">Order {{ $order->order_number }} — {{ $order->event->name }}</p>

    <div class="space-y-3 text-left">
        @foreach ($order->tickets as $ticket)
            <div class="border rounded-lg p-4">
                <div class="font-semibold">{{ $ticket->ticketType->name }}</div>
                <div class="text-sm text-gray-500">{{ $ticket->ticket_code }}</div>
            </div>
        @endforeach
    </div>

    <p class="text-sm text-gray-400 mt-6">
        Your tickets have also been emailed to you as a PDF with a QR code.
    </p>
</div>

'@
Set-Content -Path "resources\views\orders\confirmation.blade.php" -Value $content -Encoding UTF8
Write-Host "  created resources\views\orders\confirmation.blade.php"

$content = @'
# Route additions for the checkout flow

Add to routes/web.php, inside your `auth` middleware group where relevant:

```php
use App\Livewire\TicketSelector;
use App\Livewire\Checkout;
use App\Models\Order;

// Public — anyone can view an event and pick tickets
Route::get('/events/{event:slug}', TicketSelector::class)->name('events.show');

// Requires login — TicketSelector redirects to /login if needed before
// creating the order, so these live behind auth for defense in depth
Route::middleware('auth')->group(function () {
    Route::get('/checkout/{order:order_number}', Checkout::class)->name('checkout');

    Route::get('/orders/{order:order_number}/confirmation', function (Order $order) {
        abort_if($order->user_id !== auth()->id(), 403);
        abort_unless($order->status === 'paid', 404);

        return view('orders.confirmation', compact('order'));
    })->name('orders.confirmation');
});
```

Note the route-model binding uses `slug` for events and `order_number` for
orders (not the numeric `id`) — add this to `Event.php` and `Order.php`
if you haven't already:

```php
// In app/Models/Event.php
public function getRouteKeyName(): string
{
    return 'slug';
}

// In app/Models/Order.php
public function getRouteKeyName(): string
{
    return 'order_number';
}
```

This keeps ticket/order URLs from leaking sequential database IDs.

'@
Set-Content -Path "ROUTES.md" -Value $content -Encoding UTF8
Write-Host "  created ROUTES.md"

Write-Host ""
Write-Host "Done. Created:" -ForegroundColor Green
Write-Host "  app\Livewire\TicketSelector.php"
Write-Host "  app\Livewire\Checkout.php"
Write-Host "  resources\views\livewire\ticket-selector.blade.php"
Write-Host "  resources\views\livewire\checkout.blade.php"
Write-Host "  resources\views\orders\confirmation.blade.php"
Write-Host "  ROUTES.md"
Write-Host ""
Write-Host "Now open ROUTES.md and make the 2 edits it describes" -ForegroundColor Yellow
Write-Host "(routes/web.php, plus getRouteKeyName() on Event and Order models)." -ForegroundColor Yellow
