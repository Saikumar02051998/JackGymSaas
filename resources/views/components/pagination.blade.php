@props(['model' => null, 'class' => ''])

@php
$pagination = $model ?? $slot;
@endphp

<div class="mt-6 flex flex-col items-center justify-between gap-3 sm:flex-row">
    <p class="text-xs text-ink-400">
        Showing {{ $pagination->firstItem() ?? 0 }} to {{ $pagination->lastItem() ?? 0 }} of {{ $pagination->total() }} entries
    </p>
    {{ $pagination->links() }}
</div>
