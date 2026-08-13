@props(['title' => 'Modal', 'maxWidth' => 'max-w-lg'])

<div x-data="modal()" @open-modal.window="openModal()" @close-modal.window="closeModal()" x-cloak
     x-show="open"
     @keydown.escape.window="closeModal()"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
     style="display:none">
    <div class="absolute inset-0 bg-ink-950/60 backdrop-blur-sm" @click="closeModal()"></div>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
         @class(['relative w-full rounded-2xl bg-white shadow-2xl dark:bg-night-900', $maxWidth])
         role="dialog" aria-modal="true" aria-label="{{ $title }}">

        <div class="flex items-center justify-between border-b border-ink-100 px-5 py-4 dark:border-ink-800">
            <h3 class="text-sm font-bold text-ink-900 dark:text-white">{{ $title }}</h3>
            <button @click="closeModal()" class="rounded-lg p-1.5 text-ink-400 transition-colors hover:bg-ink-100 dark:hover:bg-ink-800">
                <x-icon name="x" class="size-5" />
            </button>
        </div>

        <div class="max-h-[75vh] overflow-y-auto px-5 py-5">
            {{ $slot }}
        </div>

        @if ($footer ?? null)
            <div class="flex items-center justify-end gap-2 border-t border-ink-100 px-5 py-3.5 dark:border-ink-800">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
