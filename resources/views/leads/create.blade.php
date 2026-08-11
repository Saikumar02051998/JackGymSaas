<x-layouts.app
    title="Add Lead"
    description="Capture a prospective member."
    :breadcrumbs="[['label' => 'Leads', 'url' => route('leads.index')], ['label' => 'Add Lead']]">

    <form method="POST" action="{{ route('leads.store') }}">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Contact Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Full name" name="name" value="{{ old('name') }}" required />
                        <x-input label="Phone" name="phone" value="{{ old('phone') }}" required />
                        <x-input label="Email" type="email" name="email" value="{{ old('email') }}" />
                        <x-input label="Source" name="source" value="{{ old('source') }}" placeholder="e.g. Walk-in, Instagram, Referral" />
                    </div>
                </x-card>

                <x-card title="Sales Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-select label="Interested plan" name="interested_plan_id" placeholder="Select a plan">
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" {{ old('interested_plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }} · {{ $plan->duration_label }}</option>
                            @endforeach
                        </x-select>
                        <x-select label="Assign to" name="assigned_to" placeholder="Assign a staff member">
                            @foreach ($staff as $s)
                                <option value="{{ $s->user_id }}" {{ old('assigned_to') == $s->user_id ? 'selected' : '' }}>{{ $s->display_name }}</option>
                            @endforeach
                        </x-select>
                        <x-select label="Status" name="status" placeholder="Select status">
                            @foreach (['new' => 'New', 'contacted' => 'Contacted', 'interested' => 'Interested', 'trial' => 'Trial', 'converted' => 'Converted', 'not_interested' => 'Not Interested', 'lost' => 'Lost'] as $value => $label)
                                <option value="{{ $value }}" {{ old('status', 'new') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </x-select>
                        <x-input label="Follow-up date" type="date" name="follow_up_date" value="{{ old('follow_up_date') }}" help="An initial follow-up will be scheduled for this date." />
                        <div class="sm:col-span-2">
                            <x-field label="Notes" name="notes">
                                <textarea name="notes" rows="3" class="input">{{ old('notes') }}</textarea>
                            </x-field>
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Summary">
                    <p class="text-sm leading-relaxed text-ink-500 dark:text-ink-400">
                        The lead will be assigned to you by default if no staff member is selected.
                    </p>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="user-plus" class="size-4" />
                        Create Lead
                    </x-button>
                    <a href="{{ route('leads.index') }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
