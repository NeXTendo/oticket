<x-layouts.app>

{{-- ======= HERO SECTION ======= --}}
<section class="relative overflow-hidden" id="hero">
    @if($featuredEvent)
    <div class="relative h-[70vh] min-h-[500px] max-h-[700px]">
        {{-- Background image --}}
        <div class="absolute inset-0">
            <img src="{{ $featuredEvent->cover_image_path }}" alt="{{ $featuredEvent->name }}"
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-[oklch(0.09_0.01_265)] via-black/60 to-black/20"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-black/40 to-transparent"></div>
        </div>

        {{-- Hero Content --}}
        <div class="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-end pb-14">
            <div class="max-w-2xl">
                {{-- Category badge --}}
                <div class="flex items-center gap-3 mb-4">
                    <span class="badge gradient-brand text-white uppercase tracking-widest text-[10px]">
                        ✦ Featured Event
                    </span>
                    <span class="badge glass text-white/80 capitalize">{{ $featuredEvent->category }}</span>
                </div>

                <h1 class="font-display font-extrabold text-4xl sm:text-5xl lg:text-6xl text-white leading-tight mb-4 drop-shadow-xl">
                    {{ $featuredEvent->name }}
                </h1>

                <div class="flex flex-wrap items-center gap-4 text-white/80 text-sm mb-6">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[oklch(0.75_0.18_265)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $featuredEvent->starts_at->format('D, d M Y · H:i') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[oklch(0.75_0.18_265)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $featuredEvent->venue_name }}, {{ Str::before($featuredEvent->venue_address, ',') }}
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('events.show', $featuredEvent->slug) }}" class="btn-primary text-base px-8 py-3.5" id="hero-get-tickets">
                        Get Tickets
                        @if($featuredEvent->starting_price)
                            · From K{{ number_format($featuredEvent->starting_price, 0) }}
                        @endif
                    </a>
                    <a href="{{ route('home') }}#events" class="btn-ghost text-sm">See All Events</a>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="relative py-32 text-center">
        <div class="absolute inset-0 bg-gradient-to-b from-[oklch(0.13_0.015_265)] to-transparent"></div>
        <div class="relative max-w-3xl mx-auto px-4">
            <div class="badge gradient-brand text-white mb-6 mx-auto">✦ Events in Zambia</div>
            <h1 class="font-display font-extrabold text-5xl sm:text-6xl text-white mb-6">Your ticket to <span class="gradient-text">unforgettable</span> experiences</h1>
            <p class="text-white/60 text-lg mb-8">Discover concerts, festivals, tech summits, sports and more — all across Zambia.</p>
        </div>
    </div>
    @endif
</section>

