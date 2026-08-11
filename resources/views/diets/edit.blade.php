<x-layouts.app
    title="Edit Diet Plan"
    description="Update diet plan details."
    :breadcrumbs="[['label' => 'Diet Plans', 'url' => route('diets.index')], ['label' => $diet->name, 'url' => route('diets.show', $diet)], ['label' => 'Edit']]">

    <form method="POST" action="{{ route('diets.update', $diet) }}" x-data="dietForm(@js($diet->meals->map(fn ($m) => $m->only(['meal', 'meal_time', 'food', 'quantity', 'calories', 'protein', 'carbs', 'fat', 'notes', 'sort_order']))->values()))">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Plan Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-select label="Client" name="client_id" required placeholder="Select a client">
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id', $diet->client_id) == $client->id ? 'selected' : '' }}>{{ $client->display_name }} ({{ $client->member_id }})</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="sm:col-span-2">
                            <x-input label="Plan name" name="name" value="{{ old('name', $diet->name) }}" required placeholder="e.g. Lean Bulk 2500" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input label="Goal" name="goal" value="{{ old('goal', $diet->goal) }}" placeholder="e.g. Lose fat, gain muscle" />
                        </div>
                        <x-input label="Start date" type="date" name="start_date" value="{{ old('start_date', $diet->start_date) }}" />
                        <x-input label="End date" type="date" name="end_date" value="{{ old('end_date', $diet->end_date) }}" />
                        <x-select label="Status" name="status">
                            <option value="active" {{ old('status', $diet->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="draft" {{ old('status', $diet->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="completed" {{ old('status', $diet->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $diet->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </x-select>
                        <div class="sm:col-span-2">
                            <x-field label="Notes" name="notes">
                                <textarea name="notes" rows="3" class="input">{{ old('notes', $diet->notes) }}</textarea>
                            </x-field>
                        </div>
                    </div>
                </x-card>

                <x-card title="Meals & Foods">
                    <template x-for="(meal, index) in meals" :key="index">
                        <div class="mb-5 rounded-xl border border-ink-100 p-4 dark:border-ink-800">
                            <div class="mb-3 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-ink-700 dark:text-ink-200" x-text="`Food ${index + 1}`"></p>
                                    <input type="text" :name="`meals[${index}][meal]`" x-model="meal.meal" class="input input-sm w-40" placeholder="Meal (e.g. Breakfast)">
                                </div>
                                <button type="button" @click="removeMeal(index)" class="rounded-lg p-1.5 text-ink-400 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10">
                                    <x-icon name="x" class="size-4" />
                                </button>
                            </div>
                            <input type="hidden" :name="`meals[${index}][sort_order]`" :value="index">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input type="text" :name="`meals[${index}][food]`" x-model="meal.food" class="input" placeholder="Food item *" required>
                                <input type="text" :name="`meals[${index}][quantity]`" x-model="meal.quantity" class="input" placeholder="Quantity (e.g. 150g)">
                                <input type="text" :name="`meals[${index}][meal_time]`" x-model="meal.meal_time" class="input" placeholder="Time (e.g. 08:00)">
                                <div class="grid grid-cols-4 gap-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink-500 dark:text-ink-400">Kcal</label>
                                        <input type="number" :name="`meals[${index}][calories]`" x-model="meal.calories" class="input" min="0" step="0.01">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink-500 dark:text-ink-400">Protein</label>
                                        <input type="number" :name="`meals[${index}][protein]`" x-model="meal.protein" class="input" min="0" step="0.01">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink-500 dark:text-ink-400">Carbs</label>
                                        <input type="number" :name="`meals[${index}][carbs]`" x-model="meal.carbs" class="input" min="0" step="0.01">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink-500 dark:text-ink-400">Fat</label>
                                        <input type="number" :name="`meals[${index}][fat]`" x-model="meal.fat" class="input" min="0" step="0.01">
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-medium text-ink-500 dark:text-ink-400">Notes</label>
                                    <textarea :name="`meals[${index}][notes]`" x-model="meal.notes" rows="2" class="input"></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                    <button type="button" @click="addMeal()" class="btn-outline btn-sm">
                        <x-icon name="plus" class="size-4" />
                        Add Food Item
                    </button>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Summary">
                    <p class="text-sm leading-relaxed text-ink-500 dark:text-ink-400">
                        Add every food item with portion, calories and macros. Your client will see the full breakdown in their portal.
                    </p>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="save" class="size-4" />
                        Save Changes
                    </x-button>
                    <a href="{{ route('diets.show', $diet) }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>

    <script>
        function dietForm(meals) {
            return {
                meals: meals ?? [],
                addMeal() {
                    this.meals.push({
                        meal: '',
                        meal_time: '',
                        food: '',
                        quantity: '',
                        calories: '',
                        protein: '',
                        carbs: '',
                        fat: '',
                        notes: '',
                        sort_order: 0,
                    });
                },
                removeMeal(index) {
                    this.meals.splice(index, 1);
                },
            };
        }
    </script>
</x-layouts.app>
