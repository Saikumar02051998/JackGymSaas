<x-layouts.app
    title="My Payslips"
    description="View and print your salary payslips."
    :breadcrumbs="[['label' => 'My Payslips']]">

    <x-card :padding="false">
        @if ($salaries->isEmpty())
            <div class="p-8">
                <x-empty-state icon="banknotes" title="No payslips yet" message="Payslips will appear here once your salary has been processed." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Period</th>
                            <th class="px-5 py-3 font-semibold">Basic</th>
                            <th class="px-5 py-3 font-semibold">Allowances</th>
                            <th class="px-5 py-3 font-semibold">Deductions</th>
                            <th class="px-5 py-3 font-semibold">Bonus</th>
                            <th class="px-5 py-3 font-semibold">Commission</th>
                            <th class="px-5 py-3 font-semibold">Net</th>
                            <th class="px-5 py-3 font-semibold">Paid On</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($salaries as $salary)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4 font-semibold text-ink-900 dark:text-white">{{ $salary->period }}</td>
                                <td class="px-5 py-4">{{ money($salary->basic) }}</td>
                                <td class="px-5 py-4">{{ money($salary->allowances) }}</td>
                                <td class="px-5 py-4 text-red-500">{{ money($salary->deductions) }}</td>
                                <td class="px-5 py-4">{{ money($salary->bonus) }}</td>
                                <td class="px-5 py-4">{{ money($salary->commission) }}</td>
                                <td class="px-5 py-4 font-bold text-ink-900 dark:text-white">{{ money($salary->net_salary) }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $salary->payment_date ? \Carbon\Carbon::parse($salary->payment_date)->format('d M Y') : '—' }}</td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($salary->payment_status) { 'paid' => 'green', 'partially_paid' => 'amber', 'pending' => 'blue', 'cancelled' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $salary->payment_status)) }}</x-badge>
                                </td>
                                <td class="px-5 py-4">
                                    <x-button href="{{ route('staff.payslips.show', $salary) }}" size="sm">
                                        <x-icon name="document-text" class="size-4" />
                                        Payslip
                                    </x-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$salaries" />
            </div>
        @endif
    </x-card>
</x-layouts.app>
