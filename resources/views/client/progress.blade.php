<x-layouts.app
    title="My Progress"
    description="Track your weight, measurements and fitness goals."
    :breadcrumbs="[['label' => 'My Progress']]">

    @php
        $latest = $client->weightRecords->first();
        $first = $client->weightRecords->last();
        $chartRecords = $client->weightRecords->reverse()->values();
        $change = $latest && $first && (float) $first->weight !== (float) $latest->weight
            ? number_format((float) $latest->weight - (float) $first->weight, 1)
            : null;
    @endphp

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Latest Weight" :value="$latest ? $latest->weight . ' kg' : '—'" icon="trending-up" />
        <x-stat label="BMI" :value="$latest?->bmi ?? $client->healthProfile?->bmi ?? '—'" icon="chart" />
        <x-stat label="Goal Weight" :value="$client->healthProfile?->goal_weight ? $client->healthProfile->goal_weight . ' kg' : '—'" icon="target" />
        <x-stat label="Active Goals" :value="$client->fitnessGoals->where('status', 'active')->count()" icon="check-badge" />
    </div>

    @if ($change !== null)
        <div class="mt-4">
            <x-badge :color="$change > 0 ? 'red' : 'green'">
                {{ $change > 0 ? '+' : '' }}{{ $change }} kg {{ $change > 0 ? 'gained' : 'lost' }} since {{ \Carbon\Carbon::parse($first->record_date)->format('d M') }}
            </x-badge>
        </div>
    @endif

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <x-card title="Weight Trend" class="lg:col-span-2">
            @if ($chartRecords->isEmpty())
                <div class="flex h-64 items-center justify-center text-sm text-ink-400">No weight records yet. Log your first entry below.</div>
            @else
                <div class="h-64">
                    <canvas x-data="chart($el)" x-init="init()"
                            data-chart='{!! json_encode([
                                'type' => 'line',
                                'data' => [
                                    'labels' => $chartRecords->pluck('record_date')->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M')),
                                    'datasets' => [
                                        ['label' => 'Weight (kg)', 'data' => $chartRecords->pluck('weight')->map(fn ($w) => (float) $w), 'borderColor' => '#d4a63c', 'backgroundColor' => 'rgba(212,166,60,0.12)', 'fill' => true, 'tension' => 0.35],
                                    ],
                                ],
                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'></canvas>
                </div>
            @endif
        </x-card>

        <x-card title="Log Today's Progress">
            <form action="{{ route('client.progress.store') }}" method="POST" class="space-y-4">
                @csrf

                <x-input name="weight" label="Weight (kg)" type="number" step="0.1" min="1" max="500" :required="true" value="{{ old('weight') }}" icon="trending-up" />
                <x-input name="record_date" label="Record Date" type="date" value="{{ old('record_date', now()->toDateString()) }}" />

                <div>
                    <p class="label">Measurements (cm)</p>
                    <div class="grid grid-cols-2 gap-3">
                        <x-input name="chest" label="Chest" type="number" step="0.1" value="{{ old('chest') }}" />
                        <x-input name="waist" label="Waist" type="number" step="0.1" value="{{ old('waist') }}" />
                        <x-input name="hip" label="Hip" type="number" step="0.1" value="{{ old('hip') }}" />
                        <x-input name="arms" label="Arms" type="number" step="0.1" value="{{ old('arms') }}" />
                        <x-input name="thigh" label="Thigh" type="number" step="0.1" value="{{ old('thigh') }}" class="col-span-2" />
                    </div>
                </div>

                <div class="rounded-xl bg-ink-100/60 p-4 dark:bg-ink-800/60">
                    <p class="label">Set a Fitness Goal</p>
                    <div class="space-y-3">
                        <x-select name="goal_type" placeholder="No goal (optional)">
                            @foreach (['weight_loss' => 'Weight Loss', 'muscle_gain' => 'Muscle Gain', 'endurance' => 'Endurance', 'flexibility' => 'Flexibility', 'general' => 'General Fitness'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('goal_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </x-select>
                        <div class="grid grid-cols-2 gap-3">
                            <x-input name="starting_value" label="Start" type="number" step="0.1" value="{{ old('starting_value') }}" />
                            <x-input name="target_value" label="Target" type="number" step="0.1" value="{{ old('target_value') }}" />
                        </div>
                        <x-input name="target_date" label="Target Date" type="date" value="{{ old('target_date') }}" />
                    </div>
                </div>

                <x-button type="submit" variant="primary" class="w-full justify-center">
                    <x-icon name="save" class="size-4" />
                    Save Entry
                </x-button>
            </form>
        </x-card>
    </div>

    @if ($client->fitnessGoals->isNotEmpty())
        <x-card title="Fitness Goals" class="mt-6">
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($client->fitnessGoals as $goal)
                    <div class="rounded-xl border border-ink-100 p-4 dark:border-ink-800">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-ink-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $goal->type)) }}</p>
                            <x-badge :color="match($goal->status) { 'active' => 'green', 'achieved' => 'blue', 'abandoned' => 'red', default => 'gray' }">{{ ucfirst($goal->status) }}</x-badge>
                        </div>
                        <p class="mt-1 text-xs text-ink-400">
                            {{ $goal->starting_value ?? '—' }} &rarr; {{ $goal->target_value ?? '—' }}
                            @if ($goal->target_date)
                                · by {{ \Carbon\Carbon::parse($goal->target_date)->format('d M Y') }}
                            @endif
                        </p>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-ink-100 dark:bg-ink-800">
                            <div class="h-full rounded-full bg-gradient-to-r from-gold-300 to-gold-500" style="width: {{ min((float) $goal->progress_percent, 100) }}%"></div>
                        </div>
                        <p class="mt-1.5 text-right text-xs font-semibold text-ink-500 dark:text-ink-400">{{ round((float) $goal->progress_percent) }}%</p>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        @if ($client->weightRecords->isNotEmpty())
            <x-card title="Weight History" :padding="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                <th class="px-5 py-3 font-semibold">Date</th>
                                <th class="px-5 py-3 font-semibold">Weight</th>
                                <th class="px-5 py-3 font-semibold">BMI</th>
                                <th class="px-5 py-3 font-semibold"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($client->weightRecords as $record)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-ink-900 dark:text-white">{{ \Carbon\Carbon::parse($record->record_date)->format('d M Y') }}</td>
                                    <td class="px-5 py-3">{{ $record->weight }} kg</td>
                                    <td class="px-5 py-3">{{ $record->bmi ?? '—' }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <form method="POST" action="{{ route('client.progress.destroy', $record) }}"
                                              x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Delete record?', message: 'This weight entry will be permanently removed.', confirmText: 'Delete' } })">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 transition-colors hover:text-red-600" aria-label="Delete record">
                                                <x-icon name="trash" class="size-4" />
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif

        @if ($client->bodyMeasurements->isNotEmpty())
            <x-card title="Body Measurements" :padding="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                <th class="px-5 py-3 font-semibold">Date</th>
                                <th class="px-5 py-3 font-semibold">Chest</th>
                                <th class="px-5 py-3 font-semibold">Waist</th>
                                <th class="px-5 py-3 font-semibold">Hip</th>
                                <th class="px-5 py-3 font-semibold">Arms</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($client->bodyMeasurements as $measurement)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-ink-900 dark:text-white">{{ \Carbon\Carbon::parse($measurement->record_date)->format('d M Y') }}</td>
                                    <td class="px-5 py-3">{{ $measurement->chest ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $measurement->waist ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $measurement->hip ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $measurement->arms ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif
    </div>
</x-layouts.app>
