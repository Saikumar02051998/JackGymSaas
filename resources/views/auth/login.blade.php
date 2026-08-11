<x-layouts.guest title="Login">
    <div class="flex min-h-screen">
        <div class="relative hidden w-1/2 overflow-hidden bg-ink-950 lg:block">
            <div class="absolute inset-0 bg-gradient-to-br from-ink-900 via-ink-950 to-ink-950"></div>
            <div class="absolute -left-20 top-1/4 size-72 rounded-full bg-gold-400/20 blur-3xl"></div>
            <div class="absolute -right-10 bottom-1/4 size-96 rounded-full bg-gold-500/10 blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(222,188,64,0.08)_1px,transparent_1px)] [background-size:24px_24px]"></div>

            <div class="relative flex h-full flex-col justify-between p-12">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-gold-300 to-gold-500 text-lg font-extrabold text-ink-950 shadow-lg shadow-gold-400/30">
                        {{ substr(config('app.name'), 0, 1) }}
                    </div>
                    <div>
                        <p class="text-lg font-bold text-white">{{ config('app.name') }}</p>
                        <p class="text-[10px] font-medium uppercase tracking-widest text-gold-400">Gym Management</p>
                    </div>
                </a>

                <div class="max-w-md">
                    <h1 class="text-4xl font-extrabold leading-tight tracking-tight text-white">
                        Train harder.<br>
                        <span class="bg-gradient-to-r from-gold-300 to-gold-500 bg-clip-text text-transparent">Grow faster.</span>
                    </h1>
                    <p class="mt-4 text-sm leading-relaxed text-ink-300">
                        A complete management platform for modern gyms — memberships, attendance, payments,
                        coaching and financials in one premium dashboard.
                    </p>
                    <div class="mt-8 space-y-3">
                        @foreach (['Membership & renewal management', 'Attendance with secure check-in', 'Razorpay-powered payments', 'Workout, diet & progress tracking'] as $feature)
                            <div class="flex items-center gap-3 text-sm text-ink-200">
                                <span class="flex size-5 items-center justify-center rounded-full bg-gold-400/20 text-gold-400">
                                    <x-icon name="check" class="size-3" />
                                </span>
                                {{ $feature }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <p class="text-xs text-ink-500">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>

        <div class="flex w-full items-center justify-center px-4 py-12 lg:w-1/2">
            <div class="w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <div class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-gold-300 to-gold-500 font-extrabold text-ink-950">
                            {{ substr(config('app.name'), 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-ink-900 dark:text-white">{{ config('app.name') }}</p>
                            <p class="text-[10px] font-medium uppercase tracking-widest text-gold-600">Gym Management</p>
                        </div>
                    </a>
                </div>

                <div class="animate-slide-up">
                    <h2 class="text-2xl font-bold tracking-tight text-ink-900 dark:text-white">Welcome back</h2>
                    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Sign in to access your dashboard</p>
                </div>

                @if (session('status'))
                    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="card mt-8 animate-slide-up">
                    @csrf
                    <div class="card-body space-y-5">
                        <div>
                            <label for="email" class="label">Email or Phone</label>
                            <div class="relative">
                                <x-icon name="mail" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
                                <input type="text" id="email" name="email" value="{{ old('email') }}"
                                       class="input !pl-10" placeholder="owner@jackgym.test or phone number"
                                       autocomplete="username" autofocus required>
                            </div>
                            @error('email')
                                <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="label">Password</label>
                            <div class="relative">
                                <x-icon name="lock" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
                                <input type="password" id="password" name="password"
                                       class="input !pl-10" placeholder="••••••••" autocomplete="current-password" required>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-sm text-ink-600 dark:text-ink-300">
                                <input type="checkbox" name="remember" class="size-4 rounded border-ink-300 text-gold-500 focus:ring-gold-400" {{ old('remember') ? 'checked' : '' }}>
                                Remember me
                            </label>
                            <a href="{{ route('password.request') }}" class="text-sm font-medium text-gold-600 hover:text-gold-500">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn-primary w-full py-3">
                            Sign in
                            <x-icon name="arrow-left" class="size-4 rotate-180" />
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-xs text-ink-400">Not a member yet? <a href="{{ route('home') }}" class="font-semibold text-gold-600 hover:text-gold-500">Visit our website</a></p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.guest>
