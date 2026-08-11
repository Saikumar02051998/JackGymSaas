@props([
    'icon' => 'box',
    'title' => 'Nothing here yet',
    'message' => null,
    'action' => null,
    'actionLabel' => 'Add one',
])

<div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-ink-300 bg-white px-6 py-14 text-center dark:border-ink-700 dark:bg-night-900">
    <div class="flex size-14 items-center justify-center rounded-2xl bg-ink-100 text-ink-400 dark:bg-ink-800 dark:text-ink-500">
        <x-icon :name="$icon" class="size-7" />
    </div>
    <h3 class="mt-4 text-sm font-bold text-ink-900 dark:text-white">{{ $title }}</h3>
    @if ($message)
        <p class="mt-1 max-w-sm text-sm text-ink-500 dark:text-ink-400">{{ $message }}</p>
    @endif
    @if ($action)
        <div class="mt-5">
            <a href="{{ $action }}" class="btn-primary">
                <x-icon name="plus" class="size-4" />
                {{ $actionLabel }}
            </a>
        </div>
    @endif
</div>
