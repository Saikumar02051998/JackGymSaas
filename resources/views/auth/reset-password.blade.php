<x-layouts.guest title="Reset Password">
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

            <div class="animate-slide-up">
                <h2 class="text-2xl font-bold tracking-tight text-ink-900 dark:text-white">Set a new password</h2>
                <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Choose a strong password you will use to sign in.</p>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="card mt-8 animate-slide-up">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

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

                    <div>
                        <label for="password" class="label">New password</label>
                        <div class="relative">
                            <x-icon name="lock" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
                            <input type="password" id="password" name="password"
                                   class="input !pl-10" placeholder="••••••••" autocomplete="new-password" required>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="label">Confirm new password</label>
                        <div class="relative">
                            <x-icon name="lock" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="input !pl-10" placeholder="••••••••" autocomplete="new-password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary w-full py-3">
                        Reset password
                        <x-icon name="arrow-left" class="size-4 rotate-180" />
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-ink-500 dark:text-ink-400">
                <a href="{{ route('login') }}" class="font-semibold text-gold-600 hover:text-gold-500">Back to login</a>
            </p>
        </div>
    </div>
</x-layouts.guest>
