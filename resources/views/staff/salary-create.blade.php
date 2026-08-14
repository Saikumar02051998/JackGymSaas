<x-layouts.app
    title="Process Salary"
    description="Record a salary payment for a staff member."
    :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => 'Salaries', 'url' => route('staff.salaries.index')], ['label' => 'Process Salary']]">

    @php
        $staffData = $staff->mapWithKeys(fn ($s) => [$s->id => [
            'basic' => (float) ($s->basic_salary ?? 0),
            'allowances' => (float) ($s->allowances ?? 0),
            'commission' => (float) ($s->commission_rate ?? 0),
            'deduction' => (float) ($deductions[$s->id]['deduction'] ?? 0),
            'bank_name' => $s->bank_name,
            'bank_account' => $s->bank_account,
            'bank_ifsc' => $s->bank_ifsc,
        ]]);
    @endphp

    <form method="POST" action="{{ route('staff.salaries.pay') }}" x-data="salaryForm(@js($staffData), '{{ old('period', $defaultPeriod) }}')">
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
                            <p class="mt-1 text-xs text-ink-400">Choosing a member pre-fills their basic salary, allowances and leave deduction.</p>
                        </div>
                        <x-input label="Period" name="period" value="{{ old('period', $defaultPeriod) }}" required placeholder="e.g. 2026-08" x-on:change="recompute()" />
                        <x-input label="Payment date" type="date" name="payment_date" value="{{ old('payment_date', $defaultPaymentDate->format('Y-m-d')) }}" />
                        <x-select label="Payment status" name="payment_status">
                            <option value="pending" {{ old('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ old('payment_status', 'paid') === 'paid' ? 'selected' : '' }}>Paid</option>
                        </x-select>
                    </div>
                </x-card>

                <x-card title="Bank Details">
                    <template x-if="selected">
                        <div>
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Bank name</p>
                                    <p class="mt-1 text-sm font-bold text-ink-900 dark:text-white" x-text="bankDetails.bank_name || '—'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Account number</p>
                                    <p class="mt-1 text-sm font-bold text-ink-900 dark:text-white" x-text="bankDetails.bank_account || '—'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">IFSC code</p>
                                    <p class="mt-1 text-sm font-bold text-ink-900 dark:text-white" x-text="bankDetails.bank_ifsc || '—'"></p>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-ink-100 pt-3 dark:border-ink-800">
                                <button type="button" class="btn-outline btn-sm" x-show="bankDetails.bank_account" x-on:click="copy(bankDetails.bank_account)">
                                    <x-icon name="document-text" class="size-4" />
                                    Copy account
                                </button>
                                <button type="button" class="btn-outline btn-sm" x-show="bankDetails.bank_ifsc" x-on:click="copy(bankDetails.bank_ifsc)">
                                    <x-icon name="document-text" class="size-4" />
                                    Copy IFSC
                                </button>
                                <p class="text-xs text-ink-400">Use these details to transfer the salary amount.</p>
                            </div>
                        </div>
                    </template>
                    <template x-if="!selected">
                        <p class="text-sm text-ink-500 dark:text-ink-400">Select a staff member to see their bank details for the transfer.</p>
                    </template>
                </x-card>

                <x-card title="Salary Breakdown">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Basic" type="number" step="0.01" min="0" name="basic" x-model.number="basic" value="{{ old('basic') }}" x-on:change="recompute()" />
                        <x-input label="Allowances" type="number" step="0.01" min="0" name="allowances" x-model.number="allowances" value="{{ old('allowances') }}" x-on:change="recompute()" />
                        <x-input label="Deductions" type="number" step="0.01" min="0" name="deductions" x-model.number="deductions" value="{{ old('deductions') }}" help="Leave deductions are pre-filled. Adjust if needed." />
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

                <x-card title="Leave Deduction">
                    <template x-if="selected">
                        <div>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Per-day rate</p>
                                    <p class="mt-1 text-sm font-bold text-ink-900 dark:text-white">{{ gym_setting('currency_symbol', '₹') }}<span x-text="preview.per_day.toFixed(2)"></span></p>
                                    <p class="text-xs text-ink-400" x-text="'÷ ' + preview.calendar_days + ' days'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Approved full days</p>
                                    <p class="mt-1 text-sm font-bold text-ink-900 dark:text-white"><span x-text="preview.full_days"></span> <span class="font-normal text-ink-400" x-text="'(allowance ' + preview.paid_leave_days + ')'"></span></p>
                                    <p class="text-xs text-ink-400" x-text="'Excess: ' + preview.excess_full_days + ' day(s)'"></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Half days</p>
                                    <p class="mt-1 text-sm font-bold text-ink-900 dark:text-white"><span x-text="preview.half_days"></span> <span class="font-normal text-ink-400" x-text="'(allowance ' + preview.paid_half_days + ')'"></span></p>
                                    <p class="text-xs text-ink-400" x-text="'Excess: ' + preview.excess_half_days + ' day(s)'"></p>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between border-t border-ink-100 pt-3 dark:border-ink-800">
                                <span class="text-sm font-medium text-ink-500 dark:text-ink-400">Auto-calculated deduction</span>
                                <span class="text-sm font-bold text-red-500">{{ gym_setting('currency_symbol', '₹') }}<span x-text="preview.deduction.toFixed(2)"></span></span>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn-outline btn-sm" x-on:click="applyDeduction()">
                                    Apply this deduction
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="!selected">
                        <p class="text-sm text-ink-500 dark:text-ink-400">Select a staff member to see their approved leave deduction for the selected period.</p>
                    </template>
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
        function salaryForm(staffData, period) {
            return {
                staffData,
                period,
                selected: null,
                basic: 0,
                allowances: 0,
                deductions: 0,
                bonus: 0,
                commission: 0,
                incentives: 0,
                advance: 0,
                preview: {
                    full_days: 0,
                    half_days: 0,
                    excess_full_days: 0,
                    excess_half_days: 0,
                    paid_leave_days: 0,
                    paid_half_days: 0,
                    calendar_days: 30,
                    per_day: 0,
                    deduction: 0,
                },
                prefill(id) {
                    this.selected = id;
                    const s = this.staffData[id];
                    if (s) {
                        this.basic = s.basic;
                        this.allowances = s.allowances;
                        this.commission = s.commission;
                        this.deductions = s.deduction || 0;
                    }
                    this.recompute();
                },
                recompute() {
                    if (!this.selected) return;
                    const el = document.querySelector('[name="period"]');
                    if (el) this.period = el.value;
                    fetch('{{ route('staff.salaries.deduction-preview') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            staff_id: this.selected,
                            period: this.period,
                            basic: this.basic,
                            allowances: this.allowances,
                        }),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data && typeof data.deduction === 'number') {
                            this.preview = data;
                        }
                    })
                    .catch(() => {});
                },
                applyDeduction() {
                    this.deductions = this.preview.deduction;
                },
                copy(text) {
                    if (!text) return;
                    navigator.clipboard.writeText(text)
                        .then(() => window.toast?.('Copied to clipboard', 'success'))
                        .catch(() => window.toast?.('Could not copy', 'error'));
                },
                get bankDetails() {
                    return this.staffData[this.selected] || { bank_name: null, bank_account: null, bank_ifsc: null };
                },
                get net() {
                    return (this.basic || 0) + (this.allowances || 0) + (this.bonus || 0) + (this.commission || 0) + (this.incentives || 0) - (this.deductions || 0) - (this.advance || 0);
                },
            };
        }
    </script>
</x-layouts.app>
