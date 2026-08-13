@php
    $message = 'We are performing scheduled maintenance. Please check back shortly.';
@endphp

<x-error code="503" title="Service Unavailable" :message="$message" />
