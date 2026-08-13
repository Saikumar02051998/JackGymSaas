<x-layouts.app
    title="Request Leave"
    description="Submit a leave request."
    :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => 'Leaves', 'url' => route('staff.leaves.index')], ['label' => 'Request Leave']]">

    <form method="POST" action="{{ route('staff.leaves.store') }}" x-data="leaveForm('{{ old('start_date', now()->toDateString()) }}', '{{ old('end_date', now()->toDateString()) }}', {{ old('is_half_day') ? 'true' : 'false' }})">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Leave Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-select label="Leave type" name="leave_type" required placeholder="Select type">
                            <option value="casual" {{ old('leave_type') === 'casual' ? 'selected' : '' }}>Casual Leave</option>
                            <option value="sick" {{ old('leave_type') === 'sick' ? 'selected' : '' }}>Sick Leave</option>
                            <option value="paid" {{ old('leave_type') === 'paid' ? 'selected' : '' }}>Paid Leave</option>
                            <option value="unpaid" {{ old('leave_type') === 'unpaid' ? 'selected' : '' }}>Unpaid Leave</option>
                            <option value="emergency" {{ old('leave_type') === 'emergency' ? 'selected' : '' }}>Emergency Leave</option>
                        </x-select>
                        <label class="flex cursor-pointer items-center gap-2.5 self-end pb-1 text-sm text-ink-600 dark:text-ink-300">
                            <input type="checkbox" name="is_half_day" value="1" x-model="halfDay" class="size-4 rounded border-ink-300 text-gold-500 focus:ring-gold-400">
                            Half day
                        </label>
                        <template x-if="!halfDay">
                            <x-input label="Start date" type="date" name="start_date" x-model="start" required />
                        </template>
                        <template x-if="halfDay">
                            <x-input label="Date" type="date" name="start_date" x-model="start" required />
                        </template>
                        <template x-if="!halfDay">
                            <x-input label="End date" type="date" name="end_date" x-model="end" required />
                        </template>
                        <template x-if="halfDay">
                            <input type="hidden" name="end_date" x-bind:value="start">
                        </template>
                        <div class="flex items-end">
                            <p class="text-sm text-ink-500 dark:text-ink-400">
                                Duration: <strong class="text-ink-900 dark:text-white" x-text="days"></strong> day(s)
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <x-field label="Reason" name="reason">
                                <textarea name="reason" rows="3" class="input" placeholder="Why do you need this leave?">{{ old('reason') }}</textarea>
                            </x-field>
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Summary">
                    <p class="text-sm leading-relaxed text-ink-500 dark:text-ink-400">
                        Your request will be sent to your manager for approval.
                    </p>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="save" class="size-4" />
                        Submit Request
                    </x-button>
                    <a href="{{ route('staff.leaves.index') }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>

    <script>
        function leaveForm(start, end, halfDay) {
            return {
                start,
                end,
                halfDay,
                get days() {
                    if (this.halfDay) return 0.5;
                    if (!this.start || !this.end) return 0;
                    const s = new Date(this.start);
                    const e = new Date(this.end);
                    if (e < s) return 0;
                    return Math.round((e - s) / 86400000) + 1;
                },
            };
        }
    </script>
</x-layouts.app>
