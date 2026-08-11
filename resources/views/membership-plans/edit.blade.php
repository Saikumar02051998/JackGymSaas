<x-layouts.app
    title="Edit Plan"
    description="Update {{ $plan->name }}."
    :breadcrumbs="[['label' => 'Memberships', 'url' => route('memberships.index')], ['label' => 'Plans', 'url' => route('memberships.plans.index')], ['label' => 'Edit']]">

    <form method="POST" action="{{ route('memberships.plans.update', $plan) }}" x-data="{ features: @js(old('features', $plan->features ?? [''])) }">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Plan Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-input label="Plan name" name="name" value="{{ old('name', $plan->name) }}" required />
                        </div>
                        <x-input label="Duration (days)" type="number" name="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" required min="1" />
                        <x-input label="Duration label" name="duration_label" value="{{ old('duration_label', $plan->duration_label) }}" required />
                        <x-input label="Price" type="number" step="0.01" min="0" name="price" value="{{ old('price', $plan->price) }}" required />
                        <x-input label="Discount" type="number" step="0.01" min="0" name="discount" value="{{ old('discount', $plan->discount) }}" />
                        <div class="sm:col-span-2">
                            <x-field label="Description" name="description">
                                <textarea name="description" rows="3" class="input">{{ old('description', $plan->description) }}</textarea>
                            </x-field>
                        </div>
                    </div>
                </x-card>

                <x-card title="Features">
                    <template x-for="(feature, index) in features" :key="index">
                        <div class="mb-3 flex items-center gap-2">
                            <input type="text" name="features[]" x-model="features[index]"
                                   class="input flex-1" placeholder="e.g. Full gym access">
                            <button type="button" @click="features.splice(index, 1)" class="rounded-lg p-2 text-ink-400 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10" :disabled="features.length === 1">
                                <x-icon name="x" class="size-4" />
                            </button>
                        </div>
                    </template>
                    <button type="button" @click="features.push('')" class="btn-outline btn-sm mt-2">
                        <x-icon name="plus" class="size-4" />
                        Add feature
                    </button>
                    @error('features')
                        <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p>
                    @enderror
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Pricing Summary">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Current price</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ money($plan->price) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Final amount</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ money($plan->final_amount) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Tax</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ gym_setting('tax_percent', 0) }}%</dd>
                        </div>
                    </dl>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="save" class="size-4" />
                        Save Changes
                    </x-button>
                    <a href="{{ route('memberships.plans.index') }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
