<x-layouts.app
    title="Edit Subscription Plan"
    description="Update this subscription plan."
    :breadcrumbs="[['label' => 'SaaS', 'url' => route('saas.dashboard')], ['label' => 'Plans', 'url' => route('saas.plans.index')], ['label' => $plan->name]]">

    <div class="mx-auto max-w-2xl">
        <x-card>
            <form method="POST" action="{{ route('saas.plans.update', $plan) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-input label="Plan name" name="name" value="{{ old('name', $plan->name) }}" required />

                <x-field label="Description" name="description">
                    <textarea name="description" rows="3" class="input">{{ old('description', $plan->description) }}</textarea>
                </x-field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input label="Monthly price" name="price_monthly" type="number" step="0.01" min="0" value="{{ old('price_monthly', $plan->price_monthly) }}" required />
                    <x-input label="Yearly price" name="price_yearly" type="number" step="0.01" min="0" value="{{ old('price_yearly', $plan->price_yearly) }}" required />
                </div>

                <x-field label="Status" name="status">
                    <select name="status" class="input">
                        <option value="active" {{ old('status', $plan->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $plan->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </x-field>

                <div class="flex justify-end border-t border-ink-100 pt-4 dark:border-ink-800">
                    <x-button type="submit">
                        <x-icon name="save" class="size-4" />
                        Save Changes
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
