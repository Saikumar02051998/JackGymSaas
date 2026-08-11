@props(['unreadCount' => 0])

<header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-ink-200 bg-white/80 px-4 backdrop-blur-lg dark:border-ink-800 dark:bg-night-900/80 lg:hidden">
    <button @click="mobileOpen = true" class="rounded-lg p-2 text-ink-500 hover:bg-ink-100 dark:hover:bg-ink-800">
        <x-icon name="menu" class="size-6" />
    </button>
    <div class="flex items-center gap-2.5">
        <div class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-gold-300 to-gold-500 text-sm font-extrabold text-ink-950">
            {{ substr(config('app.name'), 0, 1) }}
        </div>
        <p class="text-sm font-bold text-ink-900 dark:text-white">{{ config('app.name') }}</p>
    </div>
    <div class="ml-auto flex items-center gap-1">
        <button @click="$store.theme.toggle()" class="rounded-lg p-2 text-ink-500 hover:bg-ink-100 dark:text-ink-300 dark:hover:bg-ink-800" aria-label="Toggle theme">
            <x-icon name="sun" class="size-5" x-show="$store.theme.dark" x-cloak />
            <x-icon name="moon" class="size-5" x-show="!$store.theme.dark" x-cloak />
        </button>
        <a href="{{ route('notifications.index') }}" class="relative rounded-lg p-2 text-ink-500 hover:bg-ink-100 dark:text-ink-300 dark:hover:bg-ink-800">
            <x-icon name="bell" class="size-5" />
            @if ($unreadCount > 0)
                <span class="absolute -right-0.5 -top-0.5 flex size-4.5 items-center justify-center rounded-full bg-gold-400 text-[10px] font-bold text-ink-950">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
            @endif
        </a>
    </div>
</header>
