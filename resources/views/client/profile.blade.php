<x-layouts.app
    title="My Profile"
    description="Update your personal details and contact information."
    :breadcrumbs="[['label' => 'My Profile']]">

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <x-card>
                <div class="flex flex-col items-center text-center">
                    <x-avatar :user="$client->user" size="size-20 text-2xl" class="ring-4 ring-gold-400/20" />
                    <h2 class="mt-4 text-lg font-bold text-ink-900 dark:text-white">{{ $client->display_name }}</h2>
                    <p class="text-sm text-ink-400">Member ID: {{ $client->member_id }}</p>
                    <div class="mt-3">
                        <x-badge :color="$client->status === 'active' ? 'green' : 'gray'">{{ ucfirst($client->status) }}</x-badge>
                    </div>
                </div>

                <div class="mt-6 space-y-3 border-t border-ink-100 pt-5 text-sm dark:border-ink-800">
                    <div class="flex items-center gap-3">
                        <x-icon name="mail" class="size-4 shrink-0 text-ink-400" />
                        <span class="truncate text-ink-600 dark:text-ink-300">{{ $client->user->email }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-icon name="phone" class="size-4 shrink-0 text-ink-400" />
                        <span class="text-ink-600 dark:text-ink-300">{{ $client->phone ?? 'Not provided' }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-icon name="briefcase" class="size-4 shrink-0 text-ink-400" />
                        <span class="text-ink-600 dark:text-ink-300">{{ $client->trainer?->display_name ?? 'No trainer assigned' }}</span>
                    </div>
                </div>
            </x-card>

            @if ($client->healthProfile)
                <x-card title="Health Snapshot">
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-ink-400">Height</dt>
                            <dd class="font-medium text-ink-900 dark:text-white">{{ $client->healthProfile->height ?? '—' }} cm</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-ink-400">Weight</dt>
                            <dd class="font-medium text-ink-900 dark:text-white">{{ $client->healthProfile->weight ?? '—' }} kg</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-ink-400">BMI</dt>
                            <dd class="font-medium text-ink-900 dark:text-white">{{ $client->healthProfile->bmi ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-ink-400">Body Fat</dt>
                            <dd class="font-medium text-ink-900 dark:text-white">{{ $client->healthProfile->body_fat ?? '—' }}%</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-ink-400">Goal</dt>
                            <dd class="font-medium text-ink-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $client->healthProfile->fitness_goal ?? '—')) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-ink-400">Activity Level</dt>
                            <dd class="font-medium text-ink-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $client->healthProfile->activity_level ?? '—')) }}</dd>
                        </div>
                    </dl>
                </x-card>
            @endif
        </div>

        <div class="lg:col-span-2">
            <x-card title="Personal Information">
                <form action="{{ route('client.profile.update') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-input name="name" label="Full Name" :required="true" value="{{ old('name', $client->user->name) }}" />
                        <x-input name="email" label="Email" type="email" :required="true" value="{{ old('email', $client->user->email) }}" icon="mail" />
                        <x-input name="phone" label="Phone" value="{{ old('phone', $client->phone) }}" icon="phone" />
                        <x-select name="gender" label="Gender">
                            @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('gender', $client->gender) === $value)>{{ $label }}</option>
                            @endforeach
                        </x-select>
                        <x-input name="dob" label="Date of Birth" type="date" value="{{ old('dob', $client->dob ? \Carbon\Carbon::parse($client->dob)->format('Y-m-d') : '') }}" />
                        <x-input name="address" label="Address" value="{{ old('address', $client->address) }}" />
                        <x-input name="emergency_contact" label="Emergency Contact Name" value="{{ old('emergency_contact', $client->emergency_contact) }}" />
                        <x-input name="emergency_phone" label="Emergency Contact Phone" value="{{ old('emergency_phone', $client->emergency_phone) }}" icon="phone" />
                    </div>

                    <div class="flex justify-end">
                        <x-button type="submit" variant="primary">
                            <x-icon name="save" class="size-4" />
                            Save Changes
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.app>
