@props([
    'code' => '500',
    'title' => 'Something went wrong',
    'message' => 'An unexpected error occurred. Please try again later.',
    'homeUrl' => '/',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} | {{ config('app.name', 'Gym Management') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-ink-50 px-4 py-12 dark:bg-night-950">
    <div class="relative w-full max-w-lg text-center">
        <div class="absolute -top-16 left-1/2 size-64 -translate-x-1/2 rounded-full bg-gold-400/15 blur-3xl"></div>

        <div class="relative">
            <p class="bg-gradient-to-br from-gold-300 to-gold-500 bg-clip-text text-[120px] font-extrabold leading-none tracking-tight text-transparent drop-shadow-sm">
                {{ $code }}
            </p>

            <h1 class="mt-4 text-2xl font-bold tracking-tight text-ink-900 dark:text-white">{{ $title }}</h1>
            <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-ink-500 dark:text-ink-400">{{ $message }}</p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ $homeUrl }}" class="btn-primary">
                    <x-icon name="building" class="size-4" />
                    Back to Home
                </a>
                <button type="button" onclick="history.back()" class="btn-outline">
                    <x-icon name="arrow-left" class="size-4" />
                    Go Back
                </button>
            </div>
        </div>
    </div>
</body>
</html>
