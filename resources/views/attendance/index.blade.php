<x-layouts.app
    title="Member Attendance"
    description="Track member check-ins and check-outs."
    :breadcrumbs="[['label' => 'Attendance']]">

    <x-slot name="actions">
        @if (can_manage('attendance.manage'))
            <form method="POST" action="{{ route('attendance.checkout-all') }}" class="inline">
                @csrf
                <x-button type="submit" variant="outline" size="sm">
                    <x-icon name="clock" class="size-4" />
                    Check Out All
                </x-button>
            </form>
        @endif
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat label="Total Today" :value="$todayCount" icon="calendar-check" />
        <x-stat label="Checked In" :value="$checkedIn" icon="check-badge" positive />
        <x-stat label="Checked Out" :value="$checkedOut" icon="clock" />
    </div>

    @if (can_manage('attendance.manage'))
        <x-card title="Quick Check-In" class="mt-6">
            <form method="POST" action="{{ route('attendance.check-in') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                @csrf
                <div class="flex-1">
                    <x-select label="Select member" name="client_id" required placeholder="Search by name or member ID">
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->display_name }} ({{ $c->member_id }})</option>
                        @endforeach
                    </x-select>
                    <input type="hidden" name="source" value="reception">
                    @error('client')
                        <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <x-button type="submit" class="shrink-0">
                    <x-icon name="check-badge" class="size-4" />
                    Check In
                </x-button>
            </form>
        </x-card>
    @endif

    <x-card :padding="false" class="mt-6">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4 dark:border-ink-800">
            <div>
                <form method="GET" action="{{ route('attendance.index') }}" class="flex items-center gap-2">
                    <input type="date" name="date" value="{{ $date }}" class="input w-auto">
                    <x-button type="submit">Filter</x-button>
                </form>
            </div>
            <p class="ml-auto text-sm text-ink-400">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</p>
        </div>

        @if ($records->isEmpty())
            <div class="p-8">
                <x-empty-state icon="clock" title="No attendance on this day" message="Records will appear here once members check in." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Member</th>
                            <th class="px-5 py-3 font-semibold">Plan</th>
                            <th class="px-5 py-3 font-semibold">Check In</th>
                            <th class="px-5 py-3 font-semibold">Check Out</th>
                            <th class="px-5 py-3 font-semibold">Duration</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($records as $record)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4">
                                    @if ($record->client)
                                        <a href="{{ route('clients.show', $record->client_id) }}" class="font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $record->client->display_name }}</a>
                                        <p class="text-xs text-ink-400">{{ $record->client->member_id }}</p>
                                    @else
                                        <span class="text-ink-400">Deleted client</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $record->client->activeMembership?->plan?->name ?? '—' }}</td>
                                <td class="px-5 py-4 font-medium text-ink-900 dark:text-white">{{ $record->check_in }}</td>
                                <td class="px-5 py-4 font-medium text-ink-900 dark:text-white">{{ $record->check_out ?? '—' }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">
                                    @if ($record->duration_minutes)
                                        {{ intdiv($record->duration_minutes, 60) }}h {{ $record->duration_minutes % 60 }}m
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($record->check_in && $record->check_out)
                                        <x-badge color="green">Completed</x-badge>
                                    @elseif ($record->check_in)
                                        <x-badge color="blue">In Gym</x-badge>
                                    @else
                                        <x-badge color="gray">—</x-badge>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if (! $record->check_out && can_manage('attendance.manage'))
                                        <form method="POST" action="{{ route('attendance.check-out') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="client_id" value="{{ $record->client_id }}">
                                            <x-button type="submit" variant="ghost" size="sm">Check Out</x-button>
                                        </form>
                                    @endif
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
