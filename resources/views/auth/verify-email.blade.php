<x-layouts.guest title="Verify Email">
    @php
        $brandLogo = brand_logo();
        $brandName = brand_name();
    @endphp
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
                    @if ($brandLogo)
                        <img src="{{ asset('storage/' . $brandLogo) }}" alt="{{ $brandName }}" class="size-10 rounded-xl object-cover">
                    @else
                        <div class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-gold-300 to-gold-500 font-extrabold text-ink-950">
                            {{ substr($brandName, 0, 1) }}
                        </div>
                    @endif
                    <div class="text-left">
                        <p class="font-bold text-ink-900 dark:text-white">{{ $brandName }}</p>
                        <p class="text-[10px] font-medium uppercase tracking-widest text-gold-600">Gym Management</p>
                    </div>
                </a>
            </div>

            <div class="animate-slide-up text-center">
                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-gold-400/15 text-gold-600 dark:text-gold-400">
                    <x-icon name="mail" class="size-6" />
                </div>
                <h2 class="mt-4 text-2xl font-bold tracking-tight text-ink-900 dark:text-white">Verify your email</h2>
                <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">We sent a 6-digit code to <span class="font-semibold text-ink-700 dark:text-ink-200">{{ $email }}</span>. Enter it below to activate your account.</p>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.verify') }}" class="card mt-8 animate-slide-up">
                @csrf
                <div class="card-body space-y-5">
                    <div>
                        <label for="otp" class="label">Verification code</label>
                        <div class="relative">
                            <x-icon name="lock" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
                            <input type="text" id="otp" name="otp" value="{{ old('otp') }}" maxlength="6"
                                   inputmode="numeric" autocomplete="one-time-code"
                                   class="input !pl-10 text-center font-mono text-lg tracking-[0.5em]" placeholder="••••••" required autofocus>
                        </div>
                        @error('otp')
                            <p class="mt-1.5 text-xs font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary w-full py-3">
                        Verify email
                        <x-icon name="check" class="size-4" />
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('register.verify.resend') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full text-center text-sm text-ink-500 hover:text-gold-600 dark:text-ink-400">
                    Didn't get a code? <span class="font-semibold text-gold-600">Resend</span>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-ink-500 dark:text-ink-400">
                <a href="{{ route('login') }}" class="font-semibold text-gold-600 hover:text-gold-500">Back to login</a>
            </p>
        </div>
    </div>
</x-layouts.guest>
