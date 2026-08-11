<x-layouts.app
    title="Lead Report"
    description="Lead acquisition and conversion overview."
    :breadcrumbs="[['label' => 'Reports'], ['label' => 'Leads']]">

    <x-card>
        <form method="GET" action="{{ route('reports.leads') }}" class="flex flex-wrap items-end gap-3">
            <x-input label="From" type="date" name="from" value="{{ request('from') }}" />
            <x-input label="To" type="date" name="to" value="{{ request('to') }}" />
            <x-button type="submit">
                <x-icon name="search" class="size-4" />
                Run Report
            </x-button>
            <x-button href="{{ route('reports.export', array_merge(['type' => 'leads'], request()->only(['from', 'to']))) }}" variant="outline">
                <x-icon name="download" class="size-4" />
                Export
            </x-button>
        </form>
    </x-card>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <x-stat label="Total Leads" :value="$report['total_leads']" icon="funnel" />
        <x-stat label="Converted" :value="$report['converted']" icon="check-badge" positive />
        <x-stat label="Conversion Rate" :value="$report['conversion_rate'] . '%'" icon="trending-up" positive />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-card title="Leads by Source">
            @if ($report['by_source']->isEmpty())
                <p class="text-sm text-ink-400">No leads in this period.</p>
            @else
                <dl class="space-y-3 text-sm">
                    @foreach ($report['by_source'] as $row)
                        <div class="flex justify-between">
                            <dt class="text-ink-500 dark:text-ink-400">{{ ucfirst($row['source']) }}</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ $row['total'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </x-card>

        <div class="lg:col-span-2">
            <x-card :padding="false" title="Leads">
                @if ($report['leads']->isEmpty())
                    <div class="p-6 text-center text-sm text-ink-400">No leads in this period.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                    <th class="px-5 py-3 font-semibold">Lead</th>
                                    <th class="px-5 py-3 font-semibold">Source</th>
                                    <th class="px-5 py-3 font-semibold">Assigned To</th>
                                    <th class="px-5 py-3 font-semibold">Created</th>
                                    <th class="px-5 py-3 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                @foreach ($report['leads'] as $lead)
                                    <tr>
                                        <td class="px-5 py-3">
                                            <a href="{{ route('leads.show', $lead) }}" class="font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $lead->name }}</a>
                                            <p class="text-xs text-ink-400">{{ $lead->phone }}</p>
                                        </td>
                                        <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ $lead->source ?? '—' }}</td>
                                        <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ $lead->assignedTo?->name ?? '—' }}</td>
                                        <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($lead->created_at)->format('d M Y') }}</td>
                                        <td class="px-5 py-3">
                                            <x-badge :color="match($lead->status) { 'converted' => 'green', 'new' => 'blue', 'contacted' => 'purple', 'interested' => 'amber', 'trial' => 'gold', 'lost' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</x-badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4">
                        <x-pagination :model="$report['leads']" />
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-layouts.app>
