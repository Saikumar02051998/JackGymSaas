<x-layouts.app
    title="Process Salary"
    description="Record a salary payment for a staff member."
    :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => 'Salaries', 'url' => route('staff.salaries.index')], ['label' => 'Process Salary']]">

    @php
        $staffData = $staff->mapWithKeys(fn ($s) => [$s->id => [
            'basic' => (float) ($s->basic_salary ?? 0),
            'allowances' => (float) ($s->allowances ?? 0),
            'commission' => (float) ($s->commission_rate ?? 0),
        ]]);
    @endphp

    <form method="POST" action="{{ route('staff.salaries.pay') }}" x-data="salaryForm(@js($staffData))">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Staff & Period">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-select label="Staff member" name="staff_id" required placeholder="Select staff member" x-on:change="prefill($event.target.value)">
                                @foreach ($staff as $s)
                                    <option value="{{ $s->id }}" {{ old('staff_id') == $s->id ? 'selected' : '' }}>{{ $s->display_name }} ({{ $s->employee_id }})</option>
                                @endforeach
                            </x-select>
                            <p class="mt-1 text-xs text-ink-400">Choosing a member pre-fills their basic salary and allowances.</p>
                        </div>
                        <x-input label="Period" name="period" value="{{ old('period', now()->format('Y-m')) }}" required placeholder="e.g. 2026-08" />
                        <x-input label="Payment date" type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" />
                        <x-select label="Payment status" name="payment_status">
                            <option value="pending" {{ old('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ old('payment_status', 'paid') === 'paid' ? 'selected' : '' }}>Paid</option>
                        </x-select>
                    </div>
                </x-card>

                <x-card title="Salary Breakdown">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Basic" type="number" step="0.01" min="0" name="basic" x-model.number="basic" value="{{ old('basic') }}" />
                        <x-input label="Allowances" type="number" step="0.01" min="0" name="allowances" x-model.number="allowances" value="{{ old('allowances') }}" />
                        <x-input label="Deductions" type="number" step="0.01" min="0" name="deductions" x-model.number="deductions" value="{{ old('deductions', 0) }}" />
                        <x-input label="Bonus" type="number" step="0.01" min="0" name="bonus" x-model.number="bonus" value="{{ old('bonus', 0) }}" />
                        <x-input label="Commission" type="number" step="0.01" min="0" name="commission" x-model.number="commission" value="{{ old('commission') }}" />
                        <x-input label="Incentives" type="number" step="0.01" min="0" name="incentives" x-model.number="incentives" value="{{ old('incentives', 0) }}" />
                        <x-input label="Advance" type="number" step="0.01" min="0" name="advance" x-model.number="advance" value="{{ old('advance', 0) }}" />
                    </div>
                    <div class="mt-4 rounded-xl bg-ink-50 p-4 dark:bg-ink-800">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-ink-500 dark:text-ink-400">Net salary</span>
                            <span class="text-lg font-bold text-ink-900 dark:text-white">{{ gym_setting('currency_symbol', '₹') }}<span x-text="net.toFixed(2)"></span></span>
                        </div>
                    </div>
                </x-card>

                <x-card title="Notes">
                    <x-field label="Notes" name="notes">
                        <textarea name="notes" rows="3" class="input">{{ old('notes') }}</textarea>
                    </x-field>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Summary">
                    <p class="text-sm leading-relaxed text-ink-500 dark:text-ink-400">
                        Processing a salary will create or update the record for the selected period. Duplicate entries for the same period are prevented automatically.
                    </p>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="save" class="size-4" />
                        Process Salary
                    </x-button>
                    <a href="{{ route('staff.salaries.index') }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>

    <script>
        function salaryForm(staffData) {
            return {
                staffData,
                basic: 0,
                allowances: 0,
                deductions: 0,
                bonus: 0,
                commission: 0,
                incentives: 0,
                advance: 0,
                prefill(id) {
                    const s = this.staffData[id];
                    if (s) {
                        this.basic = s.basic;
                        this.allowances = s.allowances;
                        this.commission = s.commission;
                    }
                },
                get net() {
                    return (this.basic || 0) + (this.allowances || 0) + (this.bonus || 0) + (this.commission || 0) + (this.incentives || 0) - (this.deductions || 0) - (this.advance || 0);
                },
            };
        }
    </script>
</x-layouts.app>
