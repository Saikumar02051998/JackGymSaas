<x-layouts.app
    title="{{ $diet->name }}"
    description="Diet plan details"
    :breadcrumbs="[['label' => 'Diet Plans', 'url' => route('diets.index')], ['label' => $diet->name]]">

    <x-slot name="actions">
        @if (can_manage('diets.manage'))
            <a href="{{ route('diets.edit', $diet) }}" class="btn-outline btn-sm">
                <x-icon name="pencil" class="size-4" />
                Edit
            </a>
            <form method="POST" action="{{ route('diets.toggle', $diet) }}" class="inline">
                @csrf
                <x-button type="submit" variant="outline" size="sm">
                    <x-icon name="refresh" class="size-4" />
                    {{ $diet->status === 'active' ? 'Set Draft' : 'Activate' }}
                </x-button>
            </form>
            <form method="POST" action="{{ route('diets.destroy', $diet) }}"
                  x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Delete plan?', message: 'This will permanently delete this diet plan.', confirmText: 'Delete' } })">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="ghost" size="sm" class="!text-red-500">
                    <x-icon name="trash" class="size-4" />
                </x-button>
            </form>
        @endif
    </x-slot>

    <x-card>
        <div class="flex flex-wrap items-center gap-4">
            <span class="avatar-lg">
                <x-icon name="apple" class="size-7" />
            </span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-bold text-ink-900 dark:text-white">{{ $diet->name }}</h2>
                    <x-badge :color="match($diet->status) { 'active' => 'green', 'draft' => 'gray', 'completed' => 'blue', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst($diet->status) }}</x-badge>
                </div>
                <p class="mt-0.5 text-sm text-ink-400">
                    For <a href="{{ route('clients.show', $diet->client_id) }}" class="font-medium text-gold-600 hover:underline">{{ $diet->client->display_name }}</a>
                    @if ($diet->start_date)
                        &middot; {{ \Carbon\Carbon::parse($diet->start_date)->format('d M') }}{{ $diet->end_date ? ' → ' . \Carbon\Carbon::parse($diet->end_date)->format('d M Y') : '' }}
                    @endif
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs text-ink-400">Meals</p>
                <p class="text-2xl font-extrabold text-ink-900 dark:text-white">{{ $diet->meals->count() }}</p>
            </div>
        </div>
        @if ($diet->goal)
            <p class="mt-4 rounded-xl bg-gold-400/10 p-4 text-sm font-medium text-gold-700 dark:text-gold-300">
                <span class="font-bold">Goal:</span> {{ $diet->goal }}
            </p>
        @endif

        @php
            $totalCalories = $diet->meals->sum(fn ($m) => (float) $m->calories);
            $totalProtein = $diet->meals->sum(fn ($m) => (float) $m->protein);
            $totalCarbs = $diet->meals->sum(fn ($m) => (float) $m->carbs);
            $totalFat = $diet->meals->sum(fn ($m) => (float) $m->fat);
        @endphp
        @if ($totalCalories > 0)
            <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl bg-ink-50 p-4 text-center dark:bg-ink-800/50">
                    <p class="text-xs text-ink-400">Calories</p>
                    <p class="mt-1 text-xl font-extrabold text-ink-900 dark:text-white">{{ round($totalCalories) }}</p>
                </div>
                <div class="rounded-xl bg-ink-50 p-4 text-center dark:bg-ink-800/50">
                    <p class="text-xs text-ink-400">Protein</p>
                    <p class="mt-1 text-xl font-extrabold text-gold-600 dark:text-gold-400">{{ round($totalProtein) }}g</p>
                </div>
                <div class="rounded-xl bg-ink-50 p-4 text-center dark:bg-ink-800/50">
                    <p class="text-xs text-ink-400">Carbs</p>
                    <p class="mt-1 text-xl font-extrabold text-ink-900 dark:text-white">{{ round($totalCarbs) }}g</p>
                </div>
                <div class="rounded-xl bg-ink-50 p-4 text-center dark:bg-ink-800/50">
                    <p class="text-xs text-ink-400">Fat</p>
                    <p class="mt-1 text-xl font-extrabold text-ink-900 dark:text-white">{{ round($totalFat) }}g</p>
                </div>
            </div>
        @endif
    </x-card>

    @if ($diet->meals->isEmpty())
        <x-card class="mt-6">
            <div class="p-8">
                <x-empty-state icon="apple" title="No meals" message="Add food items to this diet plan." />
            </div>
        </x-card>
    @else
        @php
            $grouped = $diet->meals->groupBy('meal');
        @endphp
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            @foreach ($grouped as $mealName => $meals)
                <x-card :title="$mealName ?: 'General'" :padding="false">
                    <div class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($meals as $meal)
                            <div class="px-5 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-ink-900 dark:text-white">{{ $meal->food }}</p>
                                        <p class="mt-0.5 text-xs text-ink-400">
                                            {{ $meal->quantity ?? '—' }}
                                            @if ($meal->meal_time)
                                                &middot; {{ $meal->meal_time }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right text-sm">
                                        @if ($meal->calories)
                                            <p class="font-bold text-ink-900 dark:text-white">{{ round($meal->calories) }} kcal</p>
                                        @endif
                                        <p class="mt-0.5 text-xs text-ink-400">
                                            {{ $meal->protein ? 'P ' . round($meal->protein) . 'g' : '' }}{{ $meal->protein && $meal->carbs ? ' · ' : '' }}{{ $meal->carbs ? 'C ' . round($meal->carbs) . 'g' : '' }}{{ ($meal->protein || $meal->carbs) && $meal->fat ? ' · ' : '' }}{{ $meal->fat ? 'F ' . round($meal->fat) . 'g' : '' }}
                                        </p>
                                    </div>
                                </div>
                                @if ($meal->notes)
                                    <p class="mt-2 text-sm leading-relaxed text-ink-500 dark:text-ink-400">{{ $meal->notes }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif

    @if ($diet->notes)
        <x-card title="Notes" class="mt-6">
            <p class="whitespace-pre-line text-sm leading-relaxed text-ink-600 dark:text-ink-300">{{ $diet->notes }}</p>
        </x-card>
    @endif
</x-layouts.app>
