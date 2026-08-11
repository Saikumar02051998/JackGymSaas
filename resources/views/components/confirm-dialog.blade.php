<div x-data="confirmDialog()" x-cloak
     x-show="open"
     @keydown.escape.window="cancel()"
     @confirm-ask.window="ask($event.detail.action, $event.detail.options)"
     class="fixed inset-0 z-[90] flex items-center justify-center p-4"
     style="display:none">
    <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         class="absolute inset-0 bg-ink-950/60 backdrop-blur-sm" @click="cancel()"></div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="relative w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl dark:bg-night-900" role="alertdialog">
        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-50 dark:bg-red-500/10">
            <x-icon name="trash" class="size-6 text-red-500" />
        </div>
        <h3 class="mt-4 text-lg font-bold text-ink-900 dark:text-white" x-text="title"></h3>
        <p class="mt-1.5 text-sm text-ink-500 dark:text-ink-400" x-text="message"></p>
        <div class="mt-6 flex justify-center gap-3">
            <button @click="cancel()" class="btn-outline">Cancel</button>
            <button @click="confirm()" :disabled="busy" class="btn-danger min-w-28">
                <svg x-show="busy" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="confirmText"></span>
            </button>
        </div>
    </div>
</div>
