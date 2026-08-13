<x-layouts.app
    title="Notifications"
    description="Your recent notifications."
    :breadcrumbs="[['label' => 'Notifications']]">

    <x-card title="Notifications" :padding="false">
        <div data-ajax-table="notifications-table">
        @if ($notifications->isEmpty())
            <div class="p-8">
                <x-empty-state icon="bell" title="No notifications" message="You're all caught up." />
            </div>
        @else
            <div class="divide-y divide-ink-100 dark:divide-ink-800">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $unread = is_null($notification->read_at);
                        $type = $data['type'] ?? 'info';
                        $icon = $type === 'success' ? 'check-badge' : ($type === 'warning' ? 'clock' : 'bell');
                        $color = $type === 'success' ? 'green' : ($type === 'warning' ? 'amber' : 'blue');
                    @endphp
                    <div class="flex items-start gap-4 px-5 py-4 {{ $unread ? 'bg-gold-400/5' : '' }}">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-ink-100 text-ink-400 dark:bg-ink-800 dark:text-ink-500">
                            <x-icon :name="$icon" class="size-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-bold text-ink-900 dark:text-white">{{ $data['title'] ?? 'Notification' }}</p>
                                @if ($unread)
                                    <span class="size-2 rounded-full bg-gold-500"></span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-sm text-ink-500 dark:text-ink-400">{{ $data['message'] ?? '' }}</p>
                            <p class="mt-1 text-xs text-ink-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        @if ($unread)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}" class="shrink-0" data-ajax>
                                @csrf
                                <x-button type="submit" variant="ghost" size="sm">
                                    <x-icon name="check" class="size-3.5" />
                                    Mark read
                                </x-button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="px-5 py-4">
                <x-pagination :model="$notifications" />
            </div>
        @endif
        </div>
    </x-card>

    @if (! $notifications->isEmpty() && auth()->user()->unreadNotifications->isNotEmpty())
        <div class="mt-4 flex justify-end">
            <form method="POST" action="{{ route('notifications.read-all') }}" data-ajax data-refresh="[data-ajax-table='notifications-table']">
                @csrf
                <x-button type="submit" variant="outline" size="sm">
                    <x-icon name="check-badge" class="size-4" />
                    Mark all as read
                </x-button>
            </form>
        </div>
    @endif
</x-layouts.app>
