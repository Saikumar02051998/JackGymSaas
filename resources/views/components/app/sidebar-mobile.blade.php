@props(['menu' => []])

@php $gym = current_gym(); @endphp

<div x-show="mobileOpen" x-cloak
     class="fixed inset-0 z-50 lg:hidden"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="mobileOpen = false">
    <div class="absolute inset-0 bg-ink-950/60 backdrop-blur-sm"></div>
    <aside class="absolute inset-y-0 left-0 flex w-72 max-w-[85vw] flex-col bg-white shadow-2xl dark:bg-night-900"
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-150"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           @click.stop>
        <div class="flex h-16 items-center justify-between border-b border-ink-100 px-5 dark:border-ink-800">
            <div class="flex items-center gap-2.5">
                @if ($gym?->logo)
                    <img src="{{ asset('storage/' . $gym->logo) }}" alt="{{ $gym->name }}" class="size-8 shrink-0 rounded-lg object-cover">
                @else
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-gold-300 to-gold-500 text-sm font-extrabold text-ink-950">
                        {{ substr($gym?->name ?? config('app.name'), 0, 1) }}
                    </div>
                @endif
                <p class="truncate text-sm font-bold text-ink-900 dark:text-white">{{ $gym?->name ?? config('app.name') }}</p>
            </div>
            <button @click="mobileOpen = false" class="rounded-lg p-2 text-ink-500 hover:bg-ink-100 dark:hover:bg-ink-800">
                <x-icon name="x" class="size-5" />
            </button>
        </div>

        <nav class="flex-1 space-y-5 overflow-y-auto px-4 py-5">
            @foreach ($menu as $group => $items)
                @php
                    $routeName = request()->route()?->getName() ?? '';
                    $bestIndex = null;
                    $bestScore = 0;

                    foreach ($items as $index => $item) {
                        $score = 0;

                        if (isset($item['url'])) {
                            if (request()->fullUrlIs($item['url'])) {
                                $score = 1000;
                            }
                        } else {
                            $r = $item['route'];

                            if ($routeName === $r) {
                                $score = 100;
                            } elseif (str_starts_with($routeName, $r . '.')) {
                                $score = 80;
                            } else {
                                $pos = strrpos($r, '.');
                                $module = $pos === false ? $r : substr($r, 0, $pos);
                                if ($routeName === $module || str_starts_with($routeName, $module . '.')) {
                                    $score = 50 + (substr_count($module, '.') + 1) * 10;
                                }
                            }
                        }

                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $bestIndex = $index;
                        }
                    }
                @endphp
                <div>
                    <p class="mb-1.5 px-3 text-[10px] font-bold uppercase tracking-widest text-ink-400">{{ $group }}</p>
                    <ul class="space-y-0.5">
                        @foreach ($items as $index => $item)
                            <li>
                                <a href="{{ isset($item['url']) ? $item['url'] : route($item['route']) }}"
                                   @class([
                                       'sidebar-link',
                                       'sidebar-link-active' => $index === $bestIndex && $bestScore > 0,
                                   ])
                                   @click="mobileOpen = false">
                                    <x-icon :name="$item['icon']" class="size-5 shrink-0" />
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <div class="border-t border-ink-100 p-4 dark:border-ink-800">
            <a href="{{ route('profile') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-ink-100 dark:hover:bg-ink-800" @click="mobileOpen = false">
                <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gold-400/20 text-sm font-bold text-gold-700 dark:text-gold-400">
                    {{ collect(explode(' ', auth()->user()->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-ink-900 dark:text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-ink-400">{{ \App\Support\Menu::roleLabel() }}</p>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="sidebar-link w-full text-red-500 hover:bg-red-50 hover:text-red-600 dark:text-red-400 dark:hover:bg-red-500/10">
                    <x-icon name="logout" class="size-5" />
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>
</div>
