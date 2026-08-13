<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Welcome' }} | {{ saas_owner_name() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-ink-50 dark:bg-night-950">
    <x-toast-container />
    {{ $slot }}

    @php
        $flash = session('success') ? ['type' => 'success', 'message' => session('success')]
            : (session('error') ? ['type' => 'error', 'message' => session('error')]
            : (session('warning') ? ['type' => 'warning', 'message' => session('warning')]
            : (session('status') ? ['type' => 'info', 'message' => session('status')] : null)));
    @endphp
    @if ($flash)
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => window.toast?.({!! json_encode($flash['message']) !!}, {!! json_encode($flash['type']) !!}), 60);
            });
        </script>
    @endif
</body>
</html>
