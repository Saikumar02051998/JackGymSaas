<x-layouts.app
    title="{{ $gym->name }}"
    description="Subscription and account details for this gym."
    :breadcrumbs="[['label' => 'SaaS', 'url' => route('saas.dashboard')], ['label' => 'Gyms', 'url' => route('saas.gyms.index')], ['label' => $gym->name]]">

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card title="Gym">
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-ink-400">Status</span>
                    <x-badge :color="match($gym->subscription_status) { 'active' => 'green', 'trial' => 'blue', 'expired' => 'red', 'suspended' => 'purple', default => 'gray' }">{{ $gym->subscriptionStatusLabel() }}</x-badge>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-ink-400">Email</span>
                    <span class="font-medium text-ink-900 dark:text-white">{{ $gym->email ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-ink-400">Phone</span>
                    <span class="font-medium text-ink-900 dark:text-white">{{ $gym->phone ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-ink-400">Slug</span>
                    <span class="font-medium text-ink-900 dark:text-white">{{ $gym->slug }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-ink-400">Members</span>
                    <span class="font-medium text-ink-900 dark:text-white">{{ $gym->clients()->count() }}</span>
                </div>
            </div>
        </x-card>

        <x-card title="Subscription">
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-ink-400">Plan</span>
                    <span class="font-medium text-ink-900 dark:text-white">{{ $gym->subscriptionPlan?->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-ink-400">Billing cycle</span>
                    <span class="font-medium text-ink-900 dark:text-white">{{ $gym->subscription_billing_cycle ? ucfirst($gym->subscription_billing_cycle) : '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-ink-400">Expires</span>
                    <span class="font-medium text-ink-900 dark:text-white">{{ $gym->subscription_expires_at ? $gym->subscription_expires_at->format('d M Y') : '—' }}</span>
                </div>

                @if (auth()->user()->hasPermission('saas.gyms.manage'))
                    <form method="POST" action="{{ route('saas.gyms.status', $gym) }}" class="border-t border-ink-100 pt-4 dark:border-ink-800">
                        @csrf
                        <label class="mb-1 block text-xs font-semibold text-ink-900 dark:text-white">Change status</label>
                        <div class="flex gap-2">
                            <select name="subscription_status" class="input flex-1">
                                @foreach (['active' => 'Active', 'trial' => 'Trial', 'expired' => 'Expired', 'suspended' => 'Suspended'] as $value => $label)
                                    <option value="{{ $value }}" {{ $gym->subscription_status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-button type="submit" size="sm">Save</x-button>
                        </div>
                    </form>
                @endif
            </div>
        </x-card>

        <x-card title="Users">
            <ul class="divide-y divide-ink-100 dark:divide-ink-800">
                @forelse ($gym->users->take(8) as $user)
                    <li class="flex items-center justify-between gap-2 py-2.5 text-sm">
                        <div class="min-w-0">
                            <p class="font-medium text-ink-900 dark:text-white">{{ $user->name }}</p>
                            <p class="truncate text-xs text-ink-400">{{ $user->email ?? $user->phone }}</p>
                        </div>
                        <x-badge color="gray">{{ $user->roles->first()?->name ?? '—' }}</x-badge>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-ink-400">No users.</li>
                @endforelse
            </ul>
        </x-card>
    </div>

    <x-card :padding="false" class="mt-6">
        <div class="border-b border-ink-100 p-4 dark:border-ink-800">
            <h3 class="font-bold text-ink-900 dark:text-white">Subscription Payments</h3>
        </div>
        @if ($payments->isEmpty())
            <div class="p-8">
                <x-empty-state icon="banknotes" title="No payments yet" message="Recorded subscription payments will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Plan</th>
                            <th class="px-5 py-3 font-semibold">Cycle</th>
                            <th class="px-5 py-3 font-semibold">Period</th>
                            <th class="px-5 py-3 font-semibold">Method</th>
                            <th class="px-5 py-3 font-semibold">Amount</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($payments as $payment)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4 font-semibold text-ink-900 dark:text-white">{{ $payment->subscriptionPlan?->name ?? '—' }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ ucfirst($payment->billing_cycle) }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $payment->period_start->format('d M Y') }} – {{ $payment->period_end->format('d M Y') }}</td>
                                <td class="px-5 py-4"><x-badge color="gray">{{ ucfirst($payment->payment_method) }}</x-badge></td>
                                <td class="px-5 py-4 font-bold text-ink-900 dark:text-white">{{ money($payment->amount) }}</td>
                                <td class="px-5 py-4">
                                    <x-badge :color="match($payment->status) { 'paid' => 'green', 'pending' => 'amber', 'failed' => 'red', 'refunded' => 'purple', default => 'gray' }">{{ ucfirst($payment->status) }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $payment->created_at->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$payments" />
            </div>
        @endif
    </x-card>
</x-layouts.app>
