<x-layouts.app
    title="Staff Report"
    description="Staff attendance overview."
    :breadcrumbs="[['label' => 'Reports'], ['label' => 'Staff']]">

    <x-card>
        <form method="GET" action="{{ route('reports.staff') }}" class="flex flex-wrap items-end gap-3">
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

    <x-card :padding="false" title="Staff Attendance" class="mt-6">
        @if ($report['records']->isEmpty())
            <div class="p-8">
                <x-empty-state icon="users" title="No staff attendance" message="Staff attendance for this period will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-5 py-3 font-semibold">Staff</th>
                            <th class="px-5 py-3 font-semibold">Check In</th>
                            <th class="px-5 py-3 font-semibold">Check Out</th>
                            <th class="px-5 py-3 font-semibold">Working</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($report['records'] as $record)
                            <tr>
                                <td class="px-5 py-3 text-ink-900 dark:text-white">{{ \Carbon\Carbon::parse($record->attendance_date)->format('d M Y') }}</td>
                                <td class="px-5 py-3 font-semibold text-ink-900 dark:text-white">{{ $record->staff->display_name }}</td>
                                <td class="px-5 py-3">{{ $record->check_in ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $record->check_out ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $record->working_minutes ? intdiv($record->working_minutes, 60) . 'h ' . ($record->working_minutes % 60) . 'm' : '—' }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :color="match($record->status) { 'present' => 'green', 'leave' => 'blue', 'half_day' => 'amber', 'holiday' => 'purple', 'absent' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</x-badge>
                                </td>
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
