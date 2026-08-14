<x-layouts.app
    title="Audit Logs"
    description="Activity history across your gym."
    :breadcrumbs="[['label' => 'Audit Logs']]">

    <x-card title="Audit Logs">
        <form method="GET" action="{{ route('audit.index') }}" class="mb-4 grid gap-3 border-b border-ink-100 pb-4 dark:border-ink-800 sm:grid-cols-2 lg:grid-cols-4">
            <x-select label="Module" name="module">
                <option value="all">All modules</option>
                @foreach ($modules as $module)
                    @if ($module)
                        <option value="{{ $module }}" @selected(request('module') === $module)>{{ ucwords(str_replace(['_', '.'], ' ', $module)) }}</option>
                    @endif
                @endforeach
            </x-select>
            <x-input label="Action" name="action" value="{{ request('action') }}" placeholder="e.g. client.created" />
            <x-input label="From" name="from" type="date" value="{{ request('from') }}" />
            <x-input label="To" name="to" type="date" value="{{ request('to') }}" />
            <div class="flex items-end gap-2">
                <x-button type="submit">
                    <x-icon name="funnel" class="size-4" />
                    Filter
                </x-button>
                @if (request()->hasAny(['module', 'action', 'from', 'to']))
                    <x-button href="{{ route('audit.index') }}" variant="outline" size="sm">Clear</x-button>
                @endif
            </div>
        </form>

        @if ($logs->isEmpty())
            <x-empty-state icon="document-text" title="No activity yet" message="Actions performed in the app will appear here." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Time</th>
                            <th class="px-5 py-3 font-semibold">User</th>
                            <th class="px-5 py-3 font-semibold">Action</th>
                            <th class="px-5 py-3 font-semibold">Module</th>
                            <th class="px-5 py-3 font-semibold">Description</th>
                            <th class="px-5 py-3 font-semibold">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($logs as $log)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="whitespace-nowrap px-5 py-4 text-ink-600 dark:text-ink-300">{{ $log->created_at->format('d M Y, H:i') }}</td>
                                <td class="px-5 py-4">
                                    @if ($log->user)
                                        <div class="flex items-center gap-2.5">
                                            <x-avatar :user="$log->user" size="size-7" />
                                            <span class="font-medium text-ink-900 dark:text-white">{{ $log->user->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-ink-400">System</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <x-badge>{{ $log->action }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $log->module ? ucwords(str_replace(['_', '.'], ' ', $log->module)) : '—' }}</td>
                                <td class="max-w-xs px-5 py-4 !whitespace-normal text-ink-500 dark:text-ink-400">{{ $log->description }}</td>
                                <td class="px-5 py-4 text-ink-400">{{ $log->ip_address }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$logs" />
            </div>
        @endif
    </x-card>
</x-layouts.app>
