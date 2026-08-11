<x-layouts.app
    title="Edit Lead"
    description="Update {{ $lead->name }}."
    :breadcrumbs="[['label' => 'Leads', 'url' => route('leads.index')], ['label' => $lead->name, 'url' => route('leads.show', $lead)], ['label' => 'Edit']]">

    <form method="POST" action="{{ route('leads.update', $lead) }}">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Contact Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Full name" name="name" value="{{ old('name', $lead->name) }}" required />
                        <x-input label="Phone" name="phone" value="{{ old('phone', $lead->phone) }}" required />
                        <x-input label="Email" type="email" name="email" value="{{ old('email', $lead->email) }}" />
                        <x-input label="Source" name="source" value="{{ old('source', $lead->source) }}" />
                    </div>
                </x-card>

                <x-card title="Sales Details">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-select label="Interested plan" name="interested_plan_id" placeholder="Select a plan">
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" {{ old('interested_plan_id', $lead->interested_plan_id) == $plan->id ? 'selected' : '' }}>{{ $plan->name }} · {{ $plan->duration_label }}</option>
                            @endforeach
                        </x-select>
                        <x-select label="Assign to" name="assigned_to" placeholder="Assign a staff member">
                            @foreach ($staff as $s)
                                <option value="{{ $s->user_id }}" {{ old('assigned_to', $lead->assigned_to) == $s->user_id ? 'selected' : '' }}>{{ $s->display_name }}</option>
                            @endforeach
                        </x-select>
                        <x-select label="Status" name="status">
                            @foreach (['new' => 'New', 'contacted' => 'Contacted', 'interested' => 'Interested', 'trial' => 'Trial', 'converted' => 'Converted', 'not_interested' => 'Not Interested', 'lost' => 'Lost'] as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $lead->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </x-select>
                        <x-input label="Follow-up date" type="date" name="follow_up_date" value="{{ old('follow_up_date', $lead->follow_up_date) }}" />
                        <div class="sm:col-span-2">
                            <x-field label="Notes" name="notes">
                                <textarea name="notes" rows="3" class="input">{{ old('notes', $lead->notes) }}</textarea>
                            </x-field>
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Lead Summary">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Created</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ \Carbon\Carbon::parse($lead->created_at)->format('d M Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-400">Converted</dt>
                            <dd class="font-semibold text-ink-900 dark:text-white">{{ $lead->converted_at ? \Carbon\Carbon::parse($lead->converted_at)->format('d M Y') : '—' }}</dd>
                        </div>
                    </dl>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="save" class="size-4" />
                        Save Changes
                    </x-button>
                    <a href="{{ route('leads.show', $lead) }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
