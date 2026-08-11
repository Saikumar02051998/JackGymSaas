<x-layouts.app
    title="Attendance Report"
    description="Member attendance analytics."
    :breadcrumbs="[['label' => 'Reports'], ['label' => 'Attendance']]">

    <x-card>
        <form method="GET" action="{{ route('reports.attendance') }}" class="flex flex-wrap items-end gap-3">
            <x-input label="From" type="date" name="from" value="{{ request('from') }}" />
            <x-input label="To" type="date" name="to" value="{{ request('to') }}" />
            <x-button type="submit">
                <x-icon name="search" class="size-4" />
                Run Report
            </x-button>
            <x-button href="{{ route('reports.export', array_merge(['type' => 'attendance'], request()->only(['from', 'to']))) }}" variant="outline">
                <x-icon name="download" class="size-4" />
                Export
            </x-button>
        </form>
    </x-card>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <x-stat label="Total Visits" :value="$report['total_visits']" icon="calendar-check" positive />
        <x-stat label="Unique Members" :value="$report['unique_clients']" icon="users" />
        <x-stat label="Avg Duration" :value="$report['avg_duration'] . ' min'" icon="clock" />
    </div>

    @if ($report['daily']->isNotEmpty())
        <x-card title="Visits per Day" class="mt-6">
            <div class="h-64">
                <canvas x-data="chart($el)" x-init="init()" data-chart='{!! json_encode([
                    'type' => 'bar',
                    'data' => [
                        'labels' => $report['daily']->map(fn ($d) => \Carbon\Carbon::parse($d->attendance_date)->format('d M')),
                        'datasets' => [[
                            'label' => 'Visits',
                            'data' => $report['daily']->pluck('total'),
                            'backgroundColor' => 'rgba(167, 139, 60, 0.6)',
                            'borderRadius' => 6,
                        ]],
                    ],
                    'options' => ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['display' => false]]],
                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}'></canvas>
            </div>
        </x-card>
    @endif

    <x-card :padding="false" title="Attendance Records" class="mt-6">
        @if ($report['records']->isEmpty())
            <div class="p-8">
                <x-empty-state icon="clock" title="No attendance records" message="Records for this period will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-5 py-3 font-semibold">Member</th>
                            <th class="px-5 py-3 font-semibold">Check In</th>
                            <th class="px-5 py-3 font-semibold">Check Out</th>
                            <th class="px-5 py-3 font-semibold">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($report['records'] as $record)
                            <tr>
                                <td class="px-5 py-3 text-ink-900 dark:text-white">{{ \Carbon\Carbon::parse($record->attendance_date)->format('d M Y') }}</td>
                                <td class="px-5 py-3 font-semibold text-ink-900 dark:text-white">{{ $record->client->display_name }}</td>
                                <td class="px-5 py-3">{{ $record->check_in ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $record->check_out ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $record->duration_minutes ? intdiv($record->duration_minutes, 60) . 'h ' . ($record->duration_minutes % 60) . 'm' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$report['records']" />
            </div>
        @endif
    </x-card>
</x-layouts.app>
