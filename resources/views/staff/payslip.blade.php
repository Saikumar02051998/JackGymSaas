<x-layouts.app
    title="Payslip · {{ $salary->period }}"
    description="Salary payslip"
    :breadcrumbs="[['label' => 'Salaries', 'url' => route('staff.salaries.index')], ['label' => 'Payslip · ' . $salary->period]]">

    <x-slot name="actions">
        <div class="no-print flex items-center gap-2">
            <x-button href="{{ url()->previous() }}" variant="ghost" size="sm">
                <x-icon name="arrow-left" class="size-4" />
                Back
            </x-button>
            <x-button variant="outline" size="sm" onclick="window.print()">
                <x-icon name="print" class="size-4" />
                Print
            </x-button>
        </div>
    </x-slot>

    @php
        $gym = $salary->gym ?? current_gym();
        $staff = $salary->staff;
    @endphp

    <div class="card print-sheet">
        <div class="card-body sm:p-8">
            <x-partials.print-header :title="'PAYSLIP'" :gym="$gym">
                <p class="mt-2 text-sm font-semibold text-ink-900 dark:text-white">Period: {{ $salary->period }}</p>
                <p class="text-xs text-ink-500 dark:text-ink-400">Generated: {{ $salary->created_at->format('d M Y') }}</p>
                @if ($salary->payment_date)
                    <p class="text-xs text-ink-500 dark:text-ink-400">Paid on: {{ \Carbon\Carbon::parse($salary->payment_date)->format('d M Y') }}</p>
                @endif
                <div class="mt-2 print-block">
                    <x-badge :color="match($salary->payment_status) { 'paid' => 'green', 'partially_paid' => 'amber', 'pending' => 'blue', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $salary->payment_status)) }}</x-badge>
                </div>
            </x-partials.print-header>

            <div class="mt-6 grid gap-6 sm:grid-cols-2 print-block">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400 dark:text-ink-500">Employee</p>
                    <p class="mt-1.5 text-sm font-bold text-ink-900 dark:text-white">{{ $staff?->display_name }}</p>
                    @if ($staff?->role())
                        <p class="text-sm text-ink-500 dark:text-ink-400">Role: {{ $staff->role()->name }}</p>
                    @endif
                    @if ($staff?->user?->email)
                        <p class="text-sm text-ink-500 dark:text-ink-400">{{ $staff->user->email }}</p>
                    @endif
                    @if ($staff?->staff_id || $staff?->id)
                        <p class="text-sm text-ink-500 dark:text-ink-400">Staff ID: {{ $staff->staff_id ?? $staff->id }}</p>
                    @endif
                </div>
                <div class="sm:text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400 dark:text-ink-500">Payment Details</p>
                    <p class="mt-1.5 text-sm font-bold text-ink-900 dark:text-white">{{ $gym?->name ?? config('app.name') }}</p>
                    @if ($staff?->bank_name || $staff?->bank_account)
                        <p class="text-sm text-ink-500 dark:text-ink-400">
                            {{ $staff->bank_name ?? '—' }}
                            @if ($staff->bank_account)
                                · {{ '•••• ' . substr($staff->bank_account, -4) }}
                            @endif
                        </p>
                    @endif
                    @if ($staff?->bank_ifsc)
                        <p class="text-sm text-ink-500 dark:text-ink-400">IFSC: {{ $staff->bank_ifsc }}</p>
                    @endif
                </div>
            </div>

            @if ($salary->items->isNotEmpty())
                <div class="mt-6 overflow-hidden rounded-xl border border-ink-200 print-block print-avoid-break dark:border-ink-800">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-200 bg-ink-50 text-xs uppercase tracking-wider text-ink-500 dark:border-ink-800 dark:bg-night-900 dark:text-ink-400">
                                <th class="px-4 py-2.5 font-semibold">Component</th>
                                <th class="px-4 py-2.5 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @foreach ($salary->items as $item)
                                <tr>
                                    <td class="px-4 py-2 font-medium text-ink-900 dark:text-ink-100">{{ $item->label }}</td>
                                    <td class="px-4 py-2 text-right {{ in_array($item->type, ['deduction', 'advance']) ? 'text-red-500 dark:text-red-400' : 'text-ink-900 dark:text-ink-100' }}">
                                        {{ in_array($item->type, ['deduction', 'advance']) ? '-' : '+' }}{{ money($item->amount) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-6 flex flex-wrap justify-end gap-6 print-block">
                <div class="w-full max-w-xs space-y-1.5 border-t-2 border-ink-900/10 pt-3 text-sm dark:border-white/10">
                    <div class="flex justify-between">
                        <span class="text-ink-500 dark:text-ink-400">Basic</span>
                        <span class="font-medium text-ink-900 dark:text-white">{{ money($salary->basic) }}</span>
                    </div>
                    @if ((float) $salary->allowances > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500 dark:text-ink-400">Allowances</span>
                            <span class="font-medium text-ink-900 dark:text-white">{{ money($salary->allowances) }}</span>
                        </div>
                    @endif
                    @if ((float) $salary->commission > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500 dark:text-ink-400">Commission</span>
                            <span class="font-medium text-ink-900 dark:text-white">{{ money($salary->commission) }}</span>
                        </div>
                    @endif
                    @if ((float) $salary->bonus > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500 dark:text-ink-400">Bonus</span>
                            <span class="font-medium text-ink-900 dark:text-white">{{ money($salary->bonus) }}</span>
                        </div>
                    @endif
                    @if ((float) $salary->incentives > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500 dark:text-ink-400">Incentives</span>
                            <span class="font-medium text-ink-900 dark:text-white">{{ money($salary->incentives) }}</span>
                        </div>
                    @endif
                    @if ((float) $salary->deductions > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500 dark:text-ink-400">Deductions</span>
                            <span class="font-medium text-red-500 dark:text-red-400">-{{ money($salary->deductions) }}</span>
                        </div>
                    @endif
                    @if ((float) $salary->advance > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500 dark:text-ink-400">Advance</span>
                            <span class="font-medium text-red-500 dark:text-red-400">-{{ money($salary->advance) }}</span>
                        </div>
                    @endif
                    <div class="mt-2 flex justify-between border-t border-ink-200 pt-2 print-block dark:border-ink-800">
                        <span class="font-bold text-ink-900 dark:text-white">Net Salary</span>
                        <span class="text-lg font-extrabold text-gold-600 dark:text-gold-400">{{ money($salary->net_salary) }}</span>
                    </div>
                </div>
            </div>

            @if ($salary->notes)
                <div class="mt-6 rounded-lg bg-ink-50 px-4 py-3 text-sm text-ink-600 print-block print-avoid-break dark:bg-night-900 dark:text-ink-300">
                    <p class="font-semibold">Notes</p>
                    <p class="mt-0.5 whitespace-pre-line">{{ $salary->notes }}</p>
                </div>
            @endif

            <div class="mt-10 flex items-end justify-between text-xs text-ink-500 print-block dark:text-ink-400">
                <div>
                    <p class="border-t border-ink-300 pt-1 dark:border-ink-700">Employee Signature</p>
                </div>
                <div class="text-right">
                    <p class="border-t border-ink-300 pt-1 dark:border-ink-700">Authorized Signature</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
