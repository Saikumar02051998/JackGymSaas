<x-layouts.app
    title="Salaries"
    description="Manage staff salary records."
    :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => 'Salaries']]">

    <x-slot name="actions">
        @if (can_manage('salary.manage'))
            <x-button href="{{ route('staff.salaries.bonus') }}" size="sm" variant="ghost">
                <x-icon name="gift" class="size-4" />
                Give Bonus
            </x-button>
            <x-button href="{{ route('staff.salaries.create') }}" size="sm">
                <x-icon name="plus" class="size-4" />
                Process Salary
            </x-button>
        @endif
    </x-slot>

    <x-card :padding="false">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4 dark:border-ink-800">
            <form method="GET" action="{{ route('staff.salaries.index') }}" data-ajax-filter data-target="[data-ajax-table='salaries-table']" class="flex flex-wrap items-center gap-2">
                <select name="period" class="input w-auto">
                    <option value="">All periods</option>
                    @foreach ($periods as $p)
                        <option value="{{ $p }}" {{ request('period') === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
                <select name="status" class="input w-auto">
                    <option value="">All statuses</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                <x-button type="submit">Filter</x-button>
            </form>
        </div>

        <div data-ajax-table="salaries-table">
        @if ($salaries->isEmpty())
            <div class="p-8">
                <x-empty-state icon="banknotes" title="No salary records" message="Process salaries to see records here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Period</th>
                            <th class="px-5 py-3 font-semibold">Staff</th>
                            <th class="px-5 py-3 font-semibold">Basic</th>
                            <th class="px-5 py-3 font-semibold">Allowances</th>
                            <th class="px-5 py-3 font-semibold">Deductions</th>
                            <th class="px-5 py-3 font-semibold">Bonus</th>
                            <th class="px-5 py-3 font-semibold">Commission</th>
                            <th class="px-5 py-3 font-semibold">Net</th>
                            <th class="px-5 py-3 font-semibold">Paid On</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($salaries as $salary)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4 font-semibold text-ink-900 dark:text-white">{{ $salary->period }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-ink-900 dark:text-white">{{ $salary->staff?->display_name ?? 'Removed staff' }}</p>
                                    <p class="text-xs text-ink-400">{{ $salary->staff?->designation ?? '' }}</p>
                                </td>
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$salaries" />
            </div>
        @endif
        </div>
    </x-card>
</x-layouts.app>
