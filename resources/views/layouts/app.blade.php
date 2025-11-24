<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RSU Reciclaje | USAT')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
</head>

@php($user = Auth::user())

<body
    class="antialiased min-h-screen flex overflow-hidden text-slate-800 bg-gradient-to-br from-slate-50 via-white to-emerald-50/40"
    data-success="{{ session('success') }}" data-error="{{ session('error') }}">

    <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden">
        <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-emerald-400/25 blur-[120px]"></div>
        <div class="absolute right-[-120px] top-10 h-80 w-80 rounded-full bg-teal-500/15 blur-[160px]"></div>
        <div class="absolute bottom-[-140px] left-1/3 h-96 w-96 rounded-full bg-sky-400/10 blur-[170px]"></div>
    </div>

    @include('layouts.navigation')

    <div class="relative z-10 flex-1 flex flex-col overflow-hidden min-h-0 h-screen px-4 lg:px-8 py-6">
        <header
            class="sticky top-0 z-30 mb-6 h-20 w-full rounded-3xl border border-white/60 bg-white/90 backdrop-blur-xl shadow-[0_12px_60px_rgba(15,23,42,0.08)] flex items-center justify-between px-5 lg:px-8">
            <div class="flex items-center gap-4">
                <button id="navEnergyToggle" type="button" aria-label="Alternar navegación"
                    class="group relative flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-400/40 transition">
                    <span class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(255,255,255,0.14),transparent_50%)] opacity-0 transition duration-300 group-hover:opacity-80"></span>
                    <span class="absolute inset-0 blur-xl bg-emerald-400/50 opacity-0 transition duration-500 group-hover:opacity-80"></span>
                    <i class="fa-solid fa-recycle text-xl relative z-10 transition duration-500 group-hover:scale-110"></i>
                </button>
                <div class="leading-tight">
                    <p class="text-[11px] uppercase tracking-[0.28em] text-emerald-600 font-semibold">Operaciones RSU
                    </p>
                    <p class="text-sm font-semibold text-slate-900">Panel ejecutivo</p>

                </div>
            </div>

            <div class="flex items-center gap-2 lg:gap-3">
                <a href="{{ url('/') }}" title="Dashboard"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/50 bg-white/70 text-emerald-700 shadow-sm hover:-translate-y-0.5 hover:shadow-md hover:text-emerald-800 transition">
                    <i class="fa-solid fa-chart-line"></i>
                </a>
                <a href="{{ route('vehicles.index') }}" title="Gestion de Vehiculos"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/50 bg-white/70 text-emerald-700 shadow-sm hover:-translate-y-0.5 hover:shadow-md hover:text-emerald-800 transition">
                    <i class="fas fa-car"></i>
                </a>
                <a href="{{ route('personal.index') }}" title="Gestion de Personal"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/50 bg-white/70 text-emerald-700 shadow-sm hover:-translate-y-0.5 hover:shadow-md hover:text-emerald-800 transition">
                    <i class="fas fa-users"></i>
                </a>
                <a href="{{ route('schedulings.index') }}" title="Programacion"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/50 bg-white/70 text-emerald-700 shadow-sm hover:-translate-y-0.5 hover:shadow-md hover:text-emerald-800 transition">
                    <i class="fas fa-calendar-check"></i>
                </a>
                <a href="{{ route('history.index') }}" title="Gestion de cambios"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/50 bg-white/70 text-emerald-700 shadow-sm hover:-translate-y-0.5 hover:shadow-md hover:text-emerald-800 transition">
                    <i class="fas fa-retweet"></i>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" title="Cerrar sesion"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm hover:-translate-y-0.5 hover:shadow-lg hover:border-emerald-200 hover:text-emerald-700 transition">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </header>

        <main id="mainContent" class="flex-1 overflow-y-auto pb-8 min-h-0" data-turbo-frame="mainContent">

            <section
                class="rounded-3xl border border-white/70 bg-white/95 backdrop-blur-xl p-6 lg:p-8 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                @yield('content')
            </section>

        </main>

        <footer class="mt-6 text-center text-sm text-slate-500">
            &copy; {{ date('Y') }} Proyecto RSU Reciclaje - Escuela de Ingenieria de Sistemas y Computacion - USAT
        </footer>
    </div>

    <script>
        document.addEventListener("turbo:visit", () => {
            document.getElementById("page-loader")?.classList.remove("hidden");
        });
        document.addEventListener("turbo:load", () => {
            document.getElementById("page-loader")?.classList.add("hidden");
        });
    </script>

    <turbo-frame id="modal-frame"></turbo-frame>
    <div id="flyonui-modal-container"></div>

    <div id="page-loader"
        class="fixed inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center hidden z-[9999]">

        <div class="loader relative w-[150px] h-[150px]">
            <!-- Spinner principal -->
            <div class="spinner"></div>

            <!-- Imagen central (tu logo o ícono) -->
            <img src="{{ asset('img/logo_rsu_sin_fondo.png') }}" class="img-loading select-none">
        </div>
    </div>
    <!-- === CSS interno o en tu archivo app.css === -->
   
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
