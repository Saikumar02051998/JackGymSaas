<x-layouts.app
    title="Schedule Follow-Up"
    description="Schedule a follow-up with a member."
    :breadcrumbs="[['label' => 'Follow-Ups', 'url' => route('followups.index')], ['label' => 'Schedule Follow-Up']]">

    <form method="POST" action="{{ route('followups.store') }}">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Follow-Up Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-select label="Client" name="client_id" required placeholder="Select a client">
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->display_name }} ({{ $client->member_id }})</option>
                                @endforeach
                            </x-select>
                        </div>
                        <x-select label="Type" name="type" required>
                            @foreach (['phone' => 'Phone', 'email' => 'Email', 'whatsapp' => 'WhatsApp', 'visit' => 'Visit', 'text' => 'Text Message'] as $value => $label)
                                <option value="{{ $value }}" {{ old('type', 'phone') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </x-select>
                        <x-select label="Assign to" name="staff_id" placeholder="Default to you">
                            @foreach ($staff as $s)
                                <option value="{{ $s->id }}" {{ old('staff_id') == $s->id ? 'selected' : '' }}>{{ $s->display_name }}</option>
                            @endforeach
                        </x-select>
                        <x-input label="Follow-up date" type="date" name="follow_up_date" value="{{ old('follow_up_date', now()->toDateString()) }}" required />
                        <x-input label="Follow-up time" type="time" name="follow_up_time" value="{{ old('follow_up_time') }}" />
                        <div class="sm:col-span-2">
                            <x-field label="Notes" name="notes">
                                <textarea name="notes" rows="3" class="input" placeholder="What to cover in this follow-up?">{{ old('notes') }}</textarea>
                            </x-field>
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Summary">
                    <p class="text-sm leading-relaxed text-ink-500 dark:text-ink-400">
                        Follow-ups appear on the dashboard for the assigned staff member. They will be marked overdue automatically if not completed by the scheduled date.
                    </p>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="calendar-check" class="size-4" />
                        Schedule
                    </x-button>
                    <a href="{{ route('followups.index') }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
