<x-layouts.app
    title="My Profile"
    description="Manage your account details, avatar and password."
    :breadcrumbs="[['label' => 'My Profile']]">

    @php
        $clientProfile = $user->clientProfile;
        $staffProfile = $user->staffProfile;
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <x-card>
                <div class="flex flex-col items-center text-center">
                    <x-avatar :user="$user" size="size-20 text-2xl" class="ring-4 ring-gold-400/20" />
                    <h2 class="mt-4 text-lg font-bold text-ink-900 dark:text-white">{{ $user->name }}</h2>
                    <p class="text-sm text-ink-400">{{ \App\Support\Menu::roleLabel() }}</p>
                    <div class="mt-3">
                        <x-badge :color="$user->status === 'active' ? 'green' : 'gray'">{{ ucfirst($user->status) }}</x-badge>
                    </div>
                </div>

                <div class="mt-6 space-y-3 border-t border-ink-100 pt-5 text-sm dark:border-ink-800">
                    <div class="flex items-center gap-3">
                        <x-icon name="mail" class="size-4 shrink-0 text-ink-400" />
                        <span class="truncate text-ink-600 dark:text-ink-300">{{ $user->email }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-icon name="phone" class="size-4 shrink-0 text-ink-400" />
                        <span class="text-ink-600 dark:text-ink-300">{{ $user->phone ?? 'Not provided' }}</span>
                    </div>
                    @if ($clientProfile)
                        <div class="flex items-center gap-3">
                            <x-icon name="card" class="size-4 shrink-0 text-ink-400" />
                            <span class="text-ink-600 dark:text-ink-300">Member ID: {{ $clientProfile->member_id }}</span>
                        </div>
                    @elseif ($staffProfile)
                        <div class="flex items-center gap-3">
                            <x-icon name="briefcase" class="size-4 shrink-0 text-ink-400" />
                            <span class="text-ink-600 dark:text-ink-300">{{ $staffProfile->designation ?? 'Staff' }} · {{ $staffProfile->employee_id }}</span>
                        </div>
                    @endif
                </div>
            </x-card>

            @if ($clientProfile)
                <x-card title="Membership Details">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-400">Joining Date</dt>
                            <dd class="font-medium text-ink-900 dark:text-white">{{ $clientProfile->joining_date ? \Carbon\Carbon::parse($clientProfile->joining_date)->format('d M Y') : '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-400">Gender</dt>
                            <dd class="font-medium text-ink-900 dark:text-white">{{ ucfirst($clientProfile->gender ?? '—') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-400">Date of Birth</dt>
                            <dd class="font-medium text-ink-900 dark:text-white">{{ $clientProfile->dob ? \Carbon\Carbon::parse($clientProfile->dob)->format('d M Y') : '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-400">Trainer</dt>
                            <dd class="font-medium text-ink-900 dark:text-white">{{ $clientProfile->trainer?->display_name ?? 'Not assigned' }}</dd>
                        </div>
                    </dl>
                </x-card>
            @endif
        </div>

        <div class="lg:col-span-2">
            <x-card title="Edit Profile">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-input name="name" label="Full Name" :required="true" value="{{ old('name', $user->name) }}" />
                        <x-input name="phone" label="Phone" value="{{ old('phone', $user->phone) }}" icon="phone" />
                        <x-input name="email" label="Email" type="email" :required="true" value="{{ old('email', $user->email) }}" icon="mail" />
                        <x-input name="avatar" label="Profile Photo" type="file" accept="image/*" />
                    </div>

                    <div class="border-t border-ink-100 pt-5 dark:border-ink-800">
                        <h3 class="mb-1 text-sm font-bold text-ink-900 dark:text-white">Change Password</h3>
                        <p class="mb-4 text-xs text-ink-400">Leave blank to keep your current password.</p>
                        <div class="grid gap-5 sm:grid-cols-3">
                            <x-input name="current_password" label="Current Password" type="password" autocomplete="current-password" />
                            <x-input name="new_password" label="New Password" type="password" autocomplete="new-password" />
                            <x-input name="new_password_confirmation" label="Confirm Password" type="password" autocomplete="new-password" />
                        </div>
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
