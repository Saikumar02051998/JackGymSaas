<x-layouts.app
    title="{{ $ticket->subject }}"
    description="Support ticket conversation"
    :breadcrumbs="[['label' => 'Support', 'url' => route('client.support')], ['label' => $ticket->subject]]">

    <x-slot name="actions">
        <x-button href="{{ route('client.support') }}" variant="ghost" size="sm">
            <x-icon name="arrow-left" class="size-4" />
            Back to Tickets
        </x-button>
    </x-slot>

    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-ink-900 dark:text-white">{{ $ticket->subject }}</h2>
                <p class="mt-0.5 text-xs text-ink-400">
                    Opened {{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y, h:i A') }}
                    @if ($ticket->category)
                        · {{ ucfirst($ticket->category) }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-badge :color="match($ticket->priority) { 'low' => 'gray', 'medium' => 'blue', 'high' => 'amber', 'urgent' => 'red', default => 'gray' }">Priority: {{ ucfirst($ticket->priority) }}</x-badge>
                <x-badge :color="match($ticket->status) { 'open' => 'green', 'in_progress' => 'blue', 'resolved' => 'amber', 'closed' => 'gray', default => 'gray' }">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</x-badge>
            </div>
        </div>
    </x-card>

    <x-card class="mt-6">
        <div class="space-y-6">
            @foreach ($ticket->messages as $message)
                @php
                    $mine = $message->user_id === auth()->id();
                @endphp
                <div class="flex gap-3">
                    <x-avatar :user="$message->user" size="size-9 text-xs" />
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-bold text-ink-900 dark:text-white">{{ $message->user?->name ?? 'Unknown' }}</p>
                            <span class="text-xs text-ink-400">{{ \Carbon\Carbon::parse($message->created_at)->format('d M Y, h:i A') }}</span>
                            @if ($mine)
                                <x-badge color="blue">You</x-badge>
                            @endif
                        </div>
                        <div class="mt-1.5 rounded-2xl rounded-tl-sm bg-ink-100/70 px-4 py-3 text-sm text-ink-700 dark:bg-ink-800/70 dark:text-ink-200">
                            {{ $message->message }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <form action="{{ route('client.support.reply', $ticket) }}" method="POST" class="mt-8 border-t border-ink-100 pt-5 dark:border-ink-800">
            @csrf
            <x-field label="Reply" name="message">
                <textarea name="message" rows="4" class="input resize-none" placeholder="Type your reply..." required>{{ old('message') }}</textarea>
            </x-field>
            <div class="mt-3 flex justify-end">
                <x-button type="submit" variant="primary">
                    <x-icon name="chat" class="size-4" />
                    Send Reply
                </x-button>
            </div>
        </form>
    </x-card>
</x-layouts.app>
