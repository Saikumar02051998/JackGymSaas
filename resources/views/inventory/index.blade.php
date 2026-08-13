<x-layouts.app
    title="Inventory"
    description="Manage supplements and products in stock."
    :breadcrumbs="[['label' => 'Inventory']]">

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat label="Total Items" :value="$summary['total']" icon="box" />
        <x-stat label="Low Stock" :value="$summary['low_stock']" icon="clock" :positive="false" />
        <x-stat label="Stock Value" :value="money($summary['value'])" icon="banknotes" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-card title="Inventory">
                <form method="GET" action="{{ route('inventory.index') }}" data-ajax-filter data-target="[data-ajax-table='inventory-table']" class="mb-4 flex flex-wrap items-end gap-3 border-b border-ink-100 pb-4 dark:border-ink-800">
                    <div class="min-w-48 flex-1">
                        <x-input label="Search" name="search" value="{{ request('search') }}" placeholder="Search items..." />
                    </div>
                    <x-select label="Category" name="category">
                        <option value="all">All categories</option>
                        @foreach ($categories as $category)
                            @if ($category)
                                <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                            @endif
                        @endforeach
                    </x-select>
                    <label class="flex cursor-pointer items-center gap-2 pb-3 text-sm text-ink-600 dark:text-ink-300">
                        <input type="checkbox" name="low_stock" value="1" class="size-4 rounded border-ink-300 text-gold-500 focus:ring-gold-400" @checked(request()->boolean('low_stock'))>
                        Low stock only
                    </label>
                    <x-button type="submit">
                        <x-icon name="funnel" class="size-4" />
                        Filter
                    </x-button>
                </form>

                <div data-ajax-table="inventory-table">
                @if ($items->isEmpty())
                    <x-empty-state icon="box" title="No items found" message="Add inventory items to track stock levels." />
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                    <th class="px-5 py-3 font-semibold">Item</th>
                                    <th class="px-5 py-3 font-semibold">Stock</th>
                                    <th class="px-5 py-3 font-semibold">Purchase</th>
                                    <th class="px-5 py-3 font-semibold">Selling</th>
                                    <th class="px-5 py-3 font-semibold">Reorder</th>
                                    @if (can_manage('inventory.manage'))
                                        <th class="px-5 py-3 text-right font-semibold">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                @foreach ($items as $item)
                                    <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-ink-900 dark:text-white">{{ $item->name }}</p>
                                            <p class="text-xs text-ink-400">{{ $item->category ?: 'Uncategorized' }}@if ($item->supplier) · {{ $item->supplier }}@endif</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <x-badge :color="$item->isLowStock() ? 'red' : 'green'">{{ $item->stock }} {{ $item->unit }}</x-badge>
                                        </td>
                                        <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ money($item->purchase_price) }}</td>
                                        <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ money($item->selling_price) }}</td>
                                        <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $item->reorder_level }}</td>
                                        @if (can_manage('inventory.manage'))
                                            <td class="px-5 py-4 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <details class="group relative">
                                                        <summary class="btn-outline btn-sm cursor-pointer list-none">
                                                            <x-icon name="refresh" class="size-3.5" />
                                                            Stock
                                                        </summary>
                                                        <div class="absolute right-0 top-9 z-20 w-72 rounded-2xl border border-ink-100 bg-white p-5 shadow-xl dark:border-ink-800 dark:bg-night-900">
                                                            <p class="mb-3 text-sm font-bold text-ink-900 dark:text-white">Adjust stock: {{ $item->name }}</p>
                                                            <form method="POST" action="{{ route('inventory.stock', $item) }}" data-ajax class="space-y-3">
                                                                @csrf
                                                                <div class="grid grid-cols-2 gap-3">
                                                                    <x-select label="Type" name="type">
                                                                        @foreach (['in' => 'Stock in', 'out' => 'Stock out', 'adjustment' => 'Adjustment'] as $value => $label)
                                                                            <option value="{{ $value }}">{{ $label }}</option>
                                                                        @endforeach
                                                                    </x-select>
                                                                    <x-input label="Quantity" name="quantity" type="number" required />
                                                                </div>
                                                                <x-input label="Note" name="note" />
                                                                <x-button type="submit" size="sm" class="w-full">Apply</x-button>
                                                            </form>
                                                        </div>
                                                    </details>
                                                    <details class="group relative">
                                                        <summary class="btn-outline btn-sm cursor-pointer list-none">
                                                            <x-icon name="pencil" class="size-3.5" />
                                                            Edit
                                                        </summary>
                                                        <div class="absolute right-0 top-9 z-20 w-80 rounded-2xl border border-ink-100 bg-white p-5 shadow-xl dark:border-ink-800 dark:bg-night-900">
                                                            <p class="mb-3 text-sm font-bold text-ink-900 dark:text-white">Edit {{ $item->name }}</p>
                                                            <form method="POST" action="{{ route('inventory.update', $item) }}" data-ajax class="space-y-3">
                                                                @csrf
                                                                @method('PUT')
                                                                <x-input label="Name" name="name" value="{{ old('name', $item->name) }}" required />
                                                                <div class="grid grid-cols-2 gap-3">
                                                                    <x-input label="Category" name="category" value="{{ old('category', $item->category) }}" />
                                                                    <x-input label="Supplier" name="supplier" value="{{ old('supplier', $item->supplier) }}" />
                                                                </div>
                                                                <div class="grid grid-cols-2 gap-3">
                                                                    <x-input label="Purchase price" name="purchase_price" type="number" step="0.01" min="0" value="{{ old('purchase_price', $item->purchase_price) }}" />
                                                                    <x-input label="Selling price" name="selling_price" type="number" step="0.01" min="0" value="{{ old('selling_price', $item->selling_price) }}" />
                                                                </div>
                                                                <div class="grid grid-cols-2 gap-3">
                                                                    <x-input label="Reorder level" name="reorder_level" type="number" min="0" value="{{ old('reorder_level', $item->reorder_level) }}" />
                                                                    <x-input label="Unit" name="unit" value="{{ old('unit', $item->unit) }}" />
                                                                </div>
                                                                <x-field label="Notes" name="notes">
                                                                    <textarea name="notes" rows="2" class="input">{{ old('notes', $item->notes) }}</textarea>
                                                                </x-field>
                                                                <div class="flex gap-2">
                                                                    <x-button type="submit" size="sm" class="flex-1">Save</x-button>
                                                                    <button type="button" onclick="this.closest('details').removeAttribute('open')" class="btn-outline btn-sm">Cancel</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </details>
                                                    <form method="POST" action="{{ route('inventory.destroy', $item) }}" data-ajax
                                                          x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Remove item?', message: 'Remove {{ $item->name }} from inventory.', confirmText: 'Remove' } })">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-button type="submit" variant="ghost" size="sm" class="!text-red-500 hover:!bg-red-50 dark:hover:!bg-red-500/10">
                                                            <x-icon name="trash" class="size-4" />
                                                        </x-button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4">
                        <x-pagination :model="$items" />
                    </div>
                @endif
                </div>
            </x-card>
        </div>

        @if (can_manage('inventory.manage'))
            <div>
                <x-card title="Add Item">
                    <form method="POST" action="{{ route('inventory.store') }}" class="space-y-3">
                        @csrf
                        <x-input label="Name" name="name" value="{{ old('name') }}" required />
                        <div class="grid grid-cols-2 gap-3">
                            <x-input label="Category" name="category" value="{{ old('category') }}" />
                            <x-input label="Unit" name="unit" value="{{ old('unit', 'pcs') }}" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <x-input label="Stock" name="stock" type="number" min="0" value="{{ old('stock', 0) }}" required />
                            <x-input label="Reorder level" name="reorder_level" type="number" min="0" value="{{ old('reorder_level', 0) }}" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <x-input label="Purchase price" name="purchase_price" type="number" step="0.01" min="0" value="{{ old('purchase_price') }}" />
                            <x-input label="Selling price" name="selling_price" type="number" step="0.01" min="0" value="{{ old('selling_price') }}" />
                        </div>
                        <x-input label="Supplier" name="supplier" value="{{ old('supplier') }}" />
                        <x-field label="Notes" name="notes">
                            <textarea name="notes" rows="2" class="input">{{ old('notes') }}</textarea>
                        </x-field>
                        <x-button type="submit" class="w-full">
                            <x-icon name="plus" class="size-4" />
                            Add Item
                        </x-button>
                    </form>
                </x-card>
            </div>
        @endif
    </div>
</x-layouts.app>
