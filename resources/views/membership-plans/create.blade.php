<x-layouts.app
    title="New Membership Plan"
    description="Create a membership package."
    :breadcrumbs="[['label' => 'Memberships', 'url' => route('memberships.index')], ['label' => 'Plans', 'url' => route('memberships.plans.index')], ['label' => 'New Plan']]">

    <form method="POST" action="{{ route('memberships.plans.store') }}" x-data="{ features: [''] }">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Plan Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-input label="Plan name" name="name" value="{{ old('name') }}" required placeholder="e.g. Gold Monthly" />
                        </div>
                        <x-input label="Duration (days)" type="number" name="duration_days" value="{{ old('duration_days') }}" required min="1" />
                        <x-input label="Duration label" name="duration_label" value="{{ old('duration_label') }}" required placeholder="e.g. 1 Month" />
                        <x-input label="Price" type="number" step="0.01" min="0" name="price" value="{{ old('price') }}" required />
                        <x-input label="Discount" type="number" step="0.01" min="0" name="discount" value="{{ old('discount', 0) }}" />
                        <div class="sm:col-span-2">
                            <x-field label="Description" name="description">
                                <textarea name="description" rows="3" class="input" placeholder="Short description shown to clients.">{{ old('description') }}</textarea>
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
                            <dt class="text-ink-400">Tax</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ gym_setting('tax_percent', 0) }}%</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Currency</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ gym_setting('currency', 'INR') }}</dd>
                        </div>
                    </dl>
                    <p class="mt-4 rounded-xl bg-ink-50 p-3 text-xs leading-relaxed text-ink-500 dark:bg-ink-800 dark:text-ink-400">
                        The final amount (after discount and tax) is calculated automatically when you save.
                    </p>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="save" class="size-4" />
                        Create Plan
                    </x-button>
                    <a href="{{ route('memberships.plans.index') }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
