<x-layouts.app
    title="Edit Staff"
    description="Update {{ $user->name }}'s details."
    :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => $user->name, 'url' => route('staff.show', $user)], ['label' => 'Edit']]">

    <form method="POST" action="{{ route('staff.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Account Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Full name" name="name" value="{{ old('name', $user->name) }}" required />
                        <x-input label="Email" type="email" name="email" value="{{ old('email', $user->email) }}" required />
                        <x-input label="Phone" name="phone" value="{{ old('phone', $user->phone) }}" />
                        <x-input label="Password" type="password" name="password" help="Leave blank to keep the current password." />
                    </div>
                </x-card>

                <x-card title="Role & Employment">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-select label="Role" name="role_id" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $user->roles->first()?->id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <x-input label="Designation" name="designation" value="{{ old('designation', $user->staffProfile?->designation) }}" />
                        <x-input label="Employee ID" name="employee_id" value="{{ old('employee_id', $user->staffProfile?->employee_id) }}" />
                        <x-input label="Joining date" type="date" name="joining_date" value="{{ old('joining_date', $user->staffProfile?->joining_date) }}" />
                        <x-select label="Status" name="status">
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </x-select>
                    </div>
                </x-card>

                <x-card title="Salary">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-select label="Salary type" name="salary_type">
                            <option value="fixed" {{ old('salary_type', $user->staffProfile?->salary_type) === 'fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="commission" {{ old('salary_type', $user->staffProfile?->salary_type) === 'commission' ? 'selected' : '' }}>Commission</option>
                            <option value="contract" {{ old('salary_type', $user->staffProfile?->salary_type) === 'contract' ? 'selected' : '' }}>Contract</option>
                        </x-select>
                        <x-input label="Basic salary" type="number" step="0.01" min="0" name="basic_salary" value="{{ old('basic_salary', $user->staffProfile?->basic_salary) }}" />
                        <x-input label="Allowances" type="number" step="0.01" min="0" name="allowances" value="{{ old('allowances', $user->staffProfile?->allowances) }}" />
                        <x-input label="Commission rate (%)" type="number" step="0.01" min="0" max="100" name="commission_rate" value="{{ old('commission_rate', $user->staffProfile?->commission_rate) }}" />
                    </div>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Summary">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Member since</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ $user->staffProfile?->joining_date ? \Carbon\Carbon::parse($user->staffProfile->joining_date)->format('d M Y') : '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Created</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ \Carbon\Carbon::parse($user->created_at)->format('d M Y') }}</dd>
                        </div>
                    </dl>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="save" class="size-4" />
                        Save Changes
                    </x-button>
                    <a href="{{ route('staff.show', $user) }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
