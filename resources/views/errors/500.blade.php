@php
    $message = config('app.debug') && $exception?->getMessage()
        ? $exception->getMessage()
        : 'Something went wrong on our end. Please try again later.';
@endphp

<x-error code="500" title="Server Error" :message="$message" />
