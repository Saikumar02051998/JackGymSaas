<x-layouts.app
    title="Ticket #{{ $ticket->id }}"
    description="Support ticket thread."
    :breadcrumbs="[['label' => 'Support', 'url' => route('support.index')], ['label' => '#' . $ticket->id]]">

    <div class="mx-auto max-w-3xl space-y-6">
        <div id="ticket-header" data-ajax-table="ticket-header">
        <x-card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-bold text-ink-900 dark:text-white">{{ $ticket->subject }}</h2>
                        <x-badge :color="match($ticket->priority) { 'urgent' => 'red', 'high' => 'amber', 'medium' => 'blue', default => 'gray' }">{{ ucfirst($ticket->priority) }} priority</x-badge>
                        <x-badge :color="match($ticket->status) { 'open' => 'red', 'in_progress' => 'amber', 'resolved' => 'green', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</x-badge>
                    </div>
                    <p class="mt-1 text-xs text-ink-400">
                        Opened by {{ $ticket->user?->name ?? 'Unknown' }} on {{ $ticket->created_at->format('d M Y, H:i') }}
                        @if ($ticket->category) · {{ ucfirst($ticket->category) }}@endif
                    </p>
                </div>
                @if (can_manage('support.reply'))
                    <form method="POST" action="{{ route('support.status', $ticket) }}" data-ajax data-refresh="[data-ajax-table='ticket-header']" class="flex items-end gap-2">
                        @csrf
                        <x-select name="status" label="Status">
                            @foreach (['open' => 'Open', 'in_progress' => 'In progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
                                <option value="{{ $value }}" @selected($ticket->status === $value)>{{ $label }}</option>
                            @endforeach
                        </x-select>
                        <x-button type="submit" size="sm">Update</x-button>
                    </form>
                @endif
            </div>
        </x-card>
        </div>

        <div id="ticket-thread" data-ajax-table="ticket-thread" class="space-y-4">
            @foreach ($ticket->messages as $message)
                <div class="flex items-start gap-3">
                    <x-avatar :user="$message->user" size="size-9" />
                    <div class="card max-w-2xl flex-1">
                        <div class="card-body">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-bold text-ink-900 dark:text-white">{{ $message->user?->name }}</p>
                                <span class="text-xs text-ink-400">{{ $message->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <p class="mt-1.5 whitespace-pre-line text-sm text-ink-600 dark:text-ink-300">{{ $message->message }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if (can_manage('support.reply'))
            <x-card title="Reply">
                <form method="POST" action="{{ route('support.reply', $ticket) }}" data-ajax data-ajax-reset data-refresh="[data-ajax-table='ticket-thread']" class="space-y-4">
                    @csrf
                    <x-field label="Message" name="message">
                        <textarea name="message" rows="4" class="input" required>{{ old('message') }}</textarea>
                    </x-field>
                    <div class="flex justify-end">
                        <x-button type="submit">
                            <x-icon name="chat" class="size-4" />
                            Send Reply
                        </x-button>
                    </div>
                </form>
            </x-card>
        @endif
    </div>
</x-layouts.app>
