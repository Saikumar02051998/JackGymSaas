<div x-data class="pointer-events-none fixed inset-x-0 top-4 z-[100] flex flex-col items-center gap-2 px-4 sm:items-end sm:pr-6">
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border bg-white p-4 shadow-xl animate-slide-in-right dark:bg-night-900"
             :class="{
                 'border-emerald-200 dark:border-emerald-500/30': toast.type === 'success',
                 'border-red-200 dark:border-red-500/30': toast.type === 'error',
                 'border-amber-200 dark:border-amber-500/30': toast.type === 'warning',
                 'border-ink-200 dark:border-ink-700': toast.type === 'info',
             }">
            <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full text-white"
                  :class="{
                      'bg-emerald-500': toast.type === 'success',
                      'bg-red-500': toast.type === 'error',
                      'bg-amber-500': toast.type === 'warning',
                      'bg-blue-500': toast.type === 'info',
                  }">
                <x-icon name="check" class="size-3.5" x-show="toast.type === 'success'" />
                <x-icon name="x" class="size-3.5" x-show="toast.type === 'error'" />
                <x-icon name="clock" class="size-3.5" x-show="toast.type === 'warning'" />
                <x-icon name="sparkles" class="size-3.5" x-show="toast.type === 'info'" />
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink-900 dark:text-white" x-text="toast.title || 'Notification'"></p>
                <p class="mt-0.5 text-sm text-ink-500 dark:text-ink-400" x-text="toast.message"></p>
            </div>
            <button @click="$store.toasts.dismiss(toast.id)" class="shrink-0 rounded-lg p-1 text-ink-400 hover:bg-ink-100 dark:hover:bg-ink-800">
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    </template>
</div>
