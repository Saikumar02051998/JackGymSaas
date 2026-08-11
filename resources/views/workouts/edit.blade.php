<x-layouts.app
    title="Edit Workout Plan"
    description="Update workout plan details."
    :breadcrumbs="[['label' => 'Workout Plans', 'url' => route('workouts.index')], ['label' => $workout->name, 'url' => route('workouts.show', $workout)], ['label' => 'Edit']]">

    <form method="POST" action="{{ route('workouts.update', $workout) }}" x-data="workoutForm(@js($workout->exercises->map(fn ($e) => $e->only(['day_of_week', 'exercise', 'muscle_group', 'sets', 'reps', 'weight', 'duration_minutes', 'rest_seconds', 'instructions', 'sort_order']))->values()))">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Plan Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-select label="Client" name="client_id" required placeholder="Select a client">
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id', $workout->client_id) == $client->id ? 'selected' : '' }}>{{ $client->display_name }} ({{ $client->member_id }})</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="sm:col-span-2">
                            <x-input label="Plan name" name="name" value="{{ old('name', $workout->name) }}" required placeholder="e.g. 12-Week Strength" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input label="Goal" name="goal" value="{{ old('goal', $workout->goal) }}" placeholder="e.g. Build muscle, lose fat" />
                        </div>
                        <x-input label="Start date" type="date" name="start_date" value="{{ old('start_date', $workout->start_date) }}" />
                        <x-input label="End date" type="date" name="end_date" value="{{ old('end_date', $workout->end_date) }}" />
                        <x-select label="Status" name="status">
                            <option value="active" {{ old('status', $workout->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="draft" {{ old('status', $workout->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="completed" {{ old('status', $workout->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $workout->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </x-select>
                        <div class="sm:col-span-2">
                            <x-field label="Notes" name="notes">
                                <textarea name="notes" rows="3" class="input">{{ old('notes', $workout->notes) }}</textarea>
                            </x-field>
                        </div>
                    </div>
                </x-card>

                <x-card title="Exercises">
                    <template x-for="(exercise, index) in exercises" :key="index">
                        <div class="mb-5 rounded-xl border border-ink-100 p-4 dark:border-ink-800">
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-sm font-semibold text-ink-700 dark:text-ink-200" x-text="`Exercise ${index + 1}`"></p>
                                <button type="button" @click="removeExercise(index)" class="rounded-lg p-1.5 text-ink-400 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10">
                                    <x-icon name="x" class="size-4" />
                                </button>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input type="text" :name="`exercises[${index}][exercise]`" x-model="exercise.exercise" class="input" placeholder="Exercise name *" required>
                                <input type="hidden" :name="`exercises[${index}][sort_order]`" :value="index">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-ink-500 dark:text-ink-400">Day</label>
                                    <input type="text" :name="`exercises[${index}][day_of_week]`" x-model="exercise.day_of_week" class="input" placeholder="e.g. Monday">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-ink-500 dark:text-ink-400">Muscle group</label>
                                    <input type="text" :name="`exercises[${index}][muscle_group]`" x-model="exercise.muscle_group" class="input" placeholder="e.g. Chest">
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink-500 dark:text-ink-400">Sets</label>
                                        <input type="number" :name="`exercises[${index}][sets]`" x-model="exercise.sets" class="input" min="0">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink-500 dark:text-ink-400">Reps</label>
                                        <input type="text" :name="`exercises[${index}][reps]`" x-model="exercise.reps" class="input" placeholder="8-12">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink-500 dark:text-ink-400">Weight</label>
                                        <input type="text" :name="`exercises[${index}][weight]`" x-model="exercise.weight" class="input" placeholder="20kg">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink-500 dark:text-ink-400">Duration (min)</label>
                                        <input type="number" :name="`exercises[${index}][duration_minutes]`" x-model="exercise.duration_minutes" class="input" min="0">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink-500 dark:text-ink-400">Rest (sec)</label>
                                        <input type="number" :name="`exercises[${index}][rest_seconds]`" x-model="exercise.rest_seconds" class="input" min="0">
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-medium text-ink-500 dark:text-ink-400">Instructions</label>
                                    <textarea :name="`exercises[${index}][instructions]`" x-model="exercise.instructions" rows="2" class="input"></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                    <button type="button" @click="addExercise()" class="btn-outline btn-sm">
                        <x-icon name="plus" class="size-4" />
                        Add Exercise
                    </button>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Summary">
                    <p class="text-sm leading-relaxed text-ink-500 dark:text-ink-400">
                        Exercises are grouped by day so your client can follow the plan easily from their portal.
                    </p>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="save" class="size-4" />
                        Save Changes
                    </x-button>
                    <a href="{{ route('workouts.show', $workout) }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>

    <script>
        function workoutForm(exercises) {
            return {
                exercises: exercises ?? [],
                addExercise() {
                    this.exercises.push({
                        day_of_week: '',
                        exercise: '',
                        muscle_group: '',
                        sets: '',
                        reps: '',
                        weight: '',
                        duration_minutes: '',
                        rest_seconds: '',
                        instructions: '',
                        sort_order: 0,
                    });
                },
                removeExercise(index) {
                    this.exercises.splice(index, 1);
                },
            };
        }
    </script>
</x-layouts.app>
