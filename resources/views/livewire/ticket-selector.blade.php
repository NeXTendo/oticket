<div class="glass rounded-2xl overflow-hidden">

    {{-- Header --}}
    <div class="gradient-brand p-5">
        <h2 class="font-display font-bold text-white text-lg mb-0.5">Select Tickets</h2>
        <p class="text-white/70 text-sm">{{ $event->starts_at->format('d M Y · H:i') }}</p>
    </div>

    <div class="p-5 space-y-4">

        {{-- Error alert --}}
        @if ($errorMessage)
        <div class="flex items-start gap-3 bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-red-400 text-sm">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 5a7 7 0 100 14A7 7 0 0012 5z"/></svg>
            {{ $errorMessage }}
        </div>
        @endif

        {{-- Ticket type rows --}}
        @forelse ($event->ticketTypes as $ticketType)
            @php $available = $ticketType->availableQuantity(); @endphp
            <div class="glass rounded-xl p-4 {{ $available <= 0 ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <div class="font-semibold text-white text-sm">{{ $ticketType->name }}</div>
                            @if($available <= 20 && $available > 0)
                                <span class="badge bg-amber-500/20 text-amber-400 text-[10px]">Only {{ $available }} left</span>
                            @elseif($available <= 0)
                                <span class="badge bg-red-500/20 text-red-400 text-[10px]">Sold out</span>
                            @else
                                <span class="badge bg-emerald-500/20 text-emerald-400 text-[10px]">Available</span>
                            @endif
                        </div>
                        @if($ticketType->description)
                        <div class="text-white/40 text-xs leading-relaxed mb-2">{{ $ticketType->description }}</div>
                        @endif
                        <div class="font-bold text-[oklch(0.75_0.18_265)] text-base">K{{ number_format($ticketType->price, 2) }}</div>
                    </div>

                    {{-- Quantity stepper --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button"
                                wire:click="decrement({{ $ticketType->id }})"
                                @disabled(($quantities[$ticketType->id] ?? 0) <= 0)
                                class="w-8 h-8 rounded-full border border-white/10 flex items-center justify-center text-white/60 hover:text-white hover:border-white/30 hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition-all duration-150">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                        </button>

                        <span class="w-6 text-center font-bold text-white text-sm">{{ $quantities[$ticketType->id] ?? 0 }}</span>

                        <button type="button"
                                wire:click="increment({{ $ticketType->id }})"
                                @disabled($available <= 0 || ($quantities[$ticketType->id] ?? 0) >= $ticketType->max_per_order)
                                class="w-8 h-8 rounded-full border border-white/10 flex items-center justify-center text-white/60 hover:text-white hover:border-white/30 hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition-all duration-150">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-6 text-white/40 text-sm">No tickets available for this event.</div>
        @endforelse

        {{-- Order summary --}}
        @php $total = $this->total; @endphp
        @if($total > 0)
        <div class="border-t border-white/10 pt-4">
            <div class="flex items-center justify-between text-white/60 text-sm mb-1">
                <span>Subtotal</span>
                <span>K{{ number_format($total, 2) }}</span>
            </div>
            <div class="flex items-center justify-between text-white/40 text-xs mb-3">
                <span>Processing fee</span>
                <span>K0.00</span>
            </div>
            <div class="flex items-center justify-between font-bold text-white text-lg">
                <span>Total</span>
                <span class="gradient-text">K{{ number_format($total, 2) }}</span>
            </div>
        </div>
        @endif

        {{-- Max per order note --}}
        <p class="text-white/30 text-xs text-center">Max {{ $event->ticketTypes->first()?->max_per_order ?? 10 }} tickets per order per type</p>

        {{-- Checkout button --}}
        <button type="button"
                wire:click="proceedToCheckout"
                wire:loading.attr="disabled"
                @disabled($total <= 0)
                class="btn-primary w-full text-center text-base py-4 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:scale-100"
                id="proceed-checkout-btn">
            <span wire:loading.remove wire:target="proceedToCheckout">
                @if($total > 0)
                    Checkout · K{{ number_format($total, 2) }}
                @else
                    Select tickets to continue
                @endif
            </span>
            <span wire:loading wire:target="proceedToCheckout" class="flex items-center justify-center gap-2">
                <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Reserving your seats...
            </span>
        </button>

    </div>
</div>
