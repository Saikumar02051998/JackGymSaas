<x-layouts.guest title="Forgot Password">
    @php $gym = current_gym(); @endphp
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                    @if ($gym?->logo)
                        <img src="{{ asset('storage/' . $gym->logo) }}" alt="{{ $gym->name }}" class="size-10 rounded-xl object-cover">
                    @else
                        <div class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-gold-300 to-gold-500 font-extrabold text-ink-950">
                            {{ substr($gym?->name ?? config('app.name'), 0, 1) }}
                        </div>
                    @endif
                    <div class="text-left">
                        <p class="font-bold text-ink-900 dark:text-white">{{ $gym?->name ?? config('app.name') }}</p>
                        <p class="text-[10px] font-medium uppercase tracking-widest text-gold-600">Gym Management</p>
                    </div>
                </a>
            </div>

            <div class="animate-slide-up text-center">
                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-gold-400/15 text-gold-600 dark:text-gold-400">
                    <x-icon name="lock" class="size-6" />
                </div>
                <h2 class="mt-4 text-2xl font-bold tracking-tight text-ink-900 dark:text-white">Forgot your password?</h2>
                <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Enter your email and we will send you a reset link.</p>
            </div>

            @if (session('success'))
                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('status'))
                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="card mt-8 animate-slide-up">
                @csrf
                <div class="card-body space-y-5">
                    <div>
                        <label for="email" class="label">Email address</label>
                        <div class="relative">
                            <x-icon name="mail" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   class="input !pl-10" placeholder="you@example.com" autocomplete="email" required autofocus>
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary w-full py-3">
                        Send reset link
                        <x-icon name="arrow-left" class="size-4 rotate-180" />
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-ink-500 dark:text-ink-400">
                Remembered it?
                <a href="{{ route('login') }}" class="font-semibold text-gold-600 hover:text-gold-500">Back to login</a>
            </p>
        </div>
    </div>
</x-layouts.guest>
