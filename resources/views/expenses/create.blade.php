<x-layouts.app
    title="Record Expense"
    description="Record a gym expense."
    :breadcrumbs="[['label' => 'Expenses', 'url' => route('expenses.index')], ['label' => 'Record Expense']]">

    <form method="POST" action="{{ route('expenses.store') }}">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Expense Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-select label="Category" name="category_id" required placeholder="Select a category">
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </x-select>
                        <x-input label="Amount" type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required />
                        <x-input label="Expense date" type="date" name="expense_date" value="{{ old('expense_date', now()->toDateString()) }}" required />
                        <x-input label="Vendor" name="vendor" value="{{ old('vendor') }}" placeholder="e.g. Electricity Board" />
                        <div class="sm:col-span-2">
                            <x-field label="Description" name="description">
                                <textarea name="description" rows="3" class="input">{{ old('description') }}</textarea>
                            </x-field>
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Summary">
                    <p class="text-sm leading-relaxed text-ink-500 dark:text-ink-400">
                        Expenses are categorized and can be filtered in reports for a full view of your gym's finances.
                    </p>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="save" class="size-4" />
                        Record Expense
                    </x-button>
                    <a href="{{ route('expenses.index') }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
