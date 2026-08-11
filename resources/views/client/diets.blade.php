<x-layouts.app
    title="My Diet Plans"
    description="Your assigned nutrition plans and meal breakdowns."
    :breadcrumbs="[['label' => 'My Diet']]">

    @forelse ($plans as $plan)
        <x-card class="mb-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-2xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                        <x-icon name="apple" class="size-6" />
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-bold text-ink-900 dark:text-white">{{ $plan->name }}</h2>
                            <x-badge :color="match($plan->status) { 'active' => 'green', 'draft' => 'gray', 'completed' => 'blue', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst($plan->status) }}</x-badge>
                        </div>
                        <p class="text-sm text-ink-400">
                            {{ $plan->goal ?? 'Nutrition plan' }}
                            @if ($plan->start_date || $plan->end_date)
                                · {{ $plan->start_date ? \Carbon\Carbon::parse($plan->start_date)->format('d M Y') : '—' }} &rarr; {{ $plan->end_date ? \Carbon\Carbon::parse($plan->end_date)->format('d M Y') : 'Open' }}
                            @endif
                        </p>
                    </div>
                </div>
                @if ($plan->nutritionist)
                    <div class="text-right">
                        <p class="text-xs text-ink-400">Prepared by</p>
                        <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ $plan->nutritionist->display_name }}</p>
                    </div>
                @endif
            </div>

            @if ($plan->notes)
                <p class="mt-4 rounded-xl bg-ink-100/60 px-4 py-3 text-sm text-ink-600 dark:bg-ink-800/60 dark:text-ink-300">{{ $plan->notes }}</p>
            @endif

            @php
                $totals = [
                    'calories' => (float) $plan->meals->sum('calories'),
                    'protein' => (float) $plan->meals->sum('protein'),
                    'carbs' => (float) $plan->meals->sum('carbs'),
                    'fat' => (float) $plan->meals->sum('fat'),
                ];
            @endphp

            @if ($plan->meals->isEmpty())
                <div class="mt-4 rounded-2xl border border-dashed border-ink-300 px-4 py-8 text-center text-sm text-ink-400 dark:border-ink-700">
                    No meals added to this plan yet.
                </div>
            @else
                <div class="mt-4 overflow-x-auto rounded-xl border border-ink-100 dark:border-ink-800">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 bg-ink-50/60 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800 dark:bg-ink-800/40">
                                <th class="px-4 py-2.5 font-semibold">Meal</th>
                                <th class="px-4 py-2.5 font-semibold">Time</th>
                                <th class="px-4 py-2.5 font-semibold">Food</th>
                                <th class="px-4 py-2.5 font-semibold">Qty</th>
                                <th class="px-4 py-2.5 text-right font-semibold">Calories</th>
                                <th class="px-4 py-2.5 text-right font-semibold">Protein</th>
                                <th class="px-4 py-2.5 text-right font-semibold">Carbs</th>
                                <th class="px-4 py-2.5 text-right font-semibold">Fat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($plan->meals as $meal)
                                <tr>
                                    <td class="px-4 py-2.5 font-semibold text-ink-900 dark:text-white">{{ $meal->meal }}</td>
                                    <td class="px-4 py-2.5 text-ink-600 dark:text-ink-300">{{ $meal->meal_time ?? '—' }}</td>
                                    <td class="px-4 py-2.5">
                                        <p class="font-medium text-ink-900 dark:text-white">{{ $meal->food }}</p>
                                        @if ($meal->notes)
                                            <p class="text-xs text-ink-400">{{ $meal->notes }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-ink-600 dark:text-ink-300">{{ $meal->quantity ?? '—' }}</td>
                                    <td class="px-4 py-2.5 text-right">{{ $meal->calories ? number_format((float) $meal->calories, 0) : '—' }}</td>
                                    <td class="px-4 py-2.5 text-right">{{ $meal->protein ? number_format((float) $meal->protein, 1) . 'g' : '—' }}</td>
                                    <td class="px-4 py-2.5 text-right">{{ $meal->carbs ? number_format((float) $meal->carbs, 1) . 'g' : '—' }}</td>
                                    <td class="px-4 py-2.5 text-right">{{ $meal->fat ? number_format((float) $meal->fat, 1) . 'g' : '—' }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-gold-400/10 font-semibold text-ink-900 dark:text-white">
                                <td class="px-4 py-3" colspan="4">Daily Totals</td>
                                <td class="px-4 py-3 text-right">{{ number_format($totals['calories'], 0) }} kcal</td>
                                <td class="px-4 py-3 text-right">{{ number_format($totals['protein'], 1) }}g</td>
                                <td class="px-4 py-3 text-right">{{ number_format($totals['carbs'], 1) }}g</td>
                                <td class="px-4 py-3 text-right">{{ number_format($totals['fat'], 1) }}g</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    @empty
        <x-empty-state icon="apple" title="No diet plans yet" message="Your nutritionist will assign diet plans that appear here." />
    @endforelse
</x-layouts.app>
