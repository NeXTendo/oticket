<x-layouts.app :title="$event->name" :metaDescription="Str::limit($event->description, 160)">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Back link --}}
    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-white/50 hover:text-white text-sm transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to events
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- ===== LEFT: Event info ===== --}}
        <div class="lg:col-span-2">

            {{-- Cover image --}}
            @if($event->cover_image_path)
            <div class="relative rounded-2xl overflow-hidden h-72 sm:h-96 mb-8">
                <img src="{{ $event->cover_image_path }}" alt="{{ $event->name }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-[oklch(0.09_0.01_265)]/60 to-transparent"></div>
                <div class="absolute bottom-4 left-4">
                    <span class="badge glass-dark text-white/80 capitalize">{{ $event->category }}</span>
                </div>
            </div>
            @endif

            {{-- Title & meta --}}
            <h1 class="font-display font-extrabold text-3xl sm:text-4xl text-white mb-4 leading-tight">{{ $event->name }}</h1>

            <div class="flex flex-wrap gap-5 mb-8">
                <div class="glass rounded-xl px-4 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 gradient-brand rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-white/40 font-medium">Date & Time</div>
                        <div class="text-white font-semibold text-sm">{{ $event->starts_at->format('D, d M Y · H:i') }}</div>
                        @if($event->ends_at)
                        <div class="text-white/40 text-xs">Ends {{ $event->ends_at->format('H:i') }}</div>
                        @endif
                    </div>
                </div>

                <div class="glass rounded-xl px-4 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 gradient-brand rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-white/40 font-medium">Venue</div>
                        <div class="text-white font-semibold text-sm">{{ $event->venue_name }}</div>
                        @if($event->venue_address)
                        <div class="text-white/40 text-xs">{{ $event->venue_address }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Description --}}
            @if($event->description)
            <div class="mb-8">
                <h2 class="font-display font-bold text-white text-xl mb-3">About this event</h2>
                <p class="text-white/70 leading-relaxed">{{ $event->description }}</p>
            </div>
            @endif

            {{-- Organizer --}}
            <div class="glass rounded-xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 gradient-brand rounded-xl flex items-center justify-center font-bold text-white text-lg font-display shrink-0">
                    {{ substr($event->organizer->name ?? 'O', 0, 1) }}
                </div>
                <div>
                    <div class="text-xs text-white/40 mb-0.5">Organised by</div>
                    <div class="text-white font-semibold">{{ $event->organizer->name ?? 'Unknown Organizer' }}</div>
                </div>
            </div>
        </div>

        {{-- ===== RIGHT: Ticket selector ===== --}}
        <div class="lg:col-span-1">
            <div class="sticky top-32">
                <livewire:ticket-selector :event="$event" />
            </div>
        </div>

    </div>
</div>

</x-layouts.app>
