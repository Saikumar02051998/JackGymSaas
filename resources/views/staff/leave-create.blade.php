<x-layouts.app
    title="Request Leave"
    description="Submit a leave request."
    :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => 'Leaves', 'url' => route('staff.leaves.index')], ['label' => 'Request Leave']]">

    <form method="POST" action="{{ route('staff.leaves.store') }}" x-data="{ start: '{{ old('start_date', now()->toDateString()) }}', end: '{{ old('end_date', now()->toDateString()) }}', get days() { return leaveDays(this.start, this.end); } }">
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
                        <x-input label="Start date" type="date" name="start_date" x-model="start" required />
                        <x-input label="End date" type="date" name="end_date" x-model="end" required />
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
        function leaveDays(start, end) {
            if (!start || !end) return 0;
            const s = new Date(start);
            const e = new Date(end);
            if (e < s) return 0;
            return Math.round((e - s) / 86400000) + 1;
        }
    </script>
</x-layouts.app>
