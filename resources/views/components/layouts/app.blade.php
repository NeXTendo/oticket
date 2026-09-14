<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? "$title — OTicket" : 'OTicket · Events & Tickets in Zambia' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Discover and book tickets for the best events in Zambia — concerts, festivals, sports, conferences and more.' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col">

    {{-- Navigation --}}
    <header class="sticky top-0 z-50 glass-dark border-b border-white/5" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0" id="nav-logo">
                    <div class="w-8 h-8 gradient-brand rounded-lg flex items-center justify-center font-display font-bold text-sm text-white shadow-lg">O</div>
                    <span class="font-display font-bold text-lg tracking-tight text-white">OTicket</span>
                </a>

                {{-- Desktop Nav Links --}}
                <nav class="hidden md:flex items-center gap-1">
                    @foreach([
                        ['label' => 'Music', 'cat' => 'music'],
                        ['label' => 'Tech', 'cat' => 'tech'],
                        ['label' => 'Festivals', 'cat' => 'festival'],
                        ['label' => 'Sports', 'cat' => 'sports'],
                        ['label' => 'Arts', 'cat' => 'arts'],
                    ] as $cat)
                    <a href="{{ route('home', ['category' => $cat['cat']]) }}"
                       class="px-3 py-1.5 text-sm text-white/60 hover:text-white hover:bg-white/5 rounded-lg transition-all duration-150 font-medium">
                        {{ $cat['label'] }}
                    </a>
                    @endforeach
                </nav>

                {{-- Right Actions --}}
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('my-tickets') }}" class="hidden sm:flex items-center gap-2 text-sm text-white/70 hover:text-white transition-colors font-medium" id="nav-my-tickets">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            My Tickets
                        </a>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 glass px-3 py-1.5 rounded-xl text-sm font-medium text-white hover:bg-white/10 transition-all" id="nav-user-menu">
                                <div class="w-6 h-6 gradient-brand rounded-full flex items-center justify-center text-xs font-bold">{{ substr(auth()->user()->name, 0, 1) }}</div>
                                <span class="hidden sm:block">{{ explode(' ', auth()->user()->name)[0] }}</span>
                                <svg class="w-3 h-3 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-44 glass-dark rounded-xl shadow-2xl py-1 z-50">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-white/70 hover:text-white hover:bg-white/5 transition-colors" id="nav-logout">Sign out</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-white/70 hover:text-white font-medium transition-colors" id="nav-login">Sign in</a>
                        <a href="{{ route('register') }}" class="btn-primary text-sm px-4 py-2 rounded-lg" id="nav-register">Get Started</a>
                    @endauth

                    {{-- Mobile menu toggle --}}
                    <button @click="mobileOpen = !mobileOpen" class="md:hidden glass w-9 h-9 rounded-lg flex items-center justify-center" id="nav-mobile-toggle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileOpen" x-transition class="md:hidden border-t border-white/5 px-4 py-3 space-y-1">
            @foreach([
                ['label' => 'Music', 'cat' => 'music'],
                ['label' => 'Tech', 'cat' => 'tech'],
                ['label' => 'Festivals', 'cat' => 'festival'],
                ['label' => 'Sports', 'cat' => 'sports'],
                ['label' => 'Arts', 'cat' => 'arts'],
            ] as $cat)
            <a href="{{ route('home', ['category' => $cat['cat']]) }}"
               class="block px-3 py-2 text-sm text-white/70 hover:text-white hover:bg-white/5 rounded-lg transition-all">
                {{ $cat['label'] }}
            </a>
            @endforeach
            @auth
                <a href="{{ route('my-tickets') }}" class="block px-3 py-2 text-sm text-white/70 hover:text-white hover:bg-white/5 rounded-lg transition-all">My Tickets</a>
            @endauth
        </div>
    </header>

    {{-- Main Content --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-white/5 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 gradient-brand rounded-lg flex items-center justify-center font-display font-bold text-xs text-white">O</div>
                    <span class="font-display font-bold text-white">OTicket</span>
                    <span class="text-white/30 text-sm ml-2">© {{ date('Y') }}</span>
                </div>
                <div class="flex items-center gap-6 text-sm text-white/40">
                    <span>Events in Zambia</span>
                    <span>·</span>
                    <span>Powered by Lipila Mobile Money</span>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>
