<x-layouts.app
    title="Roles & Permissions"
    description="Manage staff roles and their permissions."
    :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => 'Roles']]">

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-card :padding="false" title="Roles">
                @if ($roles->isEmpty())
                    <div class="p-8">
                        <x-empty-state icon="shield" title="No roles found" message="System roles will appear here." />
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-ink-100 text-xs uppercase tracking-wider text-ink-400 dark:border-ink-800">
                                    <th class="px-5 py-3 font-semibold">Role</th>
                                    <th class="px-5 py-3 font-semibold">Users</th>
                                    <th class="px-5 py-3 font-semibold">Permissions</th>
                                    <th class="px-5 py-3 font-semibold">Type</th>
                                    <th class="px-5 py-3 text-right font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                @foreach ($roles as $role)
                                    <tr class="transition-colors hover:bg-ink-50 dark:hover:bg-ink-800/50">
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-ink-900 dark:text-white">{{ $role->name }}</p>
                                            <p class="text-xs text-ink-400">{{ $role->slug }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $role->users_count }}</td>
                                        <td class="px-5 py-4 text-ink-600 dark:text-ink-300">{{ $role->permissions->count() }}</td>
                                        <td class="px-5 py-4">
                                            <x-badge :color="$role->is_system ? 'gold' : 'gray'">{{ $role->is_system ? 'System' : 'Custom' }}</x-badge>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                @if (! $role->is_system && can_manage('staff.roles'))
                                                    <a href="{{ route('staff.roles.edit', $role) }}" class="btn-outline btn-sm">
                                                        <x-icon name="pencil" class="size-3.5" />
                                                        Edit
                                                    </a>
                                                    <form method="POST" action="{{ route('staff.roles.destroy', $role) }}"
                                                          x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Delete role?', message: 'Delete the {{ $role->name }} role.', confirmText: 'Delete' } })">
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
                        <x-pagination :model="$roles" />
                    </div>
                @endif
            </x-card>
        </div>

        <div>
            <x-card title="{{ isset($editingRole) ? 'Edit Role' : 'Create Role' }}">
                <form method="POST" action="{{ isset($editingRole) ? route('staff.roles.update', $editingRole) : route('staff.roles.store') }}">
                    @csrf
                    @if (isset($editingRole))
                        @method('PUT')
                    @endif

                    <div class="space-y-4">
                        <x-input label="Role name" name="name" value="{{ old('name', $editingRole->name ?? '') }}" required />
                        @if (! isset($editingRole))
                            <x-input label="Slug" name="slug" value="{{ old('slug') }}" required placeholder="e.g. front-desk" help="Used in the URL, e.g. front-desk" />
                        @endif
                        <div>
                            <x-field label="Description" name="description">
                                <textarea name="description" rows="2" class="input">{{ old('description', $editingRole->description ?? '') }}</textarea>
                            </x-field>
                        </div>
                        @error('slug')
                            <p class="text-xs font-medium text-red-500">{{ $message }}</p>
                        @enderror
                        @error('role')
                            <p class="text-xs font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6 border-t border-ink-100 pt-5 dark:border-ink-800">
                        <p class="mb-3 text-sm font-semibold text-ink-900 dark:text-white">Permissions</p>
                        <div class="max-h-80 space-y-4 overflow-y-auto pr-1">
                            @php
                                $grouped = collect($permissions)->groupBy('module');
                                $current = isset($editingRole) ? $editingRole->permissions->pluck('slug')->all() : old('permissions', []);
                            @endphp
                            @foreach ($grouped as $module => $perms)
                                <div>
                                    <p class="mb-2 text-xs font-bold uppercase tracking-wider text-ink-400">{{ $module }}</p>
                                    <div class="space-y-1.5">
                                        @foreach ($perms as $perm)
                                            <label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2 py-1.5 text-sm text-ink-600 hover:bg-ink-50 dark:text-ink-300 dark:hover:bg-ink-800">
                                                <input type="checkbox" name="permissions[]" value="{{ $perm['slug'] }}"
                                                       class="size-4 rounded border-ink-300 text-gold-500 focus:ring-gold-400"
                                                       {{ in_array($perm['slug'], $current) ? 'checked' : '' }}>
                                                {{ $perm['name'] }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <x-button type="submit" class="flex-1">
                            <x-icon name="save" class="size-4" />
                            {{ isset($editingRole) ? 'Save Changes' : 'Create Role' }}
                        </x-button>
                        @if (isset($editingRole))
                            <a href="{{ route('staff.roles.index') }}" class="btn-outline">Cancel</a>
                        @endif
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-layouts.app>
