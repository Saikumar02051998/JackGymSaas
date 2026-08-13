@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-wrap items-center justify-center gap-2">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="flex size-9 items-center justify-center rounded-full border border-ink-200 text-ink-300 dark:border-ink-800 dark:text-ink-600">
                <svg class="size-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}" class="flex size-9 items-center justify-center rounded-full border border-ink-200 text-ink-600 transition-colors hover:border-gold-500 hover:text-gold-600 dark:border-ink-700 dark:text-ink-300 dark:hover:border-gold-500 dark:hover:text-gold-400">
                <svg class="size-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span aria-disabled="true" class="flex size-9 items-center justify-center text-sm font-medium text-ink-400">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" aria-label="{{ __('Page :page', ['page' => $page]) }}" class="flex size-9 items-center justify-center rounded-full bg-gold-500 text-sm font-bold text-white shadow-sm shadow-gold-500/40">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="flex size-9 items-center justify-center rounded-full border border-ink-200 text-sm font-semibold text-ink-600 transition-colors hover:border-gold-500 hover:bg-gold-400/10 hover:text-gold-700 dark:border-ink-700 dark:text-ink-300 dark:hover:border-gold-500 dark:hover:text-gold-400">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}" class="flex size-9 items-center justify-center rounded-full border border-ink-200 text-ink-600 transition-colors hover:border-gold-500 hover:text-gold-600 dark:border-ink-700 dark:text-ink-300 dark:hover:border-gold-500 dark:hover:text-gold-400">
                <svg class="size-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
            </a>
        @else
            <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="flex size-9 items-center justify-center rounded-full border border-ink-200 text-ink-300 dark:border-ink-800 dark:text-ink-600">
                <svg class="size-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
            </span>
        @endif
    </nav>
@endif
