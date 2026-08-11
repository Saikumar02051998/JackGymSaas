@props(['menu' => []])

@php $gym = current_gym(); @endphp

<aside class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col border-r border-ink-200 bg-white dark:border-ink-800 dark:bg-night-900 lg:flex">
    <div class="flex h-16 items-center gap-3 border-b border-ink-100 px-6 dark:border-ink-800">
        @if ($gym?->logo)
            <img src="{{ asset('storage/' . $gym->logo) }}" alt="{{ $gym->name }}" class="size-9 rounded-xl object-cover">
        @else
            <div class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-gold-300 to-gold-500 text-sm font-extrabold text-ink-950 shadow-sm shadow-gold-400/40">
                {{ substr($gym?->name ?? config('app.name'), 0, 1) }}
            </div>
        @endif
        <div class="min-w-0">
            <p class="truncate text-sm font-bold tracking-tight text-ink-900 dark:text-white">{{ $gym?->name ?? config('app.name') }}</p>
            <p class="text-[10px] font-medium uppercase tracking-widest text-gold-600 dark:text-gold-400">Gym Management</p>
        </div>
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
                <p class="mb-1.5 px-3 text-[10px] font-bold uppercase tracking-widest text-ink-400 dark:text-ink-500">{{ $group }}</p>
                <ul class="space-y-0.5">
                    @foreach ($items as $index => $item)
                        <li>
                            <a href="{{ isset($item['url']) ? $item['url'] : route($item['route']) }}"
                               @class([
                                   'sidebar-link',
                                   'sidebar-link-active' => $index === $bestIndex && $bestScore > 0,
                               ])>
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
        <a href="{{ route('profile') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors hover:bg-ink-100 dark:hover:bg-ink-800">
            <div class="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gold-400/20 text-sm font-bold text-gold-700 dark:text-gold-400">
                @if (auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="" class="size-full object-cover">
                @else
                    {{ collect(explode(' ', auth()->user()->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') }}
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-ink-900 dark:text-white">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-ink-400">{{ \App\Support\Menu::roleLabel() }}</p>
            </div>
            <x-icon name="chevron-right" class="size-4 text-ink-400" />
        </a>
    </div>
</aside>
