@extends('layouts.guest')
@section('title', 'Iniciar sesión - RSU Reciclaje')

@section('content')
<div class="relative flex min-h-screen w-full items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-900 px-4 py-10 overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(16,185,129,0.14),transparent_35%),radial-gradient(circle_at_80%_0%,rgba(59,130,246,0.12),transparent_32%)] pointer-events-none"></div>

    <div class="relative w-full max-w-lg bg-white/95 dark:bg-gray-900/90 shadow-2xl rounded-2xl p-8 border border-emerald-200/70 dark:border-gray-700 backdrop-blur">
        {{-- ENCABEZADO --}}
        <div class="text-center mb-6">
            <img src="{{ asset('img/logo_rsu.png') }}" alt="RSU Reciclaje" class="h-16 mx-auto mb-3">
            <h1 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">RSU Reciclaje</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Sistema de gestión de rutas y personal - USAT 🌱
            </p>
        </div>

        {{-- FORMULARIO --}}
        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf

            {{-- EMAIL --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Correo institucional</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 
                           focus:ring-2 focus:ring-emerald-500 focus:outline-none px-4 py-3 text-gray-900 dark:text-gray-100 shadow-sm transition">
            </div>

            {{-- PASSWORD --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contraseña</label>
                <input type="password" name="password" required
                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 
                           focus:ring-2 focus:ring-emerald-500 focus:outline-none px-4 py-3 text-gray-900 dark:text-gray-100 shadow-sm transition">
            </div>

            {{-- RECORDAR --}}
            <div class="flex items-center justify-between">
                <label class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <input type="checkbox" name="remember" class="mr-2 rounded border-gray-300 dark:border-gray-600 text-emerald-600 focus:ring-emerald-500">
                    Recuérdame
                </label>
                <a href="#" class="text-sm text-emerald-600 hover:underline">¿Olvidaste tu contraseña?</a>
            </div>

            {{-- ERRORES --}}
            @error('email')
                <p class="text-red-500 text-sm text-center">{{ $message }}</p>
            @enderror

            {{-- BOTÓN --}}
            <button type="submit"
                class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold transition duration-200 shadow-lg shadow-emerald-600/20">
                <i class="fa-solid fa-recycle mr-1"></i> Iniciar sesión
            </button>
        </form>

        {{-- FOOTER --}}
        <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            © {{ date('Y') }} Proyecto RSU Reciclaje · USAT
        </p>
    </div>
</div>
@endsection
