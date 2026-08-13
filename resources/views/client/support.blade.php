<x-layouts.app
    title="Support"
    description="Raise support tickets and track their status."
    :breadcrumbs="[['label' => 'Support']]">

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-card>
                <div data-ajax-table="client-support-table">
                @if ($tickets->isEmpty())
                    <x-empty-state icon="support" title="No support tickets" message="Need help? Create a ticket using the form and our team will get back to you." />
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                    <th class="px-5 py-3 font-semibold">Subject</th>
                                    <th class="px-5 py-3 font-semibold">Category</th>
                                    <th class="px-5 py-3 font-semibold">Priority</th>
                                    <th class="px-5 py-3 font-semibold">Status</th>
                                    <th class="px-5 py-3 font-semibold">Opened</th>
                                    <th class="px-5 py-3 font-semibold"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                @foreach ($tickets as $ticket)
                                    <tr>
                                        <td class="px-5 py-3 font-medium text-ink-900 dark:text-white">{{ $ticket->subject }}</td>
                                        <td class="px-5 py-3 capitalize text-ink-600 dark:text-ink-300">{{ $ticket->category ?? '—' }}</td>
                                        <td class="px-5 py-3">
                                            <x-badge :color="match($ticket->priority) { 'low' => 'gray', 'medium' => 'blue', 'high' => 'amber', 'urgent' => 'red', default => 'gray' }">{{ ucfirst($ticket->priority) }}</x-badge>
                                        </td>
                                        <td class="px-5 py-3">
                                            <x-badge :color="match($ticket->status) { 'open' => 'green', 'in_progress' => 'blue', 'resolved' => 'amber', 'closed' => 'gray', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</x-badge>
                                        </td>
                                        <td class="px-5 py-3 text-ink-600 dark:text-ink-300">{{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y') }}</td>
                                        <td class="px-5 py-3 text-right">
                                            <a href="{{ route('client.support.show', $ticket) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-gold-600 hover:text-gold-500">
                                                View
                                                <x-icon name="chevron-right" class="size-3.5" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <x-pagination :model="$tickets" />
                @endif
                </div>
            </x-card>
        </div>

        <div>
            <x-card title="Create a Ticket">
                <form action="{{ route('client.support.store') }}" method="POST" data-ajax data-ajax-reset data-refresh="[data-ajax-table='client-support-table']" class="space-y-4">
                    @csrf

                    <x-input name="subject" label="Subject" :required="true" value="{{ old('subject') }}" />
                    <x-select name="category" label="Category" placeholder="Select category (optional)">
                        @foreach (['billing', 'membership', 'workout', 'diet', 'facility', 'other'] as $category)
                            <option value="{{ $category }}" @selected(old('category') === $category)>{{ ucfirst($category) }}</option>
                        @endforeach
                    </x-select>
                    <x-select name="priority" label="Priority">
                        @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', 'medium') === $value)>{{ $label }}</option>
                        @endforeach
                    </x-select>
                    <x-field label="Message" :required="true" name="message">
                        <textarea name="message" rows="5" class="input resize-none" required>{{ old('message') }}</textarea>
                    </x-field>

                    <x-button type="submit" variant="primary" class="w-full justify-center">
                        <x-icon name="support" class="size-4" />
                        Submit Ticket
                    </x-button>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.app>
