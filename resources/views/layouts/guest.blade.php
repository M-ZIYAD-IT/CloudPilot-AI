<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#090909">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-canvas">
            <div>
                <a href="/" class="flex items-center gap-2 text-ink">
                    <img src="{{ asset('images/cloudpilot-logo.png') }}" alt="{{ config('app.name') }}" class="h-14 w-auto">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-8 card-1 sm:rounded-xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
