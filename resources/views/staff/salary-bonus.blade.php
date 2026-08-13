<x-layouts.app
    title="Staff Bonus"
    description="Give a bonus to all staff or specific members."
    :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => 'Salaries', 'url' => route('staff.salaries.index')], ['label' => 'Staff Bonus']]">

    <form method="POST" action="{{ route('staff.salaries.bonus.apply') }}" x-data="{ scope: '{{ old('scope', 'all') }}' }">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Bonus Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Period" name="period" value="{{ old('period', now()->format('Y-m')) }}" required placeholder="e.g. 2026-08" help="Salary period the bonus applies to." />
                        <x-input label="Bonus amount" name="amount" type="number" step="0.01" min="0" value="{{ old('amount') }}" required help="The amount added to each selected staff member's salary." />
                    </div>

                    <div class="mt-6 border-t border-ink-100 pt-5 dark:border-ink-800">
                        <p class="mb-3 text-sm font-semibold text-ink-900 dark:text-white">Apply to</p>
                        <div class="space-y-3">
                            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-ink-600 dark:text-ink-300">
                                <input type="radio" name="scope" value="all" x-model="scope" class="size-4 accent-gold-500">
                                All active staff
                            </label>
                            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-ink-600 dark:text-ink-300">
                                <input type="radio" name="scope" value="selected" x-model="scope" class="size-4 accent-gold-500">
                                Specific staff
                            </label>

                            <template x-if="scope === 'selected'">
                                <div class="mt-3 max-h-72 overflow-y-auto rounded-xl border border-ink-200 p-3 dark:border-ink-800">
                                    @forelse ($staff as $s)
                                        <label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2 py-1.5 text-sm text-ink-600 hover:bg-ink-50 dark:text-ink-300 dark:hover:bg-ink-800">
                                            <input type="checkbox" name="staff_ids[]" value="{{ $s->id }}" class="size-4 rounded border-ink-300 text-gold-500 focus:ring-gold-400" @checked(in_array($s->id, old('staff_ids', [])))>
                                            {{ $s->display_name }} ({{ $s->employee_id }})
                                        </label>
                                    @empty
                                        <p class="text-sm text-ink-400">No active staff members.</p>
                                    @endforelse
                                </div>
                            </template>
                        </div>
                        @error('staff')
                            <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6">
                        <x-field label="Notes" name="notes">
                            <textarea name="notes" rows="2" class="input" placeholder="Reason for this bonus (optional)">{{ old('notes') }}</textarea>
                        </x-field>
                    </div>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Summary">
                    <p class="text-sm leading-relaxed text-ink-500 dark:text-ink-400">
                        The bonus will be added to each selected staff member's salary record for the given period. If a salary record already exists for that period, the bonus is updated on it.
                    </p>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="gift" class="size-4" />
                        Apply Bonus
                    </x-button>
                    <a href="{{ route('staff.salaries.index') }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
