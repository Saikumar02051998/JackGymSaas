<x-layouts.app
    title="My Appointments"
    description="Book, view and manage your appointments."
    :breadcrumbs="[['label' => 'My Appointments']]">

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($upcoming->isNotEmpty())
                <x-card title="Upcoming Appointments">
                    <div class="space-y-3">
                        @foreach ($upcoming as $appointment)
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-ink-100 p-4 dark:border-ink-800">
                                <div class="flex items-center gap-4">
                                    <div class="flex size-12 shrink-0 flex-col items-center justify-center rounded-xl bg-gold-400/15 text-gold-600 dark:text-gold-400">
                                        <span class="text-lg font-extrabold leading-none">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d') }}</span>
                                        <span class="text-[10px] font-bold uppercase">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M') }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-bold text-ink-900 dark:text-white">{{ ucfirst($appointment->appointment_type) }}</p>
                                            <x-badge :color="match($appointment->appointment_type) { 'pt' => 'gold', 'consultation' => 'blue', 'trial' => 'purple', 'followup' => 'amber', default => 'gray' }">{{ ucfirst($appointment->appointment_type) }}</x-badge>
                                        </div>
                                        <p class="mt-0.5 text-xs text-ink-400">
                                            {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, d M Y') }}
                                            @if ($appointment->appointment_time)
                                                · {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}
                                            @endif
                                            @if ($appointment->duration_minutes)
                                                · {{ $appointment->duration_minutes }} min
                                            @endif
                                        </p>
                                        @if ($appointment->staff?->user)
                                            <p class="mt-0.5 text-xs text-ink-500 dark:text-ink-400">With {{ $appointment->staff->user->name }}</p>
                                        @endif
                                        @if ($appointment->notes)
                                            <p class="mt-0.5 text-xs text-ink-400">{{ $appointment->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('client.appointments.destroy', $appointment) }}"
                                      x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Cancel appointment?', message: 'This will cancel your {{ $appointment->appointment_type }} appointment.', confirmText: 'Cancel' } })">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="outline" size="sm">
                                        <x-icon name="x" class="size-4" />
                                        Cancel
                                    </x-button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif

            @if ($past->isNotEmpty())
                <x-card title="Past Appointments" :padding="false" class="mt-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                    <th class="px-5 py-3 font-semibold">Date</th>
                                    <th class="px-5 py-3 font-semibold">Type</th>
                                    <th class="px-5 py-3 font-semibold">Time</th>
                                    <th class="px-5 py-3 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                @foreach ($past as $appointment)
                                    <tr>
                                        <td class="px-5 py-3 font-medium text-ink-900 dark:text-white">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</td>
                                        <td class="px-5 py-3 capitalize">{{ $appointment->appointment_type }}</td>
                                        <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : '—' }}</td>
                                        <td class="px-5 py-3">
                                            <x-badge :color="match($appointment->status) { 'completed' => 'green', 'cancelled' => 'red', 'no_show' => 'amber', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $appointment->status)) }}</x-badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>
            @endif

            @if ($upcoming->isEmpty() && $past->isEmpty())
                <x-empty-state icon="calendar" title="No appointments" message="Book your first appointment with the form on the right." />
            @endif
        </div>

        <div>
            <x-card title="Book Appointment">
                <form action="{{ route('client.appointments.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <x-select name="appointment_type" label="Appointment Type" :required="true">
                        @foreach (['pt' => 'Personal Training', 'consultation' => 'Consultation', 'trial' => 'Trial Session', 'followup' => 'Follow-up', 'general' => 'General'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('appointment_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </x-select>

                    <x-input name="appointment_date" label="Date" type="date" :required="true" value="{{ old('appointment_date') }}" />
                    <x-input name="appointment_time" label="Preferred Time" type="time" value="{{ old('appointment_time') }}" />
                    <x-input name="duration_minutes" label="Duration (minutes)" type="number" min="5" max="480" value="{{ old('duration_minutes', 30) }}" />
                    <x-input name="notes" label="Notes" value="{{ old('notes') }}" />

                    <x-button type="submit" variant="primary" class="w-full justify-center">
                        <x-icon name="plus" class="size-4" />
                        Request Appointment
                    </x-button>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.app>
