<x-layouts.app
    title="{{ $user->name }}"
    description="Staff profile"
    :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => $user->name]]">

    <x-slot name="actions">
        @if (can_manage('staff.edit'))
            <x-button href="{{ route('staff.edit', $user) }}" variant="outline" size="sm">
                <x-icon name="pencil" class="size-4" />
                Edit
            </x-button>
        @endif
    </x-slot>

    @php $profile = $user->staffProfile; @endphp

    <x-card>
        <div class="flex flex-wrap items-center gap-4">
            <span class="avatar-lg">{{ $profile->initials ?? $user->name }}</span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-bold text-ink-900 dark:text-white">{{ $user->name }}</h2>
                    <x-badge :color="$user->status === 'active' ? 'green' : 'gray'">{{ ucfirst($user->status) }}</x-badge>
                </div>
                <p class="mt-0.5 text-sm text-ink-400">{{ $user->email }}{{ $user->phone ? ' · ' . $user->phone : '' }}</p>
                <p class="mt-0.5 text-sm text-ink-500 dark:text-ink-400">{{ $profile?->designation ?? 'Staff' }} · {{ $profile?->employee_id }}</p>
            </div>
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <p class="text-xs text-ink-400">Basic Salary</p>
                    <p class="text-lg font-bold text-ink-900 dark:text-white">{{ money($profile?->basic_salary ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-xs text-ink-400">Commission Rate</p>
                    <p class="text-lg font-bold text-ink-900 dark:text-white">{{ $profile?->commission_rate ?? 0 }}%</p>
                </div>
            </div>
        </div>
    </x-card>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat label="Assigned Clients" :value="$profile?->assignedClients->count() ?? 0" icon="users" />
        <x-stat label="Salary Records" :value="$salaries?->total() ?? 0" icon="banknotes" />
        <x-stat label="Leave Requests" :value="$leaves?->total() ?? 0" icon="calendar" />
        <x-stat label="Attendance Logs" :value="$attendance?->total() ?? 0" icon="clock" />
    </div>

    @if ($salaries && $salaries->isNotEmpty())
        <x-card title="Salary History" :padding="false" class="mt-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Period</th>
                            <th class="px-5 py-3 font-semibold">Basic</th>
                            <th class="px-5 py-3 font-semibold">Allowances</th>
                            <th class="px-5 py-3 font-semibold">Deductions</th>
                            <th class="px-5 py-3 font-semibold">Bonus</th>
                            <th class="px-5 py-3 font-semibold">Commission</th>
                            <th class="px-5 py-3 font-semibold">Net</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($salaries as $salary)
                            <tr>
                                <td class="px-5 py-3 font-semibold text-ink-900 dark:text-white">{{ $salary->period }}</td>
                                <td class="px-5 py-3">{{ money($salary->basic) }}</td>
                                <td class="px-5 py-3">{{ money($salary->allowances) }}</td>
                                <td class="px-5 py-3 text-red-500">{{ money($salary->deductions) }}</td>
                                <td class="px-5 py-3">{{ money($salary->bonus) }}</td>
                                <td class="px-5 py-3">{{ money($salary->commission) }}</td>
                                <td class="px-5 py-3 font-bold text-ink-900 dark:text-white">{{ money($salary->net_salary) }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :color="match($salary->payment_status) { 'paid' => 'green', 'partially_paid' => 'amber', 'pending' => 'blue', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $salary->payment_status)) }}</x-badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$salaries" />
            </div>
        </x-card>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        @if ($attendance && $attendance->isNotEmpty())
            <x-card title="Recent Attendance" :padding="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                <th class="px-5 py-3 font-semibold">Date</th>
                                <th class="px-5 py-3 font-semibold">In</th>
                                <th class="px-5 py-3 font-semibold">Out</th>
                                <th class="px-5 py-3 font-semibold">Hours</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($attendance as $record)
                                <tr>
                                    <td class="px-5 py-3 text-ink-900 dark:text-white">{{ \Carbon\Carbon::parse($record->attendance_date)->format('d M') }}</td>
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
                    <x-pagination :model="$attendance" />
                </div>
            </x-card>
        @endif

        @if ($leaves && $leaves->isNotEmpty())
            <x-card title="Leave Requests" :padding="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                <th class="px-5 py-3 font-semibold">Type</th>
                                <th class="px-5 py-3 font-semibold">Period</th>
                                <th class="px-5 py-3 font-semibold">Days</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($leaves as $leave)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-ink-900 dark:text-white">{{ $leave->leave_type }}</td>
                                    <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} &rarr; {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                    <td class="px-5 py-3">{{ $leave->days }}</td>
                                    <td class="px-5 py-3">
                                        <x-badge :color="match($leave->status) { 'approved' => 'green', 'pending' => 'amber', 'rejected' => 'red', 'cancelled' => 'gray', default => 'gray' }">{{ ucfirst($leave->status) }}</x-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4">
                    <x-pagination :model="$leaves" />
                </div>
            </x-card>
        @endif
    </div>
</x-layouts.app>
