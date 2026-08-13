<x-layouts.guest title="Invoice {{ $invoice->invoice_no }}">
    @push('head')
        <style>
            @media print {
                .no-print { display: none !important; }
                body { background: #fff !important; }
                .print-sheet { box-shadow: none !important; border: none !important; margin: 0 !important; }
            }
        </style>
    @endpush

    <div class="mx-auto max-w-3xl px-4 py-10">
        <div class="mb-6 flex justify-end no-print">
            <button type="button" onclick="window.print()" class="btn-primary">
                <x-icon name="download" class="size-4" />
                Print
            </button>
        </div>

        <div class="print-sheet rounded-2xl border border-ink-100 bg-white p-8 shadow-sm dark:border-ink-800 dark:bg-ink-900">
            <div class="flex flex-wrap items-start justify-between gap-6 border-b border-ink-100 pb-6 dark:border-ink-800">
                <div>
                    <h1 class="text-2xl font-extrabold text-ink-900 dark:text-white">{{ $invoice->gym?->name ?? config('app.name') }}</h1>
                    @if ($invoice->gym)
                        <p class="mt-2 text-sm leading-relaxed text-ink-500 dark:text-ink-400">
                            {{ $invoice->gym->address }}<br>
                            {{ $invoice->gym->phone }}{{ $invoice->gym->email ? ' · ' . $invoice->gym->email : '' }}
                        </p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-3xl font-extrabold tracking-tight text-gold-500">INVOICE</p>
                    <p class="mt-2 text-sm text-ink-500 dark:text-ink-400">
                        <strong class="text-ink-900 dark:text-white">{{ $invoice->invoice_no }}</strong><br>
                        Date: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}<br>
                        @if ($invoice->due_date)
                            Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-start justify-between gap-6 py-6">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Billed To</p>
                    @if ($invoice->client)
                        <p class="mt-2 text-base font-semibold text-ink-900 dark:text-white">{{ $invoice->client->display_name }}</p>
                        <p class="mt-0.5 text-sm text-ink-500 dark:text-ink-400">{{ $invoice->client->member_id }}</p>
                        @if ($invoice->client->phone)
                            <p class="text-sm text-ink-500 dark:text-ink-400">{{ $invoice->client->phone }}</p>
                        @endif
                    @else
                        <p class="mt-2 text-base font-semibold text-ink-400">Deleted client</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Status</p>
                    <p class="mt-2 text-base font-semibold text-ink-900 dark:text-white">{{ ucfirst($invoice->status) }}</p>
                </div>
            </div>

            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-ink-200 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-700">
                        <th class="pb-3 font-semibold">Description</th>
                        <th class="pb-3 text-center font-semibold">Qty</th>
                        <th class="pb-3 text-right font-semibold">Unit Price</th>
                        <th class="pb-3 text-right font-semibold">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td class="py-3 text-ink-900 dark:text-white">{{ $item->description }}</td>
                            <td class="py-3 text-center">{{ $item->quantity }}</td>
                            <td class="py-3 text-right">{{ money($item->unit_price) }}</td>
                            <td class="py-3 text-right font-semibold text-ink-900 dark:text-white">{{ money($item->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6 ml-auto w-full max-w-64 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-ink-500 dark:text-ink-400">Subtotal</span>
                    <span class="font-semibold text-ink-900 dark:text-white">{{ money($invoice->subtotal) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-500 dark:text-ink-400">Discount</span>
                    <span class="font-semibold text-red-500">-{{ money($invoice->discount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-ink-500 dark:text-ink-400">Tax</span>
                    <span class="font-semibold text-ink-900 dark:text-white">{{ money($invoice->tax) }}</span>
                </div>
                <div class="flex justify-between border-t border-ink-200 pt-3 dark:border-ink-700">
                    <span class="text-base font-bold text-ink-900 dark:text-white">Grand Total</span>
                    <span class="text-xl font-extrabold text-ink-900 dark:text-white">{{ money($invoice->grand_total) }}</span>
                </div>
            </div>

            @if ($invoice->notes)
                <div class="mt-8 rounded-xl bg-ink-50 p-4 text-sm text-ink-600 dark:bg-ink-800 dark:text-ink-300">
                    <p class="font-semibold">Notes</p>
                    <p class="mt-1 whitespace-pre-line">{{ $invoice->notes }}</p>
                </div>
            @endif

            <p class="mt-10 border-t border-ink-100 pt-6 text-center text-xs text-ink-400 dark:border-ink-800">
                Thank you for training with {{ $invoice->gym?->name ?? 'us' }}! &middot; {{ now()->format('Y') }}
            </p>
        </div>
    </div>
</x-layouts.guest>
