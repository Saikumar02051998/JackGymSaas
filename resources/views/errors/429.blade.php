@php
    $message = 'You have made too many requests. Please wait a moment and try again.';
@endphp

<x-error code="429" title="Too Many Requests" :message="$message" />