{{-- ======= FILTER BAR ======= --}}
<section class="sticky top-16 z-40 border-b border-white/5 bg-[oklch(0.09_0.01_265)]/95 backdrop-blur-xl" id="filter-bar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2 py-3 overflow-x-auto no-scrollbar">
            @php
                $categories = [
                    'all' => ['label' => 'All Events', 'icon' => '🎫'],
                    'music' => ['label' => 'Music', 'icon' => '🎵'],
                    'tech' => ['label' => 'Tech', 'icon' => '💻'],
                    'festival' => ['label' => 'Festival', 'icon' => '🎪'],
                    'sports' => ['label' => 'Sports', 'icon' => '⚽'],
                    'arts' => ['label' => 'Arts', 'icon' => '🎨'],
                    'nightlife' => ['label' => 'Nightlife', 'icon' => '🌙'],
                ];
                $activeCategory = request('category', 'all');
            @endphp
            @foreach($categories as $slug => $cat)
                <a href="{{ route('home', $slug !== 'all' ? ['category' => $slug] : []) }}"
                   class="shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 whitespace-nowrap
                          {{ $activeCategory === $slug ? 'gradient-brand text-white shadow-lg' : 'text-white/60 hover:text-white hover:bg-white/5' }}"
                   id="filter-{{ $slug }}">
                    <span>{{ $cat['icon'] }}</span>
                    {{ $cat['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ======= EVENTS GRID ======= --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" id="events">

    {{-- Section header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="section-title text-white">
                @if($activeCategory !== 'all')
                    {{ ucfirst($activeCategory) }} Events
                @else
                    Upcoming Events
                @endif
            </h2>
            <p class="text-white/50 text-sm mt-1">{{ $events->total() }} {{ Str::plural('event', $events->total()) }} found</p>
        </div>
        {{-- Sort options placeholder --}}
        <div class="glass px-3 py-2 rounded-xl text-sm text-white/60">
            Soonest first
        </div>
    </div>

    @if($events->isEmpty())
        <div class="text-center py-24">
            <div class="text-5xl mb-4">🎫</div>
            <h3 class="text-xl font-semibold text-white mb-2">No events found</h3>
            <p class="text-white/40">Check back soon or try a different category.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $event)
            <a href="{{ route('events.show', $event->slug) }}"
               class="group block glass rounded-2xl overflow-hidden card-hover"
               id="event-card-{{ $event->id }}">

                {{-- Cover image --}}
                <div class="relative overflow-hidden h-48">
                    @if($event->cover_image_path)
                        <img src="{{ $event->cover_image_path }}"
                             alt="{{ $event->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full gradient-brand flex items-center justify-center">
                            <span class="text-4xl opacity-30">🎫</span>
                        </div>
                    @endif

                    {{-- Date badge --}}
                    <div class="absolute top-3 left-3 glass-dark rounded-xl px-3 py-1.5 text-center min-w-12">
                        <div class="text-[10px] font-bold text-[oklch(0.75_0.18_265)] uppercase tracking-wider">{{ $event->starts_at->format('M') }}</div>
                        <div class="text-white font-bold text-lg leading-none">{{ $event->starts_at->format('d') }}</div>
                    </div>

                    {{-- Category pill --}}
                    <div class="absolute top-3 right-3">
                        <span class="badge glass-dark text-white/80 capitalize text-[11px]">{{ $event->category }}</span>
                    </div>
                </div>

                {{-- Card body --}}
                <div class="p-5">
                    <div class="text-xs text-white/40 font-medium mb-1.5 flex items-center gap-1.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        {{ $event->venue_name }} · {{ $event->starts_at->format('H:i') }}
                    </div>

                    <h3 class="font-display font-bold text-white text-lg leading-tight group-hover:text-[oklch(0.75_0.18_265)] transition-colors duration-200 mb-3 line-clamp-2">
                        {{ $event->name }}
                    </h3>

                    {{-- Price & organizer --}}
                    <div class="flex items-center justify-between">
                        <div>
                            @if($event->starting_price !== null)
                                <div class="text-[oklch(0.75_0.18_265)] font-bold text-sm">From K{{ number_format($event->starting_price, 0) }}</div>
                            @else
                                <div class="text-white/30 text-sm">Free</div>
                            @endif
                        </div>

                        <div class="flex items-center gap-1.5">
                            <div class="w-5 h-5 gradient-brand rounded-full flex items-center justify-center text-[9px] font-bold text-white">
                                {{ substr($event->organizer->name ?? 'O', 0, 1) }}
                            </div>
                            <span class="text-white/40 text-xs truncate max-w-24">{{ $event->organizer->name ?? '' }}</span>
                        </div>
                    </div>

                    {{-- CTA --}}
                    <div class="mt-4 pt-4 border-t border-white/5">
                        <span class="flex items-center justify-center w-full glass text-white text-sm font-semibold py-2.5 rounded-xl group-hover:gradient-brand group-hover:border-transparent transition-all duration-300">
                            Buy Tickets
                            <svg class="w-4 h-4 ml-1.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($events->hasPages())
        <div class="mt-10 flex justify-center">
            {{ $events->links() }}
        </div>
        @endif
    @endif
</section>

{{-- ======= ORGANIZER CTA BANNER ======= --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16" id="organize">
    <div class="relative overflow-hidden rounded-3xl gradient-brand p-10 md:p-14 text-center">
        <div class="absolute inset-0 opacity-20"
             style="background-image: radial-gradient(circle at 20% 80%, white 1px, transparent 1px),radial-gradient(circle at 80% 20%, white 1px, transparent 1px); background-size: 60px 60px;">
        </div>
        <div class="relative">
            <div class="badge glass-dark text-white mb-4 mx-auto">🎤 Organizers</div>
            <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-white mb-4">Ready to sell tickets for your event?</h2>
            <p class="text-white/80 text-lg mb-8 max-w-xl mx-auto">List your event on OTicket and reach thousands of fans across Zambia. Powered by Lipila Mobile Money — no fuss, no bank account needed.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white text-[oklch(0.45_0.22_265)] font-bold px-8 py-4 rounded-xl hover:scale-105 transition-transform duration-200 shadow-xl" id="organize-cta">
                Start Selling Tickets
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</section>

</x-layouts.app>
