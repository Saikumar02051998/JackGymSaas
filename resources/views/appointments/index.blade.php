<x-layouts.app
    title="Appointments"
    description="Client appointments and schedules."
    :breadcrumbs="[['label' => 'Appointments']]">

    <x-slot name="actions">
        @if (can_manage('appointments.manage'))
            <x-button href="{{ route('appointments.create') }}" size="sm">
                <x-icon name="plus" class="size-4" />
                New Appointment
            </x-button>
        @endif
    </x-slot>

    <x-card title="Appointments">
        <form method="GET" action="{{ route('appointments.index') }}" class="mb-4 grid gap-3 border-b border-ink-100 pb-4 dark:border-ink-800 sm:grid-cols-2 lg:grid-cols-4">
            <x-input label="Date" name="date" type="date" value="{{ request('date', $date) }}" />
            <x-select label="Status" name="status">
                <option value="all">All statuses</option>
                @foreach (['scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'no_show' => 'No show'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </x-select>
            <div class="flex items-end gap-2">
                <x-button type="submit">
                    <x-icon name="funnel" class="size-4" />
                    Filter
                </x-button>
                @if (request()->hasAny(['date', 'status']))
                    <x-button href="{{ route('appointments.index') }}" variant="outline" size="sm">Clear</x-button>
                @endif
            </div>
        </form>

        @if ($appointments->isEmpty())
            <x-empty-state icon="calendar-check" title="No appointments" message="Schedule appointments for your clients." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-5 py-3 font-semibold">Time</th>
                            <th class="px-5 py-3 font-semibold">Client</th>
                            <th class="px-5 py-3 font-semibold">Type</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            @if (can_manage('appointments.manage'))
                                <th class="px-5 py-3 text-right font-semibold">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($appointments as $appointment)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="whitespace-nowrap px-5 py-4 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') : '—' }}</td>
                                <td class="px-5 py-4">
                                    @if ($appointment->client)
                                        <div class="flex items-center gap-2.5">
                                            <x-avatar :user="$appointment->client->user" size="size-7" />
                                            <div>
                                                <p class="font-medium text-ink-900 dark:text-white">{{ $appointment->client->display_name }}</p>
                                                <p class="text-xs text-ink-400">{{ $appointment->client->member_id }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-ink-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($appointment->appointment_type) { 'pt' => 'gold', 'consultation' => 'blue', 'trial' => 'purple', 'followup' => 'green', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</x-badge>
                                </td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($appointment->status) { 'scheduled' => 'blue', 'completed' => 'green', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</x-badge>
                                </td>
                                @if (can_manage('appointments.manage'))
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            @if ($appointment->status === 'scheduled')
                                                <form method="POST" action="{{ route('appointments.complete', $appointment) }}">
                                                    @csrf
                                                    <x-button type="submit" variant="success" size="sm">
                                                        <x-icon name="check" class="size-3.5" />
                                                        Complete
                                                    </x-button>
                                                </form>
                                                <form method="POST" action="{{ route('appointments.cancel', $appointment) }}">
                                                    @csrf
                                                    <x-button type="submit" variant="ghost" size="sm" class="!text-red-500">
                                                        <x-icon name="x" class="size-3.5" />
                                                        Cancel
                                                    </x-button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$appointments" />
            </div>
        @endif
    </x-card>
</x-layouts.app>
