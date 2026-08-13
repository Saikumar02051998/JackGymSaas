@php
    $message = config('app.debug') && $exception?->getMessage()
        ? $exception->getMessage()
        : 'You do not have permission to access this page. Contact your administrator if you think this is a mistake.';
@endphp

<x-error code="403" title="Access Denied" :message="$message" />
