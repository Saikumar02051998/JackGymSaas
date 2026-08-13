@php
    $message = config('app.debug') && $exception?->getMessage()
        ? $exception->getMessage()
        : 'Your session has expired. Please refresh the page and try again.';
@endphp

<x-error code="419" title="Session Expired" :message="$message" />
