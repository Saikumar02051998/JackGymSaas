@php
    $message = $exception?->getMessage() ?: 'The page you are looking for does not exist or may have been moved.';
@endphp

<x-error code="404" title="Page Not Found" :message="$message" />
