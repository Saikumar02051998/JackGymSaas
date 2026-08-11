<x-layouts.app
    title="Staff Attendance"
    description="Track staff check-ins and working hours."
    :breadcrumbs="[['label' => 'Attendance', 'url' => route('attendance.index')], ['label' => 'Staff']]">

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat label="Present Today" :value="$present" icon="check-badge" positive />
        <x-stat label="Total Staff" :value="$totalStaff" icon="users" />
        <x-stat label="My Status" :value="$myRecord?->check_out ? 'Checked out' : ($myRecord?->check_in ? 'Checked in' : 'Not in')" icon="clock" />
    </div>

    @if (auth()->user()->staffProfile)
        <x-card title="My Attendance" class="mt-6">
            <div class="flex flex-wrap items-center gap-3">
                @if ($myRecord?->check_in && ! $myRecord->check_out)
                    <form method="POST" action="{{ route('attendance.staff-check-out') }}">
                        @csrf
                        <x-button type="submit">
                            <x-icon name="clock" class="size-4" />
                            Check Out
                        </x-button>
                    </form>
                    <span class="text-sm text-ink-400">Checked in at <strong class="text-ink-900 dark:text-white">{{ $myRecord->check_in }}</strong></span>
                @else
                    <form method="POST" action="{{ route('attendance.staff-check-in') }}">
                        @csrf
                        <x-button type="submit">
                            <x-icon name="check-badge" class="size-4" />
                            Check In
                        </x-button>
                    </form>
                    @if ($myRecord?->check_out)
                        <span class="text-sm text-ink-400">Checked out at <strong class="text-ink-900 dark:text-white">{{ $myRecord->check_out }}</strong></span>
                    @endif
                @endif
                @error('staff')
                    <p class="text-xs font-medium text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </x-card>
    @endif

    <x-card :padding="false" class="mt-6">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4 dark:border-ink-800">
            <form method="GET" action="{{ route('attendance.staff') }}" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date }}" class="input w-auto">
                <x-button type="submit">Filter</x-button>
            </form>
            <p class="ml-auto text-sm text-ink-400">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</p>
        </div>

        @if ($records->isEmpty())
            <div class="p-8">
                <x-empty-state icon="users" title="No staff attendance recorded" message="Staff check-ins will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Staff</th>
                            <th class="px-5 py-3 font-semibold">Role</th>
                            <th class="px-5 py-3 font-semibold">Check In</th>
                            <th class="px-5 py-3 font-semibold">Check Out</th>
                            <th class="px-5 py-3 font-semibold">Working</th>
                            <th class="px-5 py-3 font-semibold">Late</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($records as $record)
                            @php $staff = $record->staff; @endphp
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4">
                                    <span class="font-semibold text-ink-900 dark:text-white">{{ $staff->display_name }}</span>
                                    <p class="text-xs text-ink-400">{{ $staff->employee_id }}</p>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $staff->designation ?? '—' }}</td>
                                <td class="px-5 py-4 font-medium text-ink-900 dark:text-white">{{ $record->check_in }}</td>
                                <td class="px-5 py-4 font-medium text-ink-900 dark:text-white">{{ $record->check_out ?? '—' }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">
                                    @if ($record->working_minutes)
                                        {{ intdiv($record->working_minutes, 60) }}h {{ $record->working_minutes % 60 }}m
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($record->is_late)
                                        <x-badge color="amber">Late</x-badge>
                                    @else
                                        <span class="text-ink-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($record->status) { 'present' => 'green', 'leave' => 'blue', 'half_day' => 'amber', 'holiday' => 'purple', 'absent' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</x-badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$records" />
            </div>
        @endif
    </x-card>
</x-layouts.app>
