<x-layouts.app :title="'Checkout — ' . $order->event->name">
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10"
     @if ($paymentInitiated) wire:poll.5s="checkStatus" @endif>

    {{-- Back link --}}
    <a href="{{ route('events.show', $order->event->slug) }}" class="inline-flex items-center gap-2 text-white/50 hover:text-white text-sm transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to event
    </a>

    <h1 class="font-display font-extrabold text-3xl text-white mb-2">Checkout</h1>
    <p class="text-white/50 text-sm mb-8">Order <span class="font-mono text-white/70">{{ $order->order_number }}</span></p>

    <div class="grid grid-cols-1 gap-6">

        {{-- Order summary card --}}
        <div class="glass rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-white/5">
                <h2 class="font-display font-bold text-white">{{ $order->event->name }}</h2>
                <p class="text-white/50 text-sm mt-0.5">
                    {{ $order->event->starts_at->format('D, d M Y · H:i') }}
                    · {{ $order->event->venue_name }}
                </p>
            </div>

            <div class="px-5 py-4 space-y-3">
                @foreach ($order->items as $item)
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-white font-medium text-sm">{{ $item->ticketType->name }}</span>
                        <span class="text-white/40 text-sm"> × {{ $item->quantity }}</span>
                    </div>
                    <span class="text-white font-semibold text-sm">K{{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                </div>
                @endforeach
            </div>

            <div class="px-5 py-4 border-t border-white/5 space-y-2">
                <div class="flex justify-between text-sm text-white/50">
                    <span>Subtotal</span>
                    <span>K{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm text-white/50">
                    <span>Processing fee</span>
                    <span>K{{ number_format($order->processing_fee, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-white text-lg pt-2 border-t border-white/5">
                    <span>Total</span>
                    <span class="gradient-text">K{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Reservation timer --}}
        @if($order->reserved_until && $order->status === 'pending_payment')
        <div class="flex items-center gap-3 glass rounded-xl px-4 py-3">
            <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <div class="text-amber-400 text-sm font-semibold">Seats reserved until {{ $order->reserved_until->format('H:i:s') }}</div>
                <div class="text-white/40 text-xs">Complete payment before your reservation expires</div>
            </div>
        </div>
        @endif

        {{-- Error alert --}}
        @if ($errorMessage)
        <div class="flex items-start gap-3 bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-red-400 text-sm">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 5a7 7 0 100 14A7 7 0 0012 5z"/></svg>
            {{ $errorMessage }}
        </div>
        @endif

        {{-- Payment panel --}}
        @if (! $paymentInitiated)
        <div class="glass rounded-2xl p-6">
            <h3 class="font-display font-bold text-white text-lg mb-1">Pay with Mobile Money</h3>
            <p class="text-white/50 text-sm mb-5">A payment prompt will be sent to your phone number.</p>

            {{-- Network logos --}}
            <div class="flex gap-3 mb-5">
                @foreach([
                    ['name' => 'Airtel Money', 'color' => 'bg-red-500/20 text-red-400', 'initial' => 'A'],
                    ['name' => 'MTN MoMo', 'color' => 'bg-yellow-500/20 text-yellow-400', 'initial' => 'M'],
                    ['name' => 'Zamtel Kwacha', 'color' => 'bg-green-500/20 text-green-400', 'initial' => 'Z'],
                ] as $network)
                <div class="flex items-center gap-2 glass rounded-lg px-3 py-2">
                    <div class="w-5 h-5 rounded-full {{ $network['color'] }} flex items-center justify-center text-xs font-bold">{{ $network['initial'] }}</div>
                    <span class="text-white/60 text-xs">{{ $network['name'] }}</span>
                </div>
                @endforeach
            </div>

            <label class="block text-sm font-medium text-white/70 mb-2" for="phone-number">Mobile money number</label>
            <input type="tel"
                   wire:model="phoneNumber"
                   id="phone-number"
                   placeholder="260977123456"
                   class="input-field mb-4">

            <button type="button"
                    wire:click="initiatePayment"
                    wire:loading.attr="disabled"
                    class="btn-primary w-full py-4 text-base"
                    id="initiate-payment-btn">
                <span wire:loading.remove wire:target="initiatePayment">
                    Pay K{{ number_format($order->total, 2) }}
                </span>
                <span wire:loading wire:target="initiatePayment" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Sending payment prompt...
                </span>
            </button>

            {{-- Dev mode instant confirm --}}
            @if(app()->environment('local'))
            <div class="mt-4 border-t border-white/5 pt-4">
                <p class="text-white/30 text-xs text-center mb-3">⚡ Dev mode: skip real payment</p>
                <button type="button"
                        wire:click="simulatePaymentSuccess"
                        class="w-full glass text-emerald-400 text-sm font-semibold py-2.5 rounded-xl hover:bg-emerald-500/10 transition-all"
                        id="dev-simulate-payment">
                    Simulate Successful Payment
                </button>
            </div>
            @endif
        </div>

        @else
        {{-- Waiting for payment --}}
        <div class="glass rounded-2xl p-8 text-center">
            <div class="w-16 h-16 gradient-brand rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-xl">
                <svg class="w-8 h-8 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21l4-1 4 1V3a2 2 0 00-2-2H8a2 2 0 00-2 2v18z"/></svg>
            </div>
            <h3 class="font-display font-bold text-white text-xl mb-2">Check your phone</h3>
            <p class="text-white/60 text-sm mb-1">A payment prompt was sent to</p>
            <p class="text-white font-mono font-bold text-lg mb-4">{{ $phoneNumber }}</p>
            <p class="text-white/40 text-xs">Approve the prompt to complete your booking. This page updates automatically.</p>
            <div class="mt-6 flex items-center justify-center gap-1.5">
                <div class="w-2 h-2 gradient-brand rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                <div class="w-2 h-2 gradient-brand rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                <div class="w-2 h-2 gradient-brand rounded-full animate-bounce" style="animation-delay: 300ms"></div>
            </div>
        </div>
        @endif

    </div>
</div>
</x-layouts.app>
