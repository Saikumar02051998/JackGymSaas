<x-layouts.app
    title="Client Progress"
    description="Track weight and fitness progress for clients."
    :breadcrumbs="[['label' => 'Progress']]">

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-card title="Client Progress">
                <form method="GET" action="{{ route('progress.index') }}" class="mb-4 flex flex-wrap items-end gap-3 border-b border-ink-100 pb-4 dark:border-ink-800">
                    <div class="min-w-48 flex-1">
                        <x-input label="Search" name="search" value="{{ request('search') }}" placeholder="Search by name or member ID..." />
                    </div>
                    <x-button type="submit">
                        <x-icon name="search" class="size-4" />
                        Search
                    </x-button>
                </form>

                @if ($clients->isEmpty())
                    <x-empty-state icon="chart" title="No clients" message="Active clients will appear here for progress tracking." />
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                    <th class="px-5 py-3 font-semibold">Client</th>
                                    <th class="px-5 py-3 font-semibold">Latest Weight</th>
                                    <th class="px-5 py-3 font-semibold">Start Weight</th>
                                    <th class="px-5 py-3 font-semibold">Change</th>
                                    <th class="px-5 py-3 font-semibold">Last Logged</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                @foreach ($clients as $client)
                                    <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-2.5">
                                                <x-avatar :user="$client->user" size="size-7" />
                                                <div>
                                                    <p class="font-medium text-ink-900 dark:text-white">{{ $client->display_name }}</p>
                                                    <p class="text-xs text-ink-400">{{ $client->member_id }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 font-semibold text-ink-900 dark:text-white">{{ $client->latest_weight !== null ? $client->latest_weight . ' kg' : '—' }}</td>
                                        <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $client->first_weight !== null ? $client->first_weight . ' kg' : '—' }}</td>
                                        <td class="px-5 py-4">
                                            @if ($client->change !== null)
                                                @php $positive = $client->change <= 0; @endphp
                                                <x-badge :color="$positive ? 'green' : 'red'">
                                                    {{ $client->change > 0 ? '+' : '' }}{{ $client->change }} kg {{ $positive ? '↓' : '↑' }}
                                                </x-badge>
                                            @else
                                                <span class="text-ink-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $client->last_weight_date ? \Carbon\Carbon::parse($client->last_weight_date)->format('d M Y') : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4">
                        <x-pagination :model="$clients" />
                    </div>
                @endif
            </x-card>
        </div>

        @if (can_manage('progress.manage'))
            <div>
                <x-card title="Log Weight">
                    <form method="POST" action="{{ route('progress.weight') }}" class="space-y-4">
                        @csrf
                        <x-select label="Client" name="client_id" required>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->display_name }}</option>
                            @endforeach
                        </x-select>
                        <div class="grid grid-cols-2 gap-3">
                            <x-input label="Weight (kg)" name="weight" type="number" step="0.1" min="1" max="500" value="{{ old('weight') }}" required />
                            <x-input label="Body fat (%)" name="body_fat" type="number" step="0.1" min="0" max="100" value="{{ old('body_fat') }}" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <x-input label="Height (cm)" name="height" type="number" step="0.1" min="0" max="300" value="{{ old('height') }}" help="Optional, uses health profile if blank" />
                            <x-input label="Date" name="record_date" type="date" value="{{ old('record_date', now()->toDateString()) }}" />
                        </div>
                        <x-button type="submit" class="w-full">
                            <x-icon name="check-badge" class="size-4" />
                            Record Weight
                        </x-button>
                    </form>
                </x-card>
            </div>
        @endif
    </div>
</x-layouts.app>
