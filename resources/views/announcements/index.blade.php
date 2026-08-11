<x-layouts.app
    title="Announcements"
    description="Share updates with staff and clients."
    :breadcrumbs="[['label' => 'Announcements']]">

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($announcements->isEmpty())
                <x-card>
                    <div class="p-8">
                        <x-empty-state icon="megaphone" title="No announcements yet" message="Publish an announcement to notify staff and clients." />
                    </div>
                </x-card>
            @else
                <div class="space-y-4">
                    @foreach ($announcements as $announcement)
                        <x-card>
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gold-400/15 text-gold-600 dark:text-gold-400">
                                        <x-icon name="megaphone" class="size-5" />
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-sm font-bold text-ink-900 dark:text-white">{{ $announcement->title }}</h3>
                                            <x-badge :color="match($announcement->status) { 'published' => 'green', 'draft' => 'gray', default => 'gray' }">{{ ucfirst($announcement->status) }}</x-badge>
                                            <x-badge color="blue">{{ ucfirst($announcement->audience) }}</x-badge>
                                        </div>
                                        <p class="mt-1.5 text-sm text-ink-500 dark:text-ink-400">{{ $announcement->message }}</p>
                                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink-400">
                                            @if ($announcement->creator)
                                                <span>By {{ $announcement->creator->name }}</span>
                                            @endif
                                            <span>{{ $announcement->created_at->format('d M Y, H:i') }}</span>
                                            @if ($announcement->start_date || $announcement->end_date)
                                                <span>{{ $announcement->start_date ? \Carbon\Carbon::parse($announcement->start_date)->format('d M') : '' }}{{ $announcement->end_date ? ' → ' . \Carbon\Carbon::parse($announcement->end_date)->format('d M Y') : '' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if (can_manage('announcements.manage'))
                                    <div class="flex shrink-0 gap-2">
                                        <details class="group relative">
                                            <summary class="btn-outline btn-sm cursor-pointer list-none">
                                                <x-icon name="pencil" class="size-3.5" />
                                                Edit
                                            </summary>
                                            <div class="absolute right-0 top-9 z-20 w-80 rounded-2xl border border-ink-100 bg-white p-5 shadow-xl dark:border-ink-800 dark:bg-night-900">
                                                <p class="mb-3 text-sm font-bold text-ink-900 dark:text-white">Edit announcement</p>
                                                <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="space-y-3">
                                                    @csrf
                                                    @method('PUT')
                                                    <x-input label="Title" name="title" value="{{ old('title', $announcement->title) }}" required />
                                                    <x-field label="Message" name="message">
                                                        <textarea name="message" rows="3" class="input">{{ old('message', $announcement->message) }}</textarea>
                                                    </x-field>
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <x-select label="Audience" name="audience">
                                                            @foreach (['all' => 'Everyone', 'staff' => 'Staff only', 'coaches' => 'Coaches', 'clients' => 'Clients'] as $value => $label)
                                                                <option value="{{ $value }}" @selected(old('audience', $announcement->audience) === $value)>{{ $label }}</option>
                                                            @endforeach
                                                        </x-select>
                                                        <x-select label="Status" name="status">
                                                            @foreach (['published' => 'Published', 'draft' => 'Draft', 'archived' => 'Archived'] as $value => $label)
                                                                <option value="{{ $value }}" @selected(old('status', $announcement->status) === $value)>{{ $label }}</option>
                                                            @endforeach
                                                        </x-select>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <x-input label="Start date" name="start_date" type="date" value="{{ old('start_date', $announcement->start_date) }}" />
                                                        <x-input label="End date" name="end_date" type="date" value="{{ old('end_date', $announcement->end_date) }}" />
                                                    </div>
                                                    <div class="flex gap-2">
                                                        <x-button type="submit" size="sm" class="flex-1">Save</x-button>
                                                        <button type="button" onclick="this.closest('details').removeAttribute('open')" class="btn-outline btn-sm">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </details>
                                        <form method="POST" action="{{ route('announcements.destroy', $announcement) }}"
                                              x-data x-on:submit.prevent="$dispatch('confirm-ask', { action: $el, options: { title: 'Delete announcement?', message: 'Delete the {{ $announcement->title }} announcement.', confirmText: 'Delete' } })">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="ghost" size="sm" class="!text-red-500 hover:!bg-red-50 dark:hover:!bg-red-500/10">
                                                <x-icon name="trash" class="size-4" />
                                            </x-button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </x-card>
                    @endforeach
                </div>
                <div class="mt-6">
                    <x-pagination :model="$announcements" />
                </div>
            @endif
        </div>

        @if (can_manage('announcements.manage'))
            <div>
                <x-card title="New Announcement">
                    <form method="POST" action="{{ route('announcements.store') }}" class="space-y-4">
                        @csrf
                        <x-input label="Title" name="title" value="{{ old('title') }}" required />
                        <x-field label="Message" name="message">
                            <textarea name="message" rows="4" class="input" required>{{ old('message') }}</textarea>
                        </x-field>
                        <div class="grid grid-cols-2 gap-3">
                            <x-select label="Audience" name="audience" required>
                                @foreach (['all' => 'Everyone', 'staff' => 'Staff only', 'coaches' => 'Coaches', 'clients' => 'Clients'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('audience', 'all') === $value)>{{ $label }}</option>
                                @endforeach
                            </x-select>
                            <x-select label="Status" name="status">
                                @foreach (['published' => 'Published', 'draft' => 'Draft'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', 'published') === $value)>{{ $label }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <x-input label="Start date" name="start_date" type="date" value="{{ old('start_date') }}" />
                            <x-input label="End date" name="end_date" type="date" value="{{ old('end_date') }}" />
                        </div>
                        <x-button type="submit" class="w-full">
                            <x-icon name="megaphone" class="size-4" />
                            Publish Announcement
                        </x-button>
                    </form>
                </x-card>
            </div>
        @endif
    </div>
</x-layouts.app>
