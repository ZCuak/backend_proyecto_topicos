@extends('layouts.guest')
@section('title', 'Iniciar sesión - RSU Reciclaje')

@section('content')
<div class="flex min-h-screen items-center justify-center bg-green-100 dark:bg-gray-900 px-4">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-8 border border-green-200 dark:border-gray-700">
        {{-- ENCABEZADO --}}
        <div class="text-center mb-6">
            <img src="{{ asset('img/logo_rsu.png') }}" alt="RSU Reciclaje" class="h-16 mx-auto mb-3">
            <h1 class="text-2xl font-bold text-green-700 dark:text-green-400">RSU Reciclaje</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
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
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 
                           focus:ring-2 focus:ring-green-500 focus:outline-none px-4 py-2.5 text-gray-900 dark:text-gray-100">
            </div>

            {{-- PASSWORD --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contraseña</label>
                <input type="password" name="password" required
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 
                           focus:ring-2 focus:ring-green-500 focus:outline-none px-4 py-2.5 text-gray-900 dark:text-gray-100">
            </div>

            {{-- RECORDAR --}}
            <div class="flex items-center justify-between">
                <label class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <input type="checkbox" name="remember" class="mr-2 rounded border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500">
                    Recuérdame
                </label>
                <a href="#" class="text-sm text-green-600 hover:underline">¿Olvidaste tu contraseña?</a>
            </div>

            {{-- ERRORES --}}
            @error('email')
                <p class="text-red-500 text-sm text-center">{{ $message }}</p>
            @enderror

            {{-- BOTÓN --}}
            <button type="submit"
                class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition duration-200">
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
