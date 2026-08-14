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
                <p class="mt-2 text-sm font-semibold text-ink-900">Period: {{ $salary->period }}</p>
                <p class="text-xs text-ink-500">Generated: {{ $salary->created_at->format('d M Y') }}</p>
                @if ($salary->payment_date)
                    <p class="text-xs text-ink-500">Paid on: {{ \Carbon\Carbon::parse($salary->payment_date)->format('d M Y') }}</p>
                @endif
                <div class="mt-2 print-block">
                    <x-badge :color="match($salary->payment_status) { 'paid' => 'green', 'partially_paid' => 'amber', 'pending' => 'blue', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $salary->payment_status)) }}</x-badge>
                </div>
            </x-partials.print-header>

            <div class="mt-6 grid gap-6 sm:grid-cols-2 print-block">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Employee</p>
                    <p class="mt-1.5 text-sm font-bold text-ink-900">{{ $staff?->display_name }}</p>
                    @if ($staff?->role())
                        <p class="text-sm text-ink-500">Role: {{ $staff->role()->name }}</p>
                    @endif
                    @if ($staff?->user?->email)
                        <p class="text-sm text-ink-500">{{ $staff->user->email }}</p>
                    @endif
                    @if ($staff?->staff_id || $staff?->id)
                        <p class="text-sm text-ink-500">Staff ID: {{ $staff->staff_id ?? $staff->id }}</p>
                    @endif
                </div>
                <div class="sm:text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Payment Details</p>
                    <p class="mt-1.5 text-sm font-bold text-ink-900">{{ $gym?->name ?? config('app.name') }}</p>
                    @if ($staff?->bank_name || $staff?->bank_account)
                        <p class="text-sm text-ink-500">
                            {{ $staff->bank_name ?? '—' }}
                            @if ($staff->bank_account)
                                · {{ '•••• ' . substr($staff->bank_account, -4) }}
                            @endif
                        </p>
                    @endif
                    @if ($staff?->bank_ifsc)
                        <p class="text-sm text-ink-500">IFSC: {{ $staff->bank_ifsc }}</p>
                    @endif
                </div>
            </div>

            @if ($salary->items->isNotEmpty())
                <div class="mt-6 overflow-hidden rounded-xl border border-ink-200 print-block print-avoid-break">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-ink-200 bg-ink-50 text-xs uppercase tracking-wider text-ink-500">
                                <th class="px-4 py-2.5 font-semibold">Component</th>
                                <th class="px-4 py-2.5 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100">
                            @foreach ($salary->items as $item)
                                <tr>
                                    <td class="px-4 py-2 font-medium text-ink-900">{{ $item->label }}</td>
                                    <td class="px-4 py-2 text-right {{ in_array($item->type, ['deduction', 'advance']) ? 'text-red-500' : 'text-ink-900' }}">
                                        {{ in_array($item->type, ['deduction', 'advance']) ? '-' : '+' }}{{ money($item->amount) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-6 flex flex-wrap justify-end gap-6 print-block">
                <div class="w-full max-w-xs space-y-1.5 border-t-2 border-ink-900/10 pt-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-ink-500">Basic</span>
                        <span class="font-medium text-ink-900">{{ money($salary->basic) }}</span>
                    </div>
                    @if ((float) $salary->allowances > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500">Allowances</span>
                            <span class="font-medium text-ink-900">{{ money($salary->allowances) }}</span>
                        </div>
                    @endif
                    @if ((float) $salary->commission > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500">Commission</span>
                            <span class="font-medium text-ink-900">{{ money($salary->commission) }}</span>
                        </div>
                    @endif
                    @if ((float) $salary->bonus > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500">Bonus</span>
                            <span class="font-medium text-ink-900">{{ money($salary->bonus) }}</span>
                        </div>
                    @endif
                    @if ((float) $salary->incentives > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500">Incentives</span>
                            <span class="font-medium text-ink-900">{{ money($salary->incentives) }}</span>
                        </div>
                    @endif
                    @if ((float) $salary->deductions > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500">Deductions</span>
                            <span class="font-medium text-red-500">-{{ money($salary->deductions) }}</span>
                        </div>
                    @endif
                    @if ((float) $salary->advance > 0)
                        <div class="flex justify-between">
                            <span class="text-ink-500">Advance</span>
                            <span class="font-medium text-red-500">-{{ money($salary->advance) }}</span>
                        </div>
                    @endif
                    <div class="mt-2 flex justify-between border-t border-ink-200 pt-2 print-block">
                        <span class="font-bold text-ink-900">Net Salary</span>
                        <span class="text-lg font-extrabold text-ink-900">{{ money($salary->net_salary) }}</span>
                    </div>
                </div>
            </div>

            @if ($salary->notes)
                <div class="mt-6 rounded-lg bg-ink-50 px-4 py-3 text-sm text-ink-600 print-block print-avoid-break">
                    <p class="font-semibold">Notes</p>
                    <p class="mt-0.5 whitespace-pre-line">{{ $salary->notes }}</p>
                </div>
            @endif

            <div class="mt-10 flex items-end justify-between text-xs text-ink-500 print-block">
                <div>
                    <p class="border-t border-ink-300 pt-1">Employee Signature</p>
                </div>
                <div class="text-right">
                    <p class="border-t border-ink-300 pt-1">Authorized Signature</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
