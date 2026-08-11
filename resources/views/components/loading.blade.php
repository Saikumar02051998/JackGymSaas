@props(['show' => false])

<div x-show="{{ $show ? 'true' : 'false' }}" class="space-y-3">
    @for ($i = 0; $i < 3; $i++)
        <div class="animate-pulse rounded-2xl border border-ink-100 bg-white p-5 dark:border-ink-800 dark:bg-night-900">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-full bg-ink-200 dark:bg-ink-800"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-3 w-1/3 rounded bg-ink-200 dark:bg-ink-800"></div>
                    <div class="h-2.5 w-1/2 rounded bg-ink-100 dark:bg-ink-800"></div>
                </div>
                <div class="h-6 w-16 rounded-full bg-ink-100 dark:bg-ink-800"></div>
            </div>
        </div>
    @endfor
</div>
