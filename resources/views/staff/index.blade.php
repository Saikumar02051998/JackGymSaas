<x-layouts.app
    title="Staff"
    description="Manage your gym's team members."
    :breadcrumbs="[['label' => 'Staff']]">

    <x-slot name="actions">
        @if (can_manage('staff.create'))
            <x-button href="{{ route('staff.create') }}" size="sm">
                <x-icon name="user-plus" class="size-4" />
                Add Staff
            </x-button>
        @endif
    </x-slot>

    <x-card :padding="false">
        <div class="flex flex-wrap items-center gap-3 border-b border-ink-100 p-4 dark:border-ink-800">
            <form method="GET" action="{{ route('staff.index') }}" class="flex flex-1 flex-wrap items-center gap-2">
                <div class="relative min-w-56 flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-ink-400"><x-icon name="search" class="size-4" /></span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, email, employee ID..." class="input pl-9">
                </div>
                <select name="role" class="input w-auto">
                    <option value="all" {{ request('role') === 'all' ? 'selected' : '' }}>All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->slug }}" {{ request('role') === $role->slug ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
                <x-button type="submit">Filter</x-button>
            </form>
        </div>

        @if ($staff->isEmpty())
            <div class="p-8">
                <x-empty-state icon="users" title="No staff found" message="Add your first team member to get started." @if (can_manage('staff.create')) action="{{ route('staff.create') }}" action-label="Add Staff" @endif />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                            <th class="px-5 py-3 font-semibold">Staff</th>
                            <th class="px-5 py-3 font-semibold">Role</th>
                            <th class="px-5 py-3 font-semibold">Designation</th>
                            <th class="px-5 py-3 font-semibold">Employee ID</th>
                            <th class="px-5 py-3 font-semibold">Phone</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($staff as $member)
                            <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="avatar-sm">{{ collect(explode(' ', $member->name))->take(2)->map(fn ($w) => strtoupper($w[0]))->join('') }}</span>
                                        <div>
                                            <p class="font-semibold text-ink-900 dark:text-white">{{ $member->name }}</p>
                                            <p class="text-xs text-ink-400">{{ $member->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <x-badge color="gold">{{ $member->roles->first()?->name ?? '—' }}</x-badge>
                                </td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $member->staffProfile?->designation ?? '—' }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $member->staffProfile?->employee_id ?? '—' }}</td>
                                <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $member->phone ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <x-badge :color="$member->status === 'active' ? 'green' : 'gray'">{{ ucfirst($member->status) }}</x-badge>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('staff.show', $member) }}" class="btn-outline btn-sm">View</a>
                                        @if (can_manage('staff.edit'))
                                            <a href="{{ route('staff.edit', $member) }}" class="btn-outline btn-sm">
                                                <x-icon name="pencil" class="size-3.5" />
                                            </a>
                                            <form method="POST" action="{{ route('staff.toggle-status', $member) }}" class="inline">
                                                @csrf
                                                <x-button type="submit" variant="ghost" size="sm">{{ $member->status === 'active' ? 'Disable' : 'Enable' }}</x-button>
                                            </form>
                                        @endif
                                        @if (can_manage('staff.delete') && $member->id !== auth()->id())
                                            <form method="POST" action="{{ route('staff.destroy', $member) }}"
                                                  x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Remove staff?', message: 'This will permanently remove {{ $member->name }} and their profile.', confirmText: 'Remove' } })">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="ghost" size="sm" class="!text-red-500 hover:!bg-red-50 dark:hover:!bg-red-500/10">
                                                    <x-icon name="trash" class="size-4" />
                                                </x-button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :model="$staff" />
            </div>
        @endif
    </x-card>
</x-layouts.app>
