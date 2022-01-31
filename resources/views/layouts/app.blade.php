<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SolaWi') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">

        @livewireStyles
        @wireUiScripts
        @powerGridScripts
        <script src="//unpkg.com/alpinejs" defer></script>
        <script src="//unpkg.com/@themesberg/flowbite@1.3.0/dist/flowbite.bundle.js"></script>
    </head>

    <body>
        <div x-data="{ sidebarIsOpened: false }" class="min-h-full">
            <!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
            <div x-show="sidebarIsOpened" class="fixed inset-0 flex z-40 lg:hidden" role="dialog" aria-modal="true">
                @livewire('navigation-menu')
                <div class="content-wrapper">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @livewireScripts

    </body>
</html>
