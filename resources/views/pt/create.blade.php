<x-layouts.app
    title="New PT Session"
    description="Schedule a personal training session."
    :breadcrumbs="[['label' => 'PT Sessions', 'url' => route('pt.index')], ['label' => 'New']]">

    <div class="mx-auto max-w-2xl">
        <x-card title="Schedule PT Session">
            <form method="POST" action="{{ route('pt.store') }}" class="space-y-4">
                @csrf

                <x-select label="Client" name="client_id" required>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->display_name }} ({{ $client->member_id }})</option>
                    @endforeach
                </x-select>

                <x-select label="Trainer" name="trainer_id" required>
                    @foreach ($trainers as $trainer)
                        <option value="{{ $trainer->id }}" @selected(old('trainer_id') == $trainer->id)>{{ $trainer->user?->name }}</option>
                    @endforeach
                </x-select>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="Date" name="session_date" type="date" value="{{ old('session_date', now()->toDateString()) }}" required />
                    <x-input label="Time" name="session_time" type="time" value="{{ old('session_time') }}" />
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <x-input label="Duration (min)" name="duration_minutes" type="number" min="5" max="480" value="{{ old('duration_minutes', 60) }}" />
                    <x-input label="Session no." name="session_no" type="number" min="1" value="{{ old('session_no') }}" help="Session number within package" />
                    <x-input label="Package sessions" name="package_sessions" type="number" min="1" value="{{ old('package_sessions') }}" help="Total sessions in package" />
                </div>

                <x-field label="Notes" name="notes">
                    <textarea name="notes" rows="3" class="input">{{ old('notes') }}</textarea>
                </x-field>

                <div class="flex gap-3 pt-1">
                    <x-button type="submit" class="flex-1">
                        <x-icon name="dumbbell" class="size-4" />
                        Schedule Session
                    </x-button>
                    <a href="{{ route('pt.index') }}" class="btn-outline">Cancel</a>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
