<x-layouts.app
    title="Payslip · {{ $salary->period }}"
    description="Salary payslip"
    :breadcrumbs="[['label' => 'Salaries', 'url' => route('staff.salaries.index')], ['label' => 'Payslip · ' . $salary->period]]">

    <x-slot name="actions">
        <x-button href="{{ url()->previous() }}" variant="ghost" size="sm">
            <x-icon name="arrow-left" class="size-4" />
            Back
        </x-button>
        <x-button variant="outline" size="sm" onclick="window.print()">
            <x-icon name="print" class="size-4" />
            Print
        </x-button>
    </x-slot>

    @php
        $gym = $salary->gym ?? current_gym();
        $staff = $salary->staff;
    @endphp

    <x-card>
        <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xl font-extrabold tracking-tight text-ink-900 dark:text-white">{{ $gym?->name ?? config('app.name') }}</p>
                @if ($gym?->address)
                    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">{{ $gym->address }}</p>
                @endif
                @if ($gym?->phone || $gym?->email)
                    <p class="text-sm text-ink-500 dark:text-ink-400">
                        {{ $gym?->phone ? $gym->phone . ' · ' : '' }}{{ $gym?->email }}
                    </p>
                @endif
            </div>
            <div class="text-left sm:text-right">
                <h2 class="text-2xl font-extrabold tracking-tight text-gold-600 dark:text-gold-400">PAYSLIP</h2>
                <p class="mt-1 text-sm font-semibold text-ink-900 dark:text-white">Period: {{ $salary->period }}</p>
                <p class="text-sm text-ink-500 dark:text-ink-400">Generated: {{ $salary->created_at->format('d M Y') }}</p>
                @if ($salary->payment_date)
                    <p class="text-sm text-ink-500 dark:text-ink-400">Paid on: {{ \Carbon\Carbon::parse($salary->payment_date)->format('d M Y') }}</p>
                @endif
                <div class="mt-2">
                    <x-badge :color="match($salary->payment_status) { 'paid' => 'green', 'partially_paid' => 'amber', 'pending' => 'blue', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $salary->payment_status)) }}</x-badge>
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-6 sm:grid-cols-2">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Employee</p>
                <p class="mt-1.5 text-sm font-bold text-ink-900 dark:text-white">{{ $staff?->display_name }}</p>
                @if ($staff?->role())
                    <p class="text-sm text-ink-500 dark:text-ink-400">Role: {{ $staff->role()->name }}</p>
                @endif
                @if ($staff?->user?->email)
                    <p class="text-sm text-ink-500 dark:text-ink-400">{{ $staff->user->email }}</p>
                @endif
            </div>
            <div class="sm:text-right">
                <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Payment Details</p>
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
            <div class="mt-8 overflow-x-auto rounded-xl border border-ink-100 dark:border-ink-800">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 bg-ink-50/60 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800 dark:bg-ink-800/40">
                            <th class="px-4 py-3 font-semibold">Component</th>
                            <th class="px-4 py-3 text-right font-semibold">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($salary->items as $item)
                            <tr>
                                <td class="px-4 py-3 font-medium text-ink-900 dark:text-white">{{ $item->label }}</td>
                                <td class="px-4 py-3 text-right {{ in_array($item->type, ['deduction', 'advance']) ? 'text-red-500' : 'text-ink-900 dark:text-white' }}">
                                    {{ in_array($item->type, ['deduction', 'advance']) ? '-' : '+' }}{{ money($item->amount) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-6 flex justify-end">
            <div class="w-full max-w-xs space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-ink-400">Basic</span>
                    <span class="font-medium text-ink-900 dark:text-white">{{ money($salary->basic) }}</span>
                </div>
                @if ((float) $salary->allowances > 0)
                    <div class="flex justify-between">
                        <span class="text-ink-400">Allowances</span>
                        <span class="font-medium text-ink-900 dark:text-white">{{ money($salary->allowances) }}</span>
                    </div>
                @endif
                @if ((float) $salary->commission > 0)
                    <div class="flex justify-between">
                        <span class="text-ink-400">Commission</span>
                        <span class="font-medium text-ink-900 dark:text-white">{{ money($salary->commission) }}</span>
                    </div>
                @endif
                @if ((float) $salary->bonus > 0)
                    <div class="flex justify-between">
                        <span class="text-ink-400">Bonus</span>
                        <span class="font-medium text-ink-900 dark:text-white">{{ money($salary->bonus) }}</span>
                    </div>
                @endif
                @if ((float) $salary->incentives > 0)
                    <div class="flex justify-between">
                        <span class="text-ink-400">Incentives</span>
                        <span class="font-medium text-ink-900 dark:text-white">{{ money($salary->incentives) }}</span>
                    </div>
                @endif
                @if ((float) $salary->deductions > 0)
                    <div class="flex justify-between">
                        <span class="text-ink-400">Deductions</span>
                        <span class="font-medium text-red-500">-{{ money($salary->deductions) }}</span>
                    </div>
                @endif
                @if ((float) $salary->advance > 0)
                    <div class="flex justify-between">
                        <span class="text-ink-400">Advance</span>
                        <span class="font-medium text-red-500">-{{ money($salary->advance) }}</span>
                    </div>
                @endif
                <div class="flex justify-between border-t border-ink-100 pt-2 dark:border-ink-800">
                    <span class="font-bold text-ink-900 dark:text-white">Net Salary</span>
                    <span class="text-lg font-extrabold text-ink-900 dark:text-white">{{ money($salary->net_salary) }}</span>
                </div>
            </div>
        </div>

        @if ($salary->notes)
            <p class="mt-6 text-sm text-ink-500 dark:text-ink-400">{{ $salary->notes }}</p>
        @endif
    </x-card>
</x-layouts.app>
