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
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Notification</th>
                            <th class="px-5 py-3 font-semibold">Type</th>
                            <th class="px-5 py-3 font-semibold">Received</th>
                            <th class="px-5 py-3 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($notifications as $notification)
                            @php
                                $data = $notification->data;
                                $unread = is_null($notification->read_at);
                                $type = $data['type'] ?? 'info';
                                $icon = $type === 'success' ? 'check-badge' : ($type === 'warning' ? 'clock' : 'bell');
                                $color = $type === 'success' ? 'green' : ($type === 'warning' ? 'amber' : 'blue');
                            @endphp
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50 {{ $unread ? 'bg-gold-400/5' : '' }}">
                                <td class="px-5 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-ink-100 text-ink-400 dark:bg-ink-800 dark:text-ink-500">
                                            <x-icon :name="$icon" class="size-5" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-ink-900 dark:text-white">{{ $data['title'] ?? 'Notification' }}</p>
                                            <p class="mt-0.5 max-w-md !whitespace-normal text-xs text-ink-500 dark:text-ink-400">{{ $data['message'] ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <x-badge :color="$color">{{ ucfirst($type) }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">
                                    {{ $notification->created_at->diffForHumans() }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if ($unread)
                                        <form method="POST" action="{{ route('notifications.read', $notification) }}" class="inline-flex" data-ajax>
                                            @csrf
                                            <x-button type="submit" variant="ghost" size="sm">
                                                <x-icon name="check" class="size-3.5" />
                                                Mark read
                                            </x-button>
                                        </form>
                                    @else
                                        <span class="text-xs text-ink-400">Read</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
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
