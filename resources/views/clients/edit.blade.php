<x-layouts.app
    title="Edit Client"
    description="Update {{ $client->display_name }}'s details."
    :breadcrumbs="[['label' => 'Clients', 'url' => route('clients.index')], ['label' => $client->display_name, 'url' => route('clients.show', $client)], ['label' => 'Edit']]">

    <form method="POST" action="{{ route('clients.update', $client) }}">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Personal Information">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Full name" name="name" value="{{ old('name', $client->user?->name) }}" required />
                        <x-input label="Email" type="email" name="email" value="{{ old('email', $client->user?->email) }}" />
                        <x-input label="Phone" name="phone" value="{{ old('phone', $client->phone) }}" />
                        <x-input label="Emergency Contact" name="emergency_contact" value="{{ old('emergency_contact', $client->emergency_contact) }}" />
                        <x-input label="Emergency Phone" name="emergency_phone" value="{{ old('emergency_phone', $client->emergency_phone) }}" />
                        <x-select label="Gender" name="gender" placeholder="Select gender">
                            <option value="male" {{ old('gender', $client->gender) === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $client->gender) === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $client->gender) === 'other' ? 'selected' : '' }}>Other</option>
                        </x-select>
                        <x-input label="Date of birth" type="date" name="dob" value="{{ old('dob', $client->dob) }}" />
                        <x-select label="Assigned Coach" name="assigned_trainer_id" placeholder="No coach">
                            @foreach ($coaches as $coach)
                                <option value="{{ $coach->id }}" {{ old('assigned_trainer_id', $client->assigned_trainer_id) == $coach->id ? 'selected' : '' }}>{{ $coach->display_name }}</option>
                            @endforeach
                        </x-select>
                        <x-input label="Lead Source" name="lead_source" value="{{ old('lead_source', $client->lead_source) }}" />
                        <div class="sm:col-span-2">
                            <x-input label="Address" name="address" value="{{ old('address', $client->address) }}" />
                        </div>
                    </div>
                </x-card>

                <x-card title="Health Profile">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-input label="Height (cm)" type="number" step="0.1" name="height" value="{{ old('height', $client->healthProfile?->height) }}" />
                        <x-input label="Weight (kg)" type="number" step="0.1" name="weight" value="{{ old('weight', $client->healthProfile?->weight) }}" />
                        <x-input label="Body Fat (%)" type="number" step="0.1" name="body_fat" value="{{ old('body_fat', $client->healthProfile?->body_fat) }}" />
                        <x-input label="Goal Weight (kg)" type="number" step="0.1" name="goal_weight" value="{{ old('goal_weight', $client->healthProfile?->goal_weight) }}" />
                        <x-select label="Fitness Goal" name="fitness_goal" placeholder="Select a goal">
                            @foreach (['weight_loss' => 'Weight Loss', 'muscle_gain' => 'Muscle Gain', 'strength' => 'Strength', 'endurance' => 'Endurance', 'general_fitness' => 'General Fitness'] as $value => $label)
                                <option value="{{ $value }}" {{ old('fitness_goal', $client->healthProfile?->fitness_goal) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </x-select>
                        <x-select label="Activity Level" name="activity_level" placeholder="Select level">
                            @foreach (['sedentary' => 'Sedentary', 'light' => 'Light', 'moderate' => 'Moderate', 'active' => 'Active', 'very_active' => 'Very Active'] as $value => $label)
                                <option value="{{ $value }}" {{ old('activity_level', $client->healthProfile?->activity_level) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </x-select>
                        <div class="sm:col-span-3">
                            <x-field label="Medical Notes" name="medical_notes">
                                <textarea name="medical_notes" rows="2" class="input">{{ old('medical_notes', $client->healthProfile?->medical_notes) }}</textarea>
                            </x-field>
                        </div>
                        <div class="sm:col-span-3">
                            <x-field label="Injuries" name="injuries">
                                <textarea name="injuries" rows="2" class="input">{{ old('injuries', $client->healthProfile?->injuries) }}</textarea>
                            </x-field>
                        </div>
                        <div class="sm:col-span-3">
                            <x-field label="Limitations" name="limitations">
                                <textarea name="limitations" rows="2" class="input">{{ old('limitations', $client->healthProfile?->limitations) }}</textarea>
                            </x-field>
                        </div>
                        <div class="sm:col-span-3">
                            <x-field label="Allergies" name="allergies">
                                <textarea name="allergies" rows="2" class="input">{{ old('allergies', $client->healthProfile?->allergies) }}</textarea>
                            </x-field>
                        </div>
                        <div class="sm:col-span-3">
                            <x-field label="Important Notes" name="important_notes">
                                <textarea name="important_notes" rows="2" class="input">{{ old('important_notes', $client->healthProfile?->important_notes) }}</textarea>
                            </x-field>
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Internal Notes">
                    <x-field label="Notes" name="notes">
                        <textarea name="notes" rows="4" class="input">{{ old('notes', $client->notes) }}</textarea>
                    </x-field>
                </x-card>

                <x-card title="Reference">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Member ID</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ $client->member_id }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Referral code</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ $client->referral_code }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Joining date</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ $client->joining_date ? \Carbon\Carbon::parse($client->joining_date)->format('d M Y') : '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Status</dt>
                            <dd><x-badge :color="$client->status === 'active' ? 'green' : 'gray'">{{ ucfirst($client->status) }}</x-badge></dd>
                        </div>
                    </dl>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="save" class="size-4" />
                        Save Changes
                    </x-button>
                    <a href="{{ route('clients.show', $client) }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
