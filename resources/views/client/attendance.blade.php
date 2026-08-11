<x-layouts.app
    title="My Attendance"
    description="Track your check-ins and gym visits."
    :breadcrumbs="[['label' => 'My Attendance']]">

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
        <x-stat label="Total Visits" :value="$stats['total']" icon="calendar-check" />
        <x-stat label="This Month" :value="$stats['this_month']" icon="trending-up" />
        <x-stat label="Avg. Duration" :value="$stats['avg_duration'] . ' min'" icon="clock" />
    </div>

    <x-card title="Attendance Records" class="mt-6">
        <form method="GET" action="{{ route('client.attendance') }}" class="mb-4 grid gap-4 sm:grid-cols-3 sm:items-end">
            <x-input name="from" label="From Date" type="date" value="{{ request('from') }}" />
            <x-input name="to" label="To Date" type="date" value="{{ request('to') }}" />
            <div class="flex gap-2">
                <x-button type="submit">
                    <x-icon name="search" class="size-4" />
                    Filter
                </x-button>
                @if (request()->has('from') || request()->has('to'))
                    <x-button href="{{ route('client.attendance') }}" variant="outline" size="sm">Clear</x-button>
                @endif
            </div>
        </form>

        @if ($records->isEmpty())
            <x-empty-state icon="calendar-check" title="No attendance records" message="Your check-ins will appear here once you start visiting the gym." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-5 py-3 font-semibold">Check-in</th>
                            <th class="px-5 py-3 font-semibold">Check-out</th>
                            <th class="px-5 py-3 font-semibold">Duration</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Source</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($records as $record)
                            <tr>
                                <td class="px-5 py-3 font-semibold text-ink-900 dark:text-white">{{ \Carbon\Carbon::parse($record->attendance_date)->format('d M Y') }}</td>
                                <td class="px-5 py-3">{{ $record->check_in ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $record->check_out ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $record->duration_minutes ? $record->duration_minutes . ' min' : '—' }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :color="match($record->status) { 'present' => 'green', 'late' => 'amber', 'left_early' => 'blue', 'absent' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</x-badge>
                                </td>
                                <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ ucfirst(str_replace('_', ' ', $record->source)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-pagination :model="$records" />
        @endif
    </x-card>
</x-layouts.app>
