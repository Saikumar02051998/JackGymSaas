<x-layouts.app
    title="Expenses"
    description="Track gym expenses."
    :breadcrumbs="[['label' => 'Expenses']]">

    <x-slot name="actions">
        @if (can_manage('expenses.create'))
            <x-button href="{{ route('expenses.create') }}" size="sm">
                <x-icon name="plus" class="size-4" />
                Record Expense
            </x-button>
        @endif
        <x-button href="{{ route('expenses.export', request()->only(['from', 'to'])) }}" variant="outline" size="sm">
            <x-icon name="download" class="size-4" />
            Export
        </x-button>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-stat label="Total Expenses" :value="money($summary['total'])" icon="banknotes" />
        <x-stat label="This Month" :value="money($summary['month'])" icon="calendar" />
    </div>

    <x-card :padding="false" class="mt-6">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4 dark:border-ink-800">
            <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-1 flex-wrap items-center gap-2">
                <input type="date" name="from" value="{{ request('from') }}" class="input w-auto">
                <input type="date" name="to" value="{{ request('to') }}" class="input w-auto">
                <select name="category" class="input w-auto">
                    <option value="all" {{ request('category', 'all') === 'all' ? 'selected' : '' }}>All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <x-button type="submit">Filter</x-button>
            </form>
        </div>

        @if ($expenses->isEmpty())
            <div class="p-8">
                <x-empty-state icon="banknotes" title="No expenses found" message="Recorded expenses will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-5 py-3 font-semibold">Category</th>
                            <th class="px-5 py-3 font-semibold">Vendor</th>
                            <th class="px-5 py-3 font-semibold">Description</th>
                            <th class="px-5 py-3 font-semibold">Amount</th>
                            @if (can_manage('expenses.manage'))
                                <th class="px-5 py-3 text-right font-semibold">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($expenses as $expense)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                                <td class="px-5 py-4">
                                    <x-badge color="gray">{{ $expense->category?->name ?? '—' }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $expense->vendor ?? '—' }}</td>
                                <td class="px-5 py-4 max-w-64 truncate text-ink-500 dark:text-ink-400" title="{{ $expense->description }}">{{ $expense->description ?? '—' }}</td>
                                <td class="px-5 py-4 font-bold text-ink-900 dark:text-white">{{ money($expense->amount) }}</td>
                                @if (can_manage('expenses.manage'))
                                    <td class="px-5 py-4 text-right">
                                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                                              x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Delete expense?', message: 'This will permanently delete this expense record.', confirmText: 'Delete' } })">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="ghost" size="sm" class="!text-red-500 hover:!bg-red-50 dark:hover:!bg-red-500/10">
                                                <x-icon name="trash" class="size-4" />
                                            </x-button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$expenses" />
            </div>
        @endif
    </x-card>
</x-layouts.app>
