<x-layouts.app
    title="Settings"
    description="Configure your gym details and payment gateway."
    :breadcrumbs="[['label' => 'Settings']]">

    @php
        $reminderDays = array_map('intval', json_decode((string) gym_setting('membership_reminder_days', '[]'), true) ?: []);
    @endphp

    <div class="mx-auto max-w-3xl space-y-6">
        <x-card title="Gym Information">
            @if ($gym->logo)
                <img src="{{ asset('storage/' . $gym->logo) }}" alt="{{ $gym->name }}" class="mb-4 size-16 rounded-xl object-cover">
            @endif
            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <x-input label="Gym name" name="name" value="{{ old('name', $gym->name) }}" required />
                <x-field label="Address" name="address">
                    <textarea name="address" rows="2" class="input">{{ old('address', $gym->address) }}</textarea>
                </x-field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="Phone" name="phone" value="{{ old('phone', $gym->phone) }}" />
                    <x-input label="Email" name="email" type="email" value="{{ old('email', $gym->email) }}" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="Website" name="website" type="url" value="{{ old('website', $gym->website) }}" />
                    <x-input label="Timezone" name="timezone" value="{{ old('timezone', $gym->timezone) }}" help="e.g. Asia/Kolkata" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="Currency code" name="currency" value="{{ old('currency', $gym->currency) }}" help="e.g. INR" />
                    <x-input label="Currency symbol" name="currency_symbol" value="{{ old('currency_symbol', $gym->currency_symbol) }}" help="e.g. ₹" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="Tax percent (%)" name="tax_percent" type="number" step="0.01" min="0" max="100" value="{{ old('tax_percent', $gym->tax_percent) }}" />
                    <x-input label="Invoice prefix" name="invoice_prefix" value="{{ old('invoice_prefix', $gym->invoice_prefix) }}" help="e.g. INV" />
                </div>

                <x-field label="Logo" name="logo">
                    <input type="file" name="logo" accept="image/jpg,image/jpeg,image/png,image/webp" class="input">
                </x-field>

                <div>
                    <p class="mb-2 text-sm font-semibold text-ink-900 dark:text-white">Membership expiry reminders</p>
                    <p class="mb-3 text-xs text-ink-400">Notify clients before their membership expires.</p>
                    <div class="flex flex-wrap gap-4">
                        @foreach ([7 => '7 days before', 15 => '15 days before', 30 => '30 days before'] as $days => $label)
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-600 dark:text-ink-300">
                                <input type="checkbox" name="membership_reminder_days[]" value="{{ $days }}"
                                       class="size-4 rounded border-ink-300 text-gold-500 focus:ring-gold-400"
                                       @checked(in_array($days, $reminderDays))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end border-t border-ink-100 pt-4 dark:border-ink-800">
                    <x-button type="submit">
                        <x-icon name="save" class="size-4" />
                        Save Settings
                    </x-button>
                </div>
            </form>
        </x-card>

        @if (can_manage('settings.manage'))
            <x-card title="Salary Rules">
                <p class="mb-4 text-xs text-ink-400">Control how staff salaries and leave deductions are calculated.</p>
                <form method="POST" action="{{ route('settings.salary-rules') }}" class="space-y-4">
                    @csrf

                    <div>
                        <p class="mb-2 text-sm font-semibold text-ink-900 dark:text-white">Monthly salary calendar</p>
                        <p class="mb-3 text-xs text-ink-400">The monthly salary is divided by this many days to get the per-day rate used for leave deductions.</p>
                        <div class="flex gap-4">
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-600 dark:text-ink-300">
                                <input type="radio" name="calendar_days" value="30" class="size-4 accent-gold-500" @checked(old('calendar_days', $salaryRules['calendar_days']) === 30)>
                                Divide by 30 days
                            </label>
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-600 dark:text-ink-300">
                                <input type="radio" name="calendar_days" value="28" class="size-4 accent-gold-500" @checked(old('calendar_days', $salaryRules['calendar_days']) === 28)>
                                Divide by 28 days
                            </label>
                        </div>
                        @error('calendar_days')
                            <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Paid leave days per month" name="paid_leave_days" type="number" min="0" max="31" value="{{ old('paid_leave_days', $salaryRules['paid_leave_days']) }}" help="Full leave days that are paid before deductions apply." />
                        <x-input label="Paid half-day leaves per month" name="paid_half_days" type="number" min="0" max="62" value="{{ old('paid_half_days', $salaryRules['paid_half_days']) }}" help="Half-day leaves that are fully paid." />
                    </div>

                    <div class="flex justify-end border-t border-ink-100 pt-4 dark:border-ink-800">
                        <x-button type="submit">
                            <x-icon name="save" class="size-4" />
                            Save Salary Rules
                        </x-button>
                    </div>
                </form>

                <div class="mt-4 rounded-xl bg-ink-50 px-4 py-3 dark:bg-ink-800/60">
                    <ul class="space-y-2 text-xs leading-relaxed text-ink-500 dark:text-ink-400">
                        <li class="flex gap-2">
                            <x-icon name="sparkles" class="mt-0.5 size-3.5 shrink-0 text-gold-500" />
                            <span><strong>Per-day rate</strong> = (basic + allowances) &divide; calendar days.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="sparkles" class="mt-0.5 size-3.5 shrink-0 text-gold-500" />
                            <span>Approved full-day leaves beyond the <strong>paid leave days</strong> allowance are deducted from the salary.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="sparkles" class="mt-0.5 size-3.5 shrink-0 text-gold-500" />
                            <span>Half-day leaves beyond the <strong>paid half-day</strong> allowance are deducted at half the per-day rate each.</span>
                        </li>
                        <li class="flex gap-2">
                            <x-icon name="sparkles" class="mt-0.5 size-3.5 shrink-0 text-gold-500" />
                            <span>Deductions are pre-filled automatically when processing a salary and can be adjusted.</span>
                        </li>
                    </ul>
                </div>
            </x-card>
        @endif

        @if (can_manage('settings.manage'))
            <x-card title="Payment Gateway">
                <div class="mb-4">
                    @if ($razorpayConfigured)
                        <x-badge color="green">Razorpay configured</x-badge>
                    @else
                        <x-badge color="amber">Razorpay not configured</x-badge>
                    @endif
                </div>
                <form method="POST" action="{{ route('settings.payment-gateway') }}" class="space-y-4">
                    @csrf

                    <x-input label="Razorpay Key ID" name="key_id" value="{{ old('key_id', (string) gym_setting('razorpay_key_id')) }}" autocomplete="off" help="Saved per gym; leave blank to keep current" />
                    <x-input label="Razorpay Key Secret" name="key_secret" type="password" autocomplete="new-password" help="Leave blank to keep current" />
                    <x-input label="Webhook Secret" name="webhook_secret" type="password" autocomplete="new-password" help="Leave blank to keep current" />

                    <div class="flex justify-end border-t border-ink-100 pt-4 dark:border-ink-800">
                        <x-button type="submit">
                            <x-icon name="save" class="size-4" />
                            Save Gateway
                        </x-button>
                    </div>
                </form>
            </x-card>
        @endif
    </div>
</x-layouts.app>
