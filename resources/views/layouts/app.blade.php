<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RSU Reciclaje | USAT')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased h-screen flex overflow-hidden text-slate-800">

    {{-- SIDEBAR (estático) --}}
    @include('layouts.navigation')

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- TOPBAR (estilo pro + flyon) --}}
        <header class="h-16 w-full border-b border-slate-200 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white flex items-center justify-between px-6">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-recycle text-2xl drop-shadow-sm"></i>
                <h2 class="text-lg font-semibold tracking-wide">@yield('title', 'Panel RSU')</h2>
            </div>

            <div class="flex items-center gap-3">
                <a href="https://www.youtube.com/playlist?list=PLTwle3OwQTDvNIH9Z0CDM_dVF4ftteIwW" target="_blank"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white/15 hover:bg-white/25 transition"
                   title="Tutoriales">
                    <i class="fa-solid fa-circle-question"></i>
                </a>

                <span class="hidden sm:inline-block font-medium bg-white/15 px-3 py-1 rounded-xl">Invitado</span>

                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/15 hover:bg-white/25 transition">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span class="hidden sm:inline">Salir</span>
                    </button>
                </form>
            </div>
        </header>

        {{-- CONTENIDO --}}
        <main class="flex-1 overflow-y-auto p-8 bg-slate-50">
            {{-- contenedor con feeling FlyonUI (cards limpias y espaciamiento) --}}
            <div class="mx-auto max-w-[1400px]">
                @yield('content')
            </div>
        </main>

        {{-- FOOTER --}}
        <footer class="text-center text-sm py-4 text-slate-500 border-t border-slate-200 bg-white">
            © {{ date('Y') }} Proyecto RSU Reciclaje — Escuela de Ingeniería de Sistemas y Computación — USAT 🌱
        </footer>
    </div>
</body>
</html>
