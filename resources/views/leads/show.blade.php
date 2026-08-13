<x-layouts.app
    title="{{ $lead->name }}"
    description="Lead details"
    :breadcrumbs="[['label' => 'Leads', 'url' => route('leads.index')], ['label' => $lead->name]]">

    <x-slot name="actions">
        @if (can_manage('leads.edit'))
            <x-button href="{{ route('leads.edit', $lead) }}" variant="outline" size="sm">
                <x-icon name="pencil" class="size-4" />
                Edit
            </x-button>
        @endif
        @if (can_manage('leads.manage') && $lead->status !== 'converted')
            <x-button size="sm" x-on:click="$dispatch('open-modal', 'convert-modal')">
                <x-icon name="check-badge" class="size-4" />
                Convert to Client
            </x-button>
        @endif
    </x-slot>

    @error('lead')
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-600 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">{{ $message }}</div>
    @enderror

    <x-card>
        <div class="flex flex-wrap items-center gap-4">
            <span class="avatar-lg">{{ collect(explode(' ', $lead->name))->take(2)->map(fn ($w) => strtoupper($w[0]))->join('') }}</span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-bold text-ink-900 dark:text-white">{{ $lead->name }}</h2>
                    <x-badge :color="match($lead->status) { 'new' => 'blue', 'contacted' => 'purple', 'interested' => 'amber', 'trial' => 'gold', 'converted' => 'green', 'not_interested' => 'gray', 'lost' => 'red', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</x-badge>
                </div>
                <p class="mt-0.5 text-sm text-ink-400">{{ $lead->phone }}{{ $lead->email ? ' · ' . $lead->email : '' }}</p>
                <p class="mt-0.5 text-sm text-ink-500 dark:text-ink-400">Source: {{ $lead->source ?? '—' }} · Assigned to: {{ $lead->assignedTo?->name ?? 'Unassigned' }}</p>
            </div>
        </div>
    </x-card>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <x-stat label="Follow-Ups" :value="$lead->followups->count()" icon="chat" />
        <x-stat label="Interested Plan" :value="$lead->interestedPlan?->name ?? '—'" icon="target" />
        <x-stat label="Next Follow-Up" :value="$lead->follow_up_date ? \Carbon\Carbon::parse($lead->follow_up_date)->format('d M Y') : '—'" icon="calendar" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @if ($lead->followups->isNotEmpty())
                <x-card title="Follow-Ups" :padding="false">
                    <div class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($lead->followups as $followup)
                            <div class="flex flex-wrap items-start gap-3 px-5 py-4">
                                <span class="mt-1 flex size-8 shrink-0 items-center justify-center rounded-full bg-gold-400/10 text-gold-500">
                                    <x-icon name="chat" class="size-4" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-ink-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($followup->follow_up_date)->format('d M Y') }}{{ $followup->follow_up_time ? ' · ' . \Carbon\Carbon::parse($followup->follow_up_time)->format('g:i A') : '' }}
                                    </p>
                                    <p class="mt-0.5 text-sm text-ink-500 dark:text-ink-400">{{ $followup->notes ?? 'No notes' }}</p>
                                    <p class="mt-1 text-xs text-ink-400">
                                        {{ $followup->type ?? 'Follow-up' }} · by {{ $followup->creator?->name ?? 'System' }}
                                        @if ($followup->outcome)
                                            · Outcome: <span class="font-medium text-ink-600 dark:text-ink-300">{{ $followup->outcome }}</span>
                                        @endif
                                    </p>
                                </div>
                                <x-badge :color="match($followup->status) { 'completed' => 'green', 'pending' => 'amber', 'cancelled' => 'gray', default => 'gray' }">{{ ucfirst($followup->status) }}</x-badge>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif

            @if ($lead->notes)
                <x-card title="Notes">
                    <p class="whitespace-pre-line text-sm leading-relaxed text-ink-600 dark:text-ink-300">{{ $lead->notes }}</p>
                </x-card>
            @endif
        </div>

        <div class="space-y-6">
            @if (can_manage('leads.edit'))
                <x-card title="Update Status">
                    <form method="POST" action="{{ route('leads.status', $lead) }}" class="space-y-3">
                        @csrf
                        <x-select label="Status" name="status">
                            @foreach (['new' => 'New', 'contacted' => 'Contacted', 'interested' => 'Interested', 'trial' => 'Trial', 'converted' => 'Converted', 'not_interested' => 'Not Interested', 'lost' => 'Lost'] as $value => $label)
                                <option value="{{ $value }}" {{ $lead->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </x-select>
                        <x-button type="submit" class="w-full">
                            <x-icon name="refresh" class="size-4" />
                            Update
                        </x-button>
                    </form>
                </x-card>
            @endif

            @if (can_manage('leads.manage'))
                <x-card title="Assign Lead">
                    <form method="POST" action="{{ route('leads.assign', $lead) }}" class="space-y-3">
                        @csrf
                        <x-select label="Assign to" name="assigned_to" required placeholder="Select staff member">
                            @foreach ($staff as $s)
                                <option value="{{ $s->id }}" {{ old('assigned_to', $lead->assigned_to) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </x-select>
                        <x-button type="submit" class="w-full">
                            <x-icon name="users" class="size-4" />
                            Assign
                        </x-button>
                    </form>
                </x-card>
            @endif

            @if (can_manage('leads.edit'))
                <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                      x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Delete lead?', message: 'This will permanently delete {{ $lead->name }}.', confirmText: 'Delete' } })">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="outline" class="w-full !text-red-500 hover:!border-red-300">
                        <x-icon name="trash" class="size-4" />
                        Delete Lead
                    </x-button>
                </form>
            @endif
        </div>
    </div>

    @if (can_manage('leads.manage') && $lead->status !== 'converted')
        <x-modal id="convert-modal" title="Convert to Client">
            <form method="POST" action="{{ route('leads.convert', $lead) }}" id="convert-form">
                @csrf
                <p class="text-sm text-ink-500 dark:text-ink-400">
                    This will create a client profile for <strong class="text-ink-900 dark:text-white">{{ $lead->name }}</strong> and mark the lead as converted.
                </p>
                <div class="mt-4 space-y-4">
                    <label class="flex items-start gap-3 rounded-xl border border-ink-200 p-4 transition-colors has-[:checked]:border-gold-400 has-[:checked]:bg-gold-400/5 dark:border-ink-700">
                        <input type="checkbox" name="start_trial" value="1" x-data x-init="$el.checked = true" class="mt-0.5 size-4 rounded border-ink-300 text-gold-500 focus:ring-gold-400">
                        <span>
                            <span class="block text-sm font-semibold text-ink-900 dark:text-white">Start a free trial</span>
                            <span class="mt-0.5 block text-xs text-ink-400">Give this client a trial period before committing.</span>
                        </span>
                    </label>
                    <x-input label="Trial days" type="number" min="1" max="60" name="trial_days" value="7" />
                </div>
                <x-slot name="footer">
                    <div class="flex justify-end gap-3">
                        <x-button type="button" variant="outline" x-on:click="$dispatch('close-modal', 'convert-modal')">Cancel</x-button>
                        <x-button type="submit" form="convert-form">
                            <x-icon name="check-badge" class="size-4" />
                            Convert Lead
                        </x-button>
                    </div>
                </x-slot>
            </form>
        </x-modal>
    @endif
</x-layouts.app>
