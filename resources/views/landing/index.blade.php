<x-layouts.guest :title="$gym?->name ?? config('app.name')">
    <div class="min-h-screen bg-ink-950 text-white">
        <header class="relative overflow-hidden border-b border-white/10">
            <div class="absolute -left-24 top-0 size-96 rounded-full bg-gold-400/10 blur-3xl"></div>
            <div class="absolute right-0 top-24 size-72 rounded-full bg-gold-500/10 blur-3xl"></div>

            <nav class="relative mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-gold-300 to-gold-500 font-extrabold text-ink-950 shadow-lg shadow-gold-400/30">
                        {{ substr($gym?->name ?? config('app.name'), 0, 1) }}
                    </div>
                    <div>
                        <p class="font-bold text-white">{{ $gym?->name ?? config('app.name') }}</p>
                        <p class="text-[10px] font-medium uppercase tracking-widest text-gold-400">Gym Management</p>
                    </div>
                </a>
                <div class="flex items-center gap-3">
                    @if (is_saas())
                        <a href="{{ route('register') }}" class="btn-outline border-white/20 text-white hover:border-gold-400 hover:text-gold-400">Create your gym</a>
                    @endif
                    <a href="{{ route('login') }}" class="btn-outline border-white/20 text-white hover:border-gold-400 hover:text-gold-400">Sign in</a>
                    <a href="{{ $gym?->phone ? 'tel:' . $gym->phone : '#plans' }}" class="btn-primary hidden sm:inline-flex">{{ $gym?->phone ?? 'View Plans' }}</a>
                </div>
            </nav>

            <div class="relative mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 lg:px-8 lg:pb-28 lg:pt-24">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <p class="inline-flex items-center gap-2 rounded-full border border-gold-400/30 bg-gold-400/10 px-3 py-1 text-xs font-semibold text-gold-400">
                            <x-icon name="sparkles" class="size-3.5" />
                            Premium Gym Experience
                        </p>
                        <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                            Train harder.<br>
                            <span class="bg-gradient-to-r from-gold-300 to-gold-500 bg-clip-text text-transparent">Grow faster.</span>
                        </h1>
                        <p class="mt-6 max-w-lg text-base leading-relaxed text-ink-300 sm:text-lg">
                            {{ $gym?->name ?? config('app.name') }} is a complete fitness destination — expert coaching,
                            modern equipment, structured memberships and measurable progress.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="#plans" class="btn-primary btn-lg">Explore Memberships</a>
                            <a href="{{ $gym?->phone ? 'tel:' . $gym->phone : route('login') }}" class="btn-outline btn-lg border-white/20 text-white hover:border-gold-400 hover:text-gold-400">
                                <x-icon name="phone" class="size-4" />
                                Contact Us
                            </a>
                        </div>
                        <div class="mt-10 grid max-w-md grid-cols-3 gap-4">
                            <div>
                                <p class="text-2xl font-extrabold text-gold-400">24/7</p>
                                <p class="text-xs text-ink-400">Access</p>
                            </div>
                            <div>
                                <p class="text-2xl font-extrabold text-gold-400">50+</p>
                                <p class="text-xs text-ink-400">Equipment</p>
                            </div>
                            <div>
                                <p class="text-2xl font-extrabold text-gold-400">100%</p>
                                <p class="text-xs text-ink-400">Dedication</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="absolute -inset-6 rounded-3xl bg-gradient-to-br from-gold-400/20 to-transparent blur-2xl"></div>
                        <div class="relative rounded-3xl border border-white/10 bg-white/5 p-8 backdrop-blur">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <span class="flex size-10 items-center justify-center rounded-xl bg-gold-400/15 text-gold-400"><x-icon name="dumbbell" class="size-5" /></span>
                                    <div>
                                        <p class="text-sm font-bold">Strength Zone</p>
                                        <p class="text-xs text-ink-400">Free weights & racks</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <span class="flex size-10 items-center justify-center rounded-xl bg-gold-400/15 text-gold-400"><x-icon name="bolt" class="size-5" /></span>
                                    <div>
                                        <p class="text-sm font-bold">Cardio Deck</p>
                                        <p class="text-xs text-ink-400">Treadmills & bikes</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <span class="flex size-10 items-center justify-center rounded-xl bg-gold-400/15 text-gold-400"><x-icon name="users" class="size-5" /></span>
                                    <div>
                                        <p class="text-sm font-bold">Group Classes</p>
                                        <p class="text-xs text-ink-400">Zumba, yoga & more</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <span class="flex size-10 items-center justify-center rounded-xl bg-gold-400/15 text-gold-400"><x-icon name="trending-up" class="size-5" /></span>
                                    <div>
                                        <p class="text-sm font-bold">Pro Coaching</p>
                                        <p class="text-xs text-ink-400">Certified trainers</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        @if ($announcements->isNotEmpty())
            <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <h2 class="text-center text-3xl font-extrabold tracking-tight">Latest Updates</h2>
                <div class="mt-8 grid gap-4 md:grid-cols-3">
                    @foreach ($announcements as $announcement)
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                            <p class="text-xs font-semibold text-gold-400">{{ \Carbon\Carbon::parse($announcement->created_at)->format('d M Y') }}</p>
                            <h3 class="mt-2 text-base font-bold text-white">{{ $announcement->title }}</h3>
                            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-ink-300">{{ $announcement->message }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section id="plans" class="mx-auto max-w-7xl px-4 pb-24 pt-8 sm:px-6 lg:px-8">
            <h2 class="text-center text-3xl font-extrabold tracking-tight">Membership Plans</h2>
            <p class="mx-auto mt-3 max-w-xl text-center text-sm text-ink-400">
                Simple, transparent pricing. Join today and start your fitness journey.
            </p>

            <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($plans as $plan)
                    <div class="relative flex flex-col rounded-3xl border border-white/10 bg-white/5 p-8 transition-transform hover:-translate-y-1">
                        <h3 class="text-lg font-bold text-white">{{ $plan->name }}</h3>
                        <p class="mt-1 text-xs text-ink-400">{{ $plan->duration_label }}</p>
                        <p class="mt-6">
                            <span class="text-4xl font-extrabold text-gold-400">{{ gym_setting('currency_symbol', '₹') }}{{ number_format($plan->final_amount, 0) }}</span>
                        </p>
                        <ul class="mt-6 flex-1 space-y-3">
                            @forelse (($plan->features ?? []) as $feature)
                                <li class="flex items-start gap-2.5 text-sm text-ink-200">
                                    <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-gold-400/20 text-gold-400"><x-icon name="check" class="size-3" /></span>
                                    {{ $feature }}
                                </li>
                            @empty
                                <li class="flex items-start gap-2.5 text-sm text-ink-200">
                                    <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-gold-400/20 text-gold-400"><x-icon name="check" class="size-3" /></span>
                                    {{ $plan->duration_label }} full gym access
                                </li>
                            @endforelse
                        </ul>
                        <a href="{{ route('login') }}" class="btn-primary mt-8 w-full py-3">Join Now</a>
                    </div>
                @empty
                    <div class="col-span-full rounded-3xl border border-white/10 bg-white/5 p-10 text-center text-sm text-ink-400">
                        New memberships coming soon. Contact the front desk to get started.
                    </div>
                @endforelse
            </div>
        </section>

        <footer class="border-t border-white/10 py-10">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 sm:flex-row sm:px-6 lg:px-8">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-gold-300 to-gold-500 text-sm font-extrabold text-ink-950">
                        {{ substr($gym?->name ?? config('app.name'), 0, 1) }}
                    </div>
                    <p class="text-sm font-bold text-white">{{ $gym?->name ?? config('app.name') }}</p>
                </div>
                <div class="text-center text-xs text-ink-500">
                    @if ($gym?->address)<p>{{ $gym->address }}</p>@endif
                    @if ($gym?->phone || $gym?->email)<p class="mt-1">{{ $gym->phone ?? '' }} {{ $gym?->email ? '· ' . $gym->email : '' }}</p>@endif
                </div>
                <p class="text-xs text-ink-500">&copy; {{ date('Y') }} {{ $gym?->name ?? config('app.name') }}. All rights reserved.</p>
            </div>
        </footer>
    </div>
</x-layouts.guest>
