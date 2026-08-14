@props(['unreadCount' => 0, 'notifications' => []])

@php $gym = current_gym(); @endphp

<header class="sticky top-0 z-30 hidden h-16 items-center gap-3 border-b border-ink-200 bg-white/80 px-4 backdrop-blur-lg dark:border-ink-800 dark:bg-night-900/80 sm:px-6 lg:flex">
    <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-2.5">
        @if ($gym?->logo)
            <img src="{{ asset('storage/' . $gym->logo) }}" alt="{{ $gym->name }}" class="size-9 shrink-0 rounded-xl object-cover">
        @else
            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-gold-300 to-gold-500 text-sm font-extrabold text-ink-950 shadow-sm shadow-gold-400/40">
                {{ substr($gym?->name ?? config('app.name'), 0, 1) }}
            </div>
        @endif
        <p class="truncate text-sm font-bold tracking-tight text-ink-900 dark:text-white">{{ $gym?->name ?? config('app.name') }}</p>
    </a>
    <div class="flex items-center gap-1.5 ml-auto">
        <button @click="$store.theme.toggle()"
                class="rounded-lg p-2 text-ink-500 transition-colors hover:bg-ink-100 dark:text-ink-300 dark:hover:bg-ink-800"
                :title="$store.theme.dark ? 'Switch to light mode' : 'Switch to dark mode'"
                aria-label="Toggle theme">
            <x-icon name="sun" class="size-5" x-show="$store.theme.dark" x-cloak />
            <x-icon name="moon" class="size-5" x-show="!$store.theme.dark" x-cloak />
        </button>

        <div class="relative" x-data>
            <button @click="$store.notifications.open = !$store.notifications.open" class="relative rounded-lg p-2 text-ink-500 transition-colors hover:bg-ink-100 dark:text-ink-300 dark:hover:bg-ink-800" aria-label="Notifications">
                <x-icon name="bell" class="size-5" />
                <span x-show="$store.notifications.count > 0"
                      x-text="$store.notifications.count > 9 ? '9+' : $store.notifications.count"
                      class="absolute -right-0.5 -top-0.5 flex size-4.5 items-center justify-center rounded-full bg-gold-400 text-[10px] font-bold text-ink-950"
                      x-cloak></span>
            </button>

            <div x-show="$store.notifications.open" @click.away="$store.notifications.open = false" x-cloak
                 class="absolute right-0 top-12 w-80 overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-xl dark:border-ink-700 dark:bg-night-900 animate-scale-in">
                <div class="flex items-center justify-between border-b border-ink-100 px-4 py-3 dark:border-ink-800">
                    <p class="text-sm font-semibold text-ink-900 dark:text-white">Notifications</p>
                    <a href="{{ route('notifications.index') }}" class="text-xs font-medium text-gold-600 hover:text-gold-500">View all</a>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    <template x-for="notification in $store.notifications.items" :key="notification.id">
                        <a :href="notification.url" @click="$store.notifications.read(notification)"
                           class="block border-b border-ink-100 px-4 py-3 transition-colors hover:bg-ink-50 dark:border-ink-800 dark:hover:bg-ink-800">
                            <div class="flex items-start gap-3">
                                <span class="mt-1.5 size-2 shrink-0 rounded-full" :class="notification.read ? 'bg-ink-300' : 'bg-gold-400'"></span>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink-900 dark:text-white" x-text="notification.title"></p>
                                    <p class="mt-0.5 line-clamp-2 text-xs text-ink-500 dark:text-ink-400" x-text="notification.message"></p>
                                    <p class="mt-1 text-[10px] text-ink-400" x-text="notification.time"></p>
                                </div>
                            </div>
                        </a>
                    </template>
                    <div x-show="$store.notifications.items.length === 0 && !$store.notifications.loading" class="px-4 py-8 text-center">
                        <x-icon name="bell" class="mx-auto size-8 text-ink-300" />
                        <p class="mt-2 text-sm text-ink-400">No notifications yet</p>
                    </div>
                    <div x-show="$store.notifications.loading" class="px-4 py-8 text-center">
                        <p class="text-sm text-ink-400">Loading…</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex size-9 items-center justify-center overflow-hidden rounded-full bg-gold-400/20 text-sm font-bold text-gold-700 ring-2 ring-transparent transition-all hover:ring-gold-400/40 dark:text-gold-400" aria-label="Profile menu">
                @if (auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="" class="size-full object-cover">
                @else
                    {{ collect(explode(' ', auth()->user()->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') }}
                @endif
            </button>

            <div x-show="open" @click.away="open = false" x-cloak
                 class="absolute right-0 top-12 w-56 overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-xl dark:border-ink-700 dark:bg-night-900 animate-scale-in">
                <div class="border-b border-ink-100 px-4 py-3 dark:border-ink-800">
                    <p class="truncate text-sm font-semibold text-ink-900 dark:text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-ink-400">{{ auth()->user()->email ?? auth()->user()->phone }}</p>
                </div>
                <div class="p-1.5">
                    <a href="{{ route('profile') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-ink-600 transition-colors hover:bg-ink-100 dark:text-ink-300 dark:hover:bg-ink-800">
                        <x-icon name="user" class="size-4" /> My Profile
                    </a>
                    <a href="{{ route('notifications.index') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-ink-600 transition-colors hover:bg-ink-100 dark:text-ink-300 dark:hover:bg-ink-800">
                        <x-icon name="bell" class="size-4" /> Notifications
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                            <x-icon name="logout" class="size-4" /> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
