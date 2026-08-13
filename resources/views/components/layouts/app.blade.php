@props(['title' => '', 'description' => '', 'breadcrumbs' => []])

@php
use App\Support\Menu;
$menu = Menu::items();
$user = auth()->user();
$gym = current_gym();
$currentRoute = request()->route()?->getName() ?? '';
$unreadCount = auth()->user()->unreadNotifications()->count();
$notifications = auth()->user()->notifications()->latest()->limit(8)->get();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' | ' : '' }}{{ $gym?->name ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body x-data="{ sidebarOpen: false, mobileOpen: false, profileOpen: false }" x-init="$store.theme.init()">

    <div id="page-loader" aria-hidden="true">
        <div class="loader-dots" role="status" aria-label="Loading">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <x-toast-container />

    <div class="min-h-screen lg:flex">
        @if ($user->isClient())
            <x-app.mobile-header :unread-count="$unreadCount" />
            <x-app.sidebar-mobile :menu="$menu" />
            <x-app.sidebar :menu="$menu" />
        @else
            <x-app.mobile-header :unread-count="$unreadCount" />
            <x-app.sidebar-mobile :menu="$menu" />
            <x-app.sidebar :menu="$menu" />
        @endif

        <div class="flex min-h-screen flex-1 flex-col lg:pl-72">
            <x-app.topbar :unread-count="$unreadCount" :notifications="$notifications" />

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @if ($breadcrumbs)
                    <nav class="mb-4 flex items-center gap-1.5 text-xs text-ink-400">
                        <a href="{{ $user->homeRoute() }}" class="transition-colors hover:text-gold-600">Home</a>
                        @foreach ($breadcrumbs as $crumb)
                            <span>/</span>
                            @if (isset($crumb['url']))
                                <a href="{{ $crumb['url'] }}" class="transition-colors hover:text-gold-600">{{ $crumb['label'] }}</a>
                            @else
                                <span class="font-medium text-ink-600 dark:text-ink-300">{{ $crumb['label'] }}</span>
                            @endif
                        @endforeach
                    </nav>
                @endif

                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="page-title">{{ $title }}</h1>
                        @if ($description)
                            <p class="page-desc">{{ $description }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        {{ $actions ?? '' }}
                    </div>
                </div>

                @if (session('status'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 dark:border-red-500/30 dark:bg-red-500/10">
                        <p class="mb-1 text-sm font-semibold text-red-700 dark:text-red-400">Please fix the following errors:</p>
                        <ul class="list-inside list-disc space-y-0.5 text-sm text-red-600 dark:text-red-400">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <footer class="border-t border-ink-200 px-6 py-4 text-center text-xs text-ink-400 dark:border-ink-800">
                &copy; {{ date('Y') }} {{ saas_owner_name() }}. All rights reserved. &middot; Premium Gym Management
            </footer>
        </div>
    </div>

    <x-confirm-dialog />

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme').init();
        });
    </script>

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

    @stack('scripts')
</body>
</html>
