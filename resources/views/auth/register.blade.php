<x-layouts.app title="Create Account">
<div class="min-h-[80vh] flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-14 h-14 gradient-brand rounded-2xl flex items-center justify-center font-display font-bold text-2xl text-white mx-auto mb-4 shadow-xl">O</div>
            <h1 class="font-display font-extrabold text-3xl text-white mb-1">Create your account</h1>
            <p class="text-white/50 text-sm">Get tickets to the best events in Zambia</p>
        </div>

        <div class="glass rounded-2xl p-8">
            @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6 text-red-400 text-sm">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="register-form">
                @csrf
                @if(request('redirect'))
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                @endif

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-2" for="name">Full name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                               class="input-field" placeholder="Mwila Mwansa">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-2" for="email">Email address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="input-field" placeholder="you@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-2" for="password">Password</label>
                        <input type="password" name="password" id="password" required
                               class="input-field" placeholder="Minimum 8 characters">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-white/70 mb-2" for="password_confirmation">Confirm password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="input-field" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full py-4 mt-6 text-base" id="register-submit">
                    Create Account
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-white/40 text-sm">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-[oklch(0.75_0.18_265)] hover:text-white font-medium transition-colors" id="register-to-login">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</div>
</x-layouts.app>
