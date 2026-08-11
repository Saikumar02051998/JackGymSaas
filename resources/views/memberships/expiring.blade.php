<x-layouts.app
    title="Expiring Soon"
    description="Memberships ending within the next 30 days. Reach out to renew."
    :breadcrumbs="[['label' => 'Memberships', 'url' => route('memberships.index')], ['label' => 'Expiring Soon']]">

    @if ($memberships->isEmpty())
        <x-card>
            <div class="p-8">
                <x-empty-state icon="check-badge" title="Nothing expiring soon" message="All active memberships are safe for the next 30 days." />
            </div>
        </x-card>
    @else
        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Member</th>
                            <th class="px-5 py-3 font-semibold">Plan</th>
                            <th class="px-5 py-3 font-semibold">Expiry</th>
                            <th class="px-5 py-3 font-semibold">Days Left</th>
                            <th class="px-5 py-3 font-semibold">Amount</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($memberships as $membership)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4">
                                    <a href="{{ route('clients.show', $membership->client_id) }}" class="font-semibold text-ink-900 hover:text-gold-600 dark:text-white">{{ $membership->client?->display_name }}</a>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $membership->plan?->name ?? '—' }}</td>
                                <td class="px-5 py-4 font-semibold text-ink-900 dark:text-white">{{ \Carbon\Carbon::parse($membership->end_date)->format('d M Y') }}</td>
                                <td class="px-5 py-4">
                                    <span class="{{ $membership->days_remaining <= 7 ? 'font-bold text-red-500' : 'font-bold text-amber-500' }}">{{ $membership->days_remaining }} days</span>
                                </td>
                                <td class="px-5 py-4 font-semibold text-ink-900 dark:text-white">{{ money($membership->final_amount) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('clients.show', $membership->client_id) }}" class="btn-outline btn-sm">View Client</a>
                                        @if (can_manage('memberships.renew'))
                                            <a href="{{ route('memberships.create', ['client' => $membership->client_id]) }}" class="btn-primary btn-sm">Renew</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$memberships" />
            </div>
        </x-card>
    @endif
</x-layouts.app>
