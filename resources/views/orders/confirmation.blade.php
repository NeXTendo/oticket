<x-layouts.app title="Booking Confirmed 🎉">
@php $order->load('tickets.ticketType', 'event'); @endphp

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-12 text-center">

    {{-- Success animation --}}
    <div class="w-20 h-20 gradient-brand rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-2xl">
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h1 class="font-display font-extrabold text-4xl text-white mb-2">You're going! 🎉</h1>
    <p class="text-white/50 text-sm mb-2">
        Order <span class="font-mono text-white/70">{{ $order->order_number }}</span>
    </p>
    <p class="text-white/60 text-base mb-10">{{ $order->event->name }} · {{ $order->event->starts_at->format('d M Y') }}</p>

    {{-- Ticket cards --}}
    <div class="space-y-4 text-left mb-10">
        @foreach($order->tickets as $index => $ticket)
        <div class="glass rounded-2xl overflow-hidden">
            {{-- Top colored strip --}}
            <div class="gradient-brand h-1.5"></div>
            <div class="p-5 flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="text-xs text-white/40 uppercase tracking-widest font-semibold mb-1">Ticket {{ $index + 1 }}</div>
                    <div class="font-display font-bold text-white text-xl mb-0.5">{{ $ticket->ticketType->name }}</div>
                    <div class="text-white/60 text-sm mb-3">{{ $order->event->name }}</div>
                    <div class="flex flex-wrap gap-3 text-xs text-white/40">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $order->event->starts_at->format('D, d M Y') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $order->event->venue_name }}
                        </span>
                    </div>
                </div>

                {{-- QR placeholder --}}
                <div class="shrink-0 w-20 h-20 bg-white rounded-xl flex items-center justify-center p-1.5">
                    <div class="w-full h-full bg-gradient-to-br from-[oklch(0.45_0.22_265)] to-[oklch(0.72_0.19_340)] rounded-lg flex items-center justify-center">
                        <svg class="w-8 h-8 text-white opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Ticket code --}}
            <div class="px-5 pb-5">
                <div class="glass rounded-xl px-4 py-2.5 flex items-center justify-between">
                    <span class="text-white/40 text-xs">Ticket Code</span>
                    <span class="font-mono font-bold text-white tracking-widest text-sm">{{ $ticket->ticket_code }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Action buttons --}}
    <div class="flex flex-col sm:flex-row items-center gap-3 justify-center">
        <a href="{{ route('my-tickets') }}" class="btn-primary px-8 py-3.5" id="view-my-tickets">
            View My Tickets
        </a>
        <a href="{{ route('home') }}" class="btn-ghost px-8 py-3.5" id="discover-more">
            Discover More Events
        </a>
    </div>

    <p class="text-white/30 text-xs mt-8">Your ticket details have been saved to your account. Show the QR code at the gate.</p>
</div>
</x-layouts.app>
