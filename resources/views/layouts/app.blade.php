<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RSU Reciclaje | USAT')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])


</head>

<body class="font-sans antialiased h-screen flex overflow-hidden text-slate-800" data-success="{{ session('success') }}"
    data-error="{{ session('error') }}">


    {{-- SIDEBAR (estático) --}}
    @include('layouts.navigation')

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- TOPBAR (estilo pro + flyon) --}}
        <header
            class="h-16 w-full border-b border-slate-200 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white flex items-center justify-between px-6">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-recycle text-2xl drop-shadow-sm"></i>
                <h2 class="text-lg font-semibold tracking-wide">@yield('title', 'Panel RSU')</h2>
            </div>

            <div class="flex items-center gap-3">

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
        <main id="mainContent" class="flex-1 overflow-y-auto p-8 bg-slate-50" data-turbo-frame="mainContent">
            <div class="mx-auto max-w-[1400px]">
                @yield('content')
            </div>
        </main>


        {{-- FOOTER --}}
        <footer class="text-center text-sm py-4 text-slate-500 border-t border-slate-200 bg-white">
            © {{ date('Y') }} Proyecto RSU Reciclaje — Escuela de Ingeniería de Sistemas y Computación — USAT 🌱
        </footer>
    </div>




    <script>
        document.addEventListener("turbo:visit", () => {
            document.getElementById("page-loader").classList.remove("hidden");
        });
        document.addEventListener("turbo:load", () => {
            document.getElementById("page-loader").classList.add("hidden");
        });
    </script>

    <!-- Contenedor global para modales FlyonUI -->
    <turbo-frame id="modal-frame"></turbo-frame>
    <div id="flyonui-modal-container"></div>
<!-- LOADER GLOBAL -->
<div id="page-loader"
    class="fixed inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center hidden z-[9999]">

    <div class="loader relative w-[150px] h-[150px]">
        <!-- Spinner principal -->
        <div class="spinner"></div>

        <!-- Imagen central (tu logo o ícono) -->
        <img src="{{ asset('img/logo_rsu_sin_fondo.png') }}" 
             class="img-loading select-none">
    </div>

</div>


<!-- === CSS interno o en tu archivo app.css === -->
<style>
/* Spinner base */
.spinner {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    margin: auto;
    display: inline-block;
    background: linear-gradient(0deg, rgb(16, 185, 129) 33%, #1f2937 100%);
    box-sizing: border-box;
    animation: rotation 1s linear infinite;
}

/* Círculo interno blanco */
.spinner::after {
    content: "";
    box-sizing: border-box;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: #ffffff;
}

/* Animación de rotación */
@keyframes rotation {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* Contenedor general */
.loader {
    position: relative;
    width: 150px;
    height: 150px;
}

/* Imagen central del loader */
.img-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    z-index: 10;
    animation: none;
    user-select: none;
}



</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>