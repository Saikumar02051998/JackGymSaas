<x-layouts.app
    title="Invoices"
    description="Manage member invoices."
    :breadcrumbs="[['label' => 'Invoices']]">

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat label="Total Invoiced" :value="money($summary['total'])" icon="document-text" />
        <x-stat label="Collected" :value="money($summary['paid'])" icon="check-badge" positive />
        <x-stat label="Outstanding" :value="money($summary['pending'])" icon="clock" />
    </div>

    <x-card :padding="false" class="mt-6">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4 dark:border-ink-800">
            <form method="GET" action="{{ route('invoices.index') }}" class="flex flex-1 flex-wrap items-center gap-2">
                <div class="relative min-w-52 flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-ink-400"><x-icon name="search" class="size-4" /></span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search invoice no, client..." class="input pl-9">
                </div>
                <select name="status" class="input w-auto">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All statuses</option>
                    @foreach (['draft' => 'Draft', 'issued' => 'Issued', 'paid' => 'Paid', 'void' => 'Void', 'refunded' => 'Refunded'] as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <x-button type="submit">Filter</x-button>
            </form>
        </div>

        @if ($invoices->isEmpty())
            <div class="p-8">
                <x-empty-state icon="document-text" title="No invoices found" message="Generated invoices will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Invoice</th>
                            <th class="px-5 py-3 font-semibold">Client</th>
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-5 py-3 font-semibold">Due</th>
                            <th class="px-5 py-3 font-semibold">Total</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($invoices as $invoice)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4 font-semibold text-ink-900 dark:text-white">{{ $invoice->invoice_no }}</td>
                                <td class="px-5 py-4">
                                    <span class="font-medium text-ink-900 dark:text-white">{{ $invoice->client?->display_name ?? 'Deleted client' }}</span>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '—' }}</td>
                                <td class="px-5 py-4 font-bold text-ink-900 dark:text-white">{{ money($invoice->grand_total) }}</td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($invoice->status) { 'paid' => 'green', 'issued' => 'blue', 'draft' => 'gray', 'void' => 'gray', 'refunded' => 'purple', default => 'gray' }">{{ ucfirst($invoice->status) }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="btn-outline btn-sm">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$invoices" />
            </div>
        @endif
    </x-card>
</x-layouts.app>
