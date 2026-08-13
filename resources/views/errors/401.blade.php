@php
    $message = config('app.debug') && $exception?->getMessage()
        ? $exception->getMessage()
        : 'You are not authorized to view this page. Please sign in and try again.';
@endphp

<x-error code="401" title="Unauthorized" :message="$message" />
