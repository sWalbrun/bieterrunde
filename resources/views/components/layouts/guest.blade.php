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
    <main class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-4 py-10">
        <div class="mb-8 flex justify-center">
            <img src="{{ asset('logo-solawi.svg') }}" alt="{{ config('app.name') }}" class="h-16">
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 sm:p-8">
            {{ $slot }}
        </div>
    </main>
</body>
</html>
