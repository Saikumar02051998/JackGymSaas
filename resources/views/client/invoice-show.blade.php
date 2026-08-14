<x-layouts.app
    title="{{ $invoice->invoice_no }}"
    description="Invoice details"
    :breadcrumbs="[['label' => 'My Invoices', 'url' => route('client.invoices')], ['label' => $invoice->invoice_no]]">

    <x-slot name="actions">
        <x-button href="{{ route('client.invoices') }}" variant="ghost" size="sm">
            <x-icon name="arrow-left" class="size-4" />
            Back
        </x-button>
        <x-button variant="outline" size="sm" onclick="window.print()">
            <x-icon name="print" class="size-4" />
            Print
        </x-button>
    </x-slot>

    @php
        $gym = $invoice->gym ?? current_gym();
        $client = $invoice->client;
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
                <h2 class="text-2xl font-extrabold tracking-tight text-gold-600 dark:text-gold-400">INVOICE</h2>
                <p class="mt-1 text-sm font-semibold text-ink-900 dark:text-white">{{ $invoice->invoice_no }}</p>
                <p class="text-sm text-ink-500 dark:text-ink-400">Issued: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</p>
                @if ($invoice->due_date)
                    <p class="text-sm text-ink-500 dark:text-ink-400">Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</p>
                @endif
                <div class="mt-2">
                    <x-badge :color="match($invoice->status) { 'paid' => 'green', 'issued' => 'blue', 'draft' => 'gray', 'void' => 'red', 'refunded' => 'purple', default => 'gray' }">{{ ucfirst($invoice->status) }}</x-badge>
                </div>
            </div>
        </div>

        <div class="mt-8 grid gap-6 sm:grid-cols-2">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Billed To</p>
                <p class="mt-1.5 text-sm font-bold text-ink-900 dark:text-white">{{ $client?->display_name }}</p>
                <p class="text-sm text-ink-500 dark:text-ink-400">Member ID: {{ $client?->member_id }}</p>
                @if ($client?->address)
                    <p class="text-sm text-ink-500 dark:text-ink-400">{{ $client->address }}</p>
                @endif
                @if ($client?->phone)
                    <p class="text-sm text-ink-500 dark:text-ink-400">{{ $client->phone }}</p>
                @endif
            </div>
            <div class="sm:text-right">
                <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Membership</p>
                @if ($invoice->membership?->plan)
                    <p class="mt-1.5 text-sm font-bold text-ink-900 dark:text-white">{{ $invoice->membership->plan->name }}</p>
                    <p class="text-sm text-ink-500 dark:text-ink-400">
                        {{ \Carbon\Carbon::parse($invoice->membership->start_date)->format('d M Y') }} &rarr; {{ \Carbon\Carbon::parse($invoice->membership->end_date)->format('d M Y') }}
                    </p>
                @else
                    <p class="mt-1.5 text-sm text-ink-500 dark:text-ink-400">—</p>
                @endif
            </div>
        </div>

        @if ($invoice->items->isNotEmpty())
            <div class="mt-8 overflow-x-auto rounded-xl border border-ink-100 dark:border-ink-800">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 bg-ink-50/60 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800 dark:bg-ink-800/40">
                            <th class="px-4 py-3 font-semibold">Description</th>
                            <th class="px-4 py-3 text-right font-semibold">Qty</th>
                            <th class="px-4 py-3 text-right font-semibold">Unit Price</th>
                            <th class="px-4 py-3 text-right font-semibold">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($invoice->items as $item)
                            <tr>
                                <td class="px-4 py-3 !whitespace-normal font-medium text-ink-900 dark:text-white">{{ $item->description }}</td>
                                <td class="px-4 py-3 text-right">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right">{{ money($item->unit_price) }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ money($item->amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="mt-6 flex justify-end">
            <div class="w-full max-w-xs space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-ink-400">Subtotal</span>
                    <span class="font-medium text-ink-900 dark:text-white">{{ money($invoice->subtotal) }}</span>
                </div>
                @if ((float) $invoice->discount > 0)
                    <div class="flex justify-between">
                        <span class="text-ink-400">Discount</span>
                        <span class="font-medium text-red-500">-{{ money($invoice->discount) }}</span>
                    </div>
                @endif
                @if ((float) $invoice->tax > 0)
                    <div class="flex justify-between">
                        <span class="text-ink-400">Tax</span>
                        <span class="font-medium text-ink-900 dark:text-white">{{ money($invoice->tax) }}</span>
                    </div>
                @endif
                <div class="flex justify-between border-t border-ink-100 pt-2 dark:border-ink-800">
                    <span class="font-bold text-ink-900 dark:text-white">Grand Total</span>
                    <span class="text-lg font-extrabold text-ink-900 dark:text-white">{{ money($invoice->grand_total) }}</span>
                </div>
            </div>
        </div>

        @if ($invoice->payment)
            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-emerald-500/10 px-4 py-3 text-sm">
                <p class="font-semibold text-emerald-700 dark:text-emerald-400">
                    Paid via {{ ucfirst($invoice->payment->payment_method) }} on {{ \Carbon\Carbon::parse($invoice->payment->payment_date)->format('d M Y') }}
                </p>
                <p class="font-bold text-emerald-700 dark:text-emerald-400">{{ money($invoice->payment->final_amount) }}</p>
            </div>
        @endif

        @if ($invoice->notes)
            <p class="mt-6 text-sm text-ink-500 dark:text-ink-400">{{ $invoice->notes }}</p>
        @endif
    </x-card>
</x-layouts.app>
