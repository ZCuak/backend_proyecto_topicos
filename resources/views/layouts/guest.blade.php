<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-green-50 dark:bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RSU Reciclaje | USAT')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased h-full flex flex-col items-center justify-center text-gray-800 dark:text-gray-100">
    <main class="flex-1 flex items-center justify-center w-full">
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>
