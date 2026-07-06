<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-amber-50/40 text-gray-900 antialiased">
    <header class="sticky top-0 z-10 border-b border-gray-950/5 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-14 w-full max-w-xl items-center justify-between px-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('logo-solawi.svg') }}" alt="{{ config('app.name') }}" class="h-8">
            </a>
            <div class="flex items-center gap-4">
                <span class="max-w-40 truncate text-sm text-gray-600">{{ auth()->user()?->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-gray-500 transition hover:text-gray-900">
                        {{ trans('Logout') }}
                    </button>
                </form>
            </div>
        </div>
    </header>
    <main class="mx-auto w-full max-w-xl px-4 py-6">
        {{ $slot }}
    </main>
    <footer class="mx-auto flex w-full max-w-xl justify-center gap-4 px-4 pb-6 text-xs text-gray-400">
        <a href="{{ route('imprint') }}" class="hover:text-gray-600">{{ trans('Imprint') }}</a>
        <a href="{{ route('privacy') }}" class="hover:text-gray-600">{{ trans('Privacy policy') }}</a>
    </footer>
</body>
</html>
