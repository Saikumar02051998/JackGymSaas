<x-layouts.app
    title="Add Staff"
    description="Create a staff account for your team."
    :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => 'Add Staff']]">

    <form method="POST" action="{{ route('staff.store') }}">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Account Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Full name" name="name" value="{{ old('name') }}" required />
                        <x-input label="Email" type="email" name="email" value="{{ old('email') }}" required />
                        <x-input label="Phone" name="phone" value="{{ old('phone') }}" />
                        <x-input label="Password" type="password" name="password" required help="Minimum 8 characters." />
                    </div>
                </x-card>

                <x-card title="Role & Employment">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-select label="Role" name="role_id" required placeholder="Select a role">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <x-input label="Designation" name="designation" value="{{ old('designation') }}" placeholder="e.g. Head Coach" />
                        <x-input label="Employee ID" name="employee_id" value="{{ old('employee_id') }}" placeholder="Auto-generated if left blank" />
                        <x-input label="Joining date" type="date" name="joining_date" value="{{ old('joining_date', now()->toDateString()) }}" />
                    </div>
                </x-card>

                <x-card title="Salary">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-select label="Salary type" name="salary_type" placeholder="Select type">
                            <option value="fixed" {{ old('salary_type', 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="commission" {{ old('salary_type') === 'commission' ? 'selected' : '' }}>Commission</option>
                            <option value="contract" {{ old('salary_type') === 'contract' ? 'selected' : '' }}>Contract</option>
                        </x-select>
                        <x-input label="Basic salary" type="number" step="0.01" min="0" name="basic_salary" value="{{ old('basic_salary', 0) }}" />
                        <x-input label="Allowances" type="number" step="0.01" min="0" name="allowances" value="{{ old('allowances', 0) }}" />
                        <x-input label="Commission rate (%)" type="number" step="0.01" min="0" max="100" name="commission_rate" value="{{ old('commission_rate', 0) }}" />
                    </div>
                </x-card>

                <x-card title="Bank Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Bank name" name="bank_name" value="{{ old('bank_name') }}" placeholder="e.g. HDFC Bank" />
                        <x-input label="Account number" name="bank_account" value="{{ old('bank_account') }}" placeholder="e.g. 50100234567890" />
                        <x-input label="IFSC code" name="bank_ifsc" value="{{ old('bank_ifsc') }}" placeholder="e.g. HDFC0001234" />
                    </div>
                    <p class="mt-2 text-xs text-ink-400">Used for salary transfers and printed on the staff's payslip.</p>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Summary">
                    <p class="text-sm leading-relaxed text-ink-500 dark:text-ink-400">
                        The staff member will receive an email inviting them to the platform. They can sign in with the credentials you provide here.
                    </p>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="user-plus" class="size-4" />
                        Create Staff
                    </x-button>
                    <a href="{{ route('staff.index') }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
