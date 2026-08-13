<x-layouts.app
    title="Salary Rules"
    description="Configure how staff salaries are calculated."
    :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => 'Salaries', 'url' => route('staff.salaries.index')], ['label' => 'Salary Rules']]">

    <div class="mx-auto max-w-3xl space-y-6">
        <x-card title="Salary Rules">
            <form method="POST" action="{{ route('staff.salaries.rules.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <p class="mb-2 text-sm font-semibold text-ink-900 dark:text-white">Monthly salary calendar</p>
                    <p class="mb-3 text-xs text-ink-400">The monthly salary is divided by this many days to get the per-day rate used for leave deductions.</p>
                    <div class="flex gap-4">
                        <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-600 dark:text-ink-300">
                            <input type="radio" name="calendar_days" value="30" class="size-4 accent-gold-500" @checked(old('calendar_days', $rules['calendar_days']) === 30)>
                            Divide by 30 days
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 text-sm text-ink-600 dark:text-ink-300">
                            <input type="radio" name="calendar_days" value="28" class="size-4 accent-gold-500" @checked(old('calendar_days', $rules['calendar_days']) === 28)>
                            Divide by 28 days
                        </label>
                    </div>
                    @error('calendar_days')
                        <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="Paid leave days per month" name="paid_leave_days" type="number" min="0" max="31" value="{{ old('paid_leave_days', $rules['paid_leave_days']) }}" help="Full leave days that are paid before deductions apply." />
                    <x-input label="Paid half-day leaves per month" name="paid_half_days" type="number" min="0" max="62" value="{{ old('paid_half_days', $rules['paid_half_days']) }}" help="Half-day leaves that are fully paid." />
                </div>

                <div class="flex gap-3 pt-2">
                    <x-button type="submit">
                        <x-icon name="save" class="size-4" />
                        Save Rules
                    </x-button>
                    <a href="{{ route('staff.salaries.index') }}" class="btn-outline">Cancel</a>
                </div>
            </form>
        </x-card>

        <x-card title="How deductions work">
            <ul class="space-y-3 text-sm text-ink-600 dark:text-ink-300">
                <li class="flex gap-2">
                    <x-icon name="sparkles" class="mt-0.5 size-4 shrink-0 text-gold-500" />
                    <span><strong>Per-day rate</strong> = (basic + allowances) &divide; calendar days.</span>
                </li>
                <li class="flex gap-2">
                    <x-icon name="sparkles" class="mt-0.5 size-4 shrink-0 text-gold-500" />
                    <span>Approved full-day leaves beyond the <strong>paid leave days</strong> allowance are deducted from the salary.</span>
                </li>
                <li class="flex gap-2">
                    <x-icon name="sparkles" class="mt-0.5 size-4 shrink-0 text-gold-500" />
                    <span>Half-day leaves beyond the <strong>paid half-day</strong> allowance are deducted at half the per-day rate each.</span>
                </li>
                <li class="flex gap-2">
                    <x-icon name="sparkles" class="mt-0.5 size-4 shrink-0 text-gold-500" />
                    <span>Deductions are pre-filled automatically when processing a salary and can be adjusted.</span>
                </li>
            </ul>
        </x-card>
    </div>
</x-layouts.app>
