<x-layouts.app
    title="New Appointment"
    description="Schedule an appointment for a client."
    :breadcrumbs="[['label' => 'Appointments', 'url' => route('appointments.index')], ['label' => 'New']]">

    <div class="mx-auto max-w-2xl">
        <x-card title="Schedule Appointment">
            <form method="POST" action="{{ route('appointments.store') }}" class="space-y-4">
                @csrf

                <x-select label="Client" name="client_id" required>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->display_name }} ({{ $client->member_id }})</option>
                    @endforeach
                </x-select>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-select label="Type" name="appointment_type" required>
                        @foreach (['pt' => 'Personal Training', 'consultation' => 'Consultation', 'trial' => 'Trial', 'followup' => 'Follow-up', 'general' => 'General'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('appointment_type', 'general') === $value)>{{ $label }}</option>
                        @endforeach
                    </x-select>
                    <x-select label="Staff" name="staff_id">
                        @foreach ($staff as $member)
                            <option value="{{ $member->id }}" @selected(old('staff_id') == $member->id)>{{ $member->user?->name }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <x-input label="Date" name="appointment_date" type="date" value="{{ old('appointment_date', now()->toDateString()) }}" required />
                    <x-input label="Time" name="appointment_time" type="time" value="{{ old('appointment_time') }}" />
                    <x-input label="Duration (min)" name="duration_minutes" type="number" min="5" max="480" value="{{ old('duration_minutes', 60) }}" />
                </div>

                <x-field label="Notes" name="notes">
                    <textarea name="notes" rows="3" class="input">{{ old('notes') }}</textarea>
                </x-field>

                <div class="flex gap-3 pt-1">
                    <x-button type="submit" class="flex-1">
                        <x-icon name="calendar-check" class="size-4" />
                        Schedule Appointment
                    </x-button>
                    <a href="{{ route('appointments.index') }}" class="btn-outline">Cancel</a>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
