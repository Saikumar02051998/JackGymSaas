<x-layouts.app
    title="Equipment"
    description="Track gym equipment and maintenance."
    :breadcrumbs="[['label' => 'Equipment']]">

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Total Equipment" :value="$summary['total']" icon="dumbbell" />
        <x-stat label="Active" :value="$summary['active']" icon="check-badge" />
        <x-stat label="In Maintenance" :value="$summary['maintenance']" icon="wrench" />
        <x-stat label="Maintenance Due" :value="$summary['due']" icon="clock" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-card title="Equipment">
                <form method="GET" action="{{ route('equipment.index') }}" class="mb-4 flex flex-wrap items-end gap-3 border-b border-ink-100 pb-4 dark:border-ink-800">
                    <div class="min-w-48 flex-1">
                        <x-input label="Search" name="search" value="{{ request('search') }}" placeholder="Search equipment..." />
                    </div>
                    <x-select label="Status" name="status">
                        <option value="all">All statuses</option>
                        @foreach (['active' => 'Active', 'maintenance' => 'Maintenance', 'retired' => 'Retired'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </x-select>
                    <x-button type="submit">
                        <x-icon name="funnel" class="size-4" />
                        Filter
                    </x-button>
                </form>

                @if ($equipment->isEmpty())
                    <x-empty-state icon="dumbbell" title="No equipment found" message="Add your gym equipment to start tracking maintenance." />
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                    <th class="px-5 py-3 font-semibold">Equipment</th>
                                    <th class="px-5 py-3 font-semibold">Condition</th>
                                    <th class="px-5 py-3 font-semibold">Status</th>
                                    <th class="px-5 py-3 font-semibold">Next Maintenance</th>
                                    @if (can_manage('equipment.manage'))
                                        <th class="px-5 py-3 text-right font-semibold">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                @foreach ($equipment as $item)
                                    <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-ink-900 dark:text-white">{{ $item->name }}</p>
                                            <p class="text-xs text-ink-400">{{ collect([$item->category, $item->location])->filter()->implode(' · ') ?: 'No category' }}</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <x-badge :color="match($item->condition) { 'excellent' => 'green', 'good' => 'blue', 'fair' => 'amber', 'poor' => 'red', 'needs_repair' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $item->condition)) }}</x-badge>
                                        </td>
                                        <td class="px-5 py-4">
                                            <x-badge :color="match($item->status) { 'active' => 'green', 'maintenance' => 'amber', 'retired' => 'gray', default => 'gray' }">{{ ucfirst($item->status) }}</x-badge>
                                        </td>
                                        <td class="px-5 py-4 text-ink-600 dark:text-ink-300">
                                            @if ($item->next_maintenance)
                                                <span class="{{ $item->isMaintenanceDue() ? 'font-semibold text-red-500' : '' }}">{{ \Carbon\Carbon::parse($item->next_maintenance)->format('d M Y') }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        @if (can_manage('equipment.manage'))
                                            <td class="px-5 py-4 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <details class="group relative">
                                                        <summary class="btn-outline btn-sm cursor-pointer list-none">
                                                            <x-icon name="pencil" class="size-3.5" />
                                                            Edit
                                                        </summary>
                                                        <div class="absolute right-0 top-9 z-20 w-96 rounded-2xl border border-ink-100 bg-white p-5 shadow-xl dark:border-ink-800 dark:bg-night-900">
                                                            <p class="mb-3 text-sm font-bold text-ink-900 dark:text-white">Edit {{ $item->name }}</p>
                                                            <form method="POST" action="{{ route('equipment.update', $item) }}" class="space-y-3">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="grid grid-cols-2 gap-3">
                                                                    <x-input label="Name" name="name" value="{{ old('name', $item->name) }}" required />
                                                                    <x-input label="Category" name="category" value="{{ old('category', $item->category) }}" />
                                                                </div>
                                                                <div class="grid grid-cols-2 gap-3">
                                                                    <x-input label="Purchase date" name="purchase_date" type="date" value="{{ old('purchase_date', $item->purchase_date) }}" />
                                                                    <x-input label="Purchase cost" name="purchase_cost" type="number" step="0.01" min="0" value="{{ old('purchase_cost', $item->purchase_cost) }}" />
                                                                </div>
                                                                <div class="grid grid-cols-2 gap-3">
                                                                    <x-input label="Warranty until" name="warranty_until" type="date" value="{{ old('warranty_until', $item->warranty_until) }}" />
                                                                    <x-input label="Location" name="location" value="{{ old('location', $item->location) }}" />
                                                                </div>
                                                                <div class="grid grid-cols-2 gap-3">
                                                                    <x-input label="Last maintenance" name="last_maintenance" type="date" value="{{ old('last_maintenance', $item->last_maintenance) }}" />
                                                                    <x-input label="Next maintenance" name="next_maintenance" type="date" value="{{ old('next_maintenance', $item->next_maintenance) }}" />
                                                                </div>
                                                                <div class="grid grid-cols-2 gap-3">
                                                                    <x-select label="Condition" name="condition">
                                                                        @foreach (['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor', 'needs_repair' => 'Needs repair'] as $value => $label)
                                                                            <option value="{{ $value }}" @selected(old('condition', $item->condition) === $value)>{{ $label }}</option>
                                                                        @endforeach
                                                                    </x-select>
                                                                    <x-select label="Status" name="status">
                                                                        @foreach (['active' => 'Active', 'maintenance' => 'Maintenance', 'retired' => 'Retired'] as $value => $label)
                                                                            <option value="{{ $value }}" @selected(old('status', $item->status) === $value)>{{ $label }}</option>
                                                                        @endforeach
                                                                    </x-select>
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
                                                    <form method="POST" action="{{ route('equipment.destroy', $item) }}"
                                                          x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Remove equipment?', message: 'Remove {{ $item->name }} from equipment.', confirmText: 'Remove' } })">
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
                        <x-pagination :model="$equipment" />
                    </div>
                @endif
            </x-card>
        </div>

        @if (can_manage('equipment.manage'))
            <div>
                <x-card title="Add Equipment">
                    <form method="POST" action="{{ route('equipment.store') }}" class="space-y-3">
                        @csrf
                        <x-input label="Name" name="name" value="{{ old('name') }}" required />
                        <div class="grid grid-cols-2 gap-3">
                            <x-input label="Category" name="category" value="{{ old('category') }}" />
                            <x-input label="Location" name="location" value="{{ old('location') }}" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <x-input label="Purchase date" name="purchase_date" type="date" value="{{ old('purchase_date') }}" />
                            <x-input label="Purchase cost" name="purchase_cost" type="number" step="0.01" min="0" value="{{ old('purchase_cost') }}" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <x-input label="Warranty until" name="warranty_until" type="date" value="{{ old('warranty_until') }}" />
                            <x-input label="Next maintenance" name="next_maintenance" type="date" value="{{ old('next_maintenance') }}" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <x-select label="Condition" name="condition">
                                @foreach (['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor', 'needs_repair' => 'Needs repair'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('condition', 'good') === $value)>{{ $label }}</option>
                                @endforeach
                            </x-select>
                            <x-select label="Status" name="status">
                                @foreach (['active' => 'Active', 'maintenance' => 'Maintenance', 'retired' => 'Retired'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', 'active') === $value)>{{ $label }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <x-field label="Notes" name="notes">
                            <textarea name="notes" rows="2" class="input">{{ old('notes') }}</textarea>
                        </x-field>
                        <x-button type="submit" class="w-full">
                            <x-icon name="plus" class="size-4" />
                            Add Equipment
                        </x-button>
                    </form>
                </x-card>
            </div>
        @endif
    </div>
</x-layouts.app>
