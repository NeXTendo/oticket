<x-layouts.app title="My Tickets">
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="font-display font-extrabold text-3xl text-white mb-2">My Tickets</h1>
    <p class="text-white/50 text-sm mb-8">All tickets purchased with your account</p>

    @forelse($tickets as $ticket)
    <div class="glass rounded-2xl overflow-hidden mb-4">
        <div class="gradient-brand h-1"></div>
        <div class="p-5 flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="text-xs text-[oklch(0.75_0.18_265)] font-semibold uppercase tracking-wider mb-1 capitalize">{{ $ticket->ticketType->name }}</div>
                <div class="font-display font-bold text-white text-lg mb-1">{{ $ticket->event->name }}</div>
                <div class="flex flex-wrap gap-3 text-xs text-white/40">
                    <span>{{ $ticket->event->starts_at->format('D, d M Y · H:i') }}</span>
                    <span>·</span>
                    <span>{{ $ticket->event->venue_name }}</span>
                </div>
            </div>
            <div class="shrink-0">
                <span class="badge {{ $ticket->status === 'valid' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-white/5 text-white/30' }}">
                    {{ ucfirst($ticket->status) }}
                </span>
            </div>
        </div>
        <div class="px-5 pb-5">
            <div class="glass rounded-xl px-4 py-2.5 flex items-center justify-between">
                <span class="text-white/40 text-xs">Ticket Code</span>
                <span class="font-mono font-bold text-white tracking-widest text-sm">{{ $ticket->ticket_code }}</span>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-24">
        <div class="text-5xl mb-4">🎫</div>
        <h2 class="font-display font-bold text-white text-xl mb-2">No tickets yet</h2>
        <p class="text-white/40 text-sm mb-6">Discover events and grab your first ticket!</p>
        <a href="{{ route('home') }}" class="btn-primary px-8 py-3.5" id="browse-events">Browse Events</a>
    </div>
    @endforelse
</div>
</x-layouts.app>
