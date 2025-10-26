@extends('layouts.app')
@section('title', 'Gestión de Tipos de Vehículos — RSU Reciclaje')

@section('content')
<div class="space-y-8">

    {{-- ENCABEZADO CON TÍTULO Y BOTÓN CREAR --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">🚛 Gestión de Tipos de Vehículos</h1>
            <p class="text-slate-500">Administra las categorías de vehículos de la flota.</p>
        </div>

        {{-- Botón para abrir modal de creación con Turbo --}}
        <a href="{{ route('vehicletypes.create') }}"
           data-turbo-frame="modal-frame"
           class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
           <i class="fa-solid fa-plus"></i> Nuevo Tipo de Vehículo
        </a>
    </div>

    {{-- ALERTAS DE ÉXITO/ERROR --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg">
            <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
        </div>
    @elseif(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
            <i class="fa-solid fa-circle-xmark mr-2"></i> {{ session('error') }}
        </div>
    @endif

    {{-- BUSCADOR --}}
    <form method="GET" action="{{ route('vehicletypes.index') }}" 
          class="flex items-center gap-3 bg-white p-4 rounded-xl shadow border border-slate-100">
        <input type="text" 
               name="search" 
               placeholder="Buscar por nombre o descripción..."
               value="{{ $search ?? '' }}"
               class="flex-1 border-none focus:ring-0 text-slate-700 placeholder-slate-400">
        <button type="submit"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i class="fa-solid fa-search"></i>
        </button>
    </form>

    {{-- TABLA DE TIPOS DE VEHÍCULOS --}}
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-emerald-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">#</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Nombre</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Descripción</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse($vehicletypes as $vt)
                    <tr class="hover:bg-emerald-50/40 transition">
                        <td class="px-4 py-3 text-slate-600 font-mono">{{ $vt->id }}</td>
                        <td class="px-4 py-3 text-slate-700 font-medium">
                            <i class="fa-solid fa-truck text-emerald-600 mr-2"></i>
                            {{ $vt->name }}
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $vt->description ?? '—' }}</td>
                        <td class="px-4 py-3 flex justify-center gap-2">
                            {{-- Botón Editar con Turbo Frame --}}
                            <a href="{{ route('vehicletypes.edit', $vt->id) }}"
                               data-turbo-frame="modal-frame"
                               class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200 transition"
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            {{-- Botón Eliminar con AJAX --}}
                            <button type="button"
                                    class="btn-delete px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition"
                                    data-id="{{ $vt->id }}"
                                    data-url="{{ route('vehicletypes.destroy', $vt->id) }}"
                                    title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-slate-400">
                            <i class="fa-solid fa-inbox text-4xl mb-2"></i>
                            <p>No se encontraron tipos de vehículos registrados.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="mt-4">
        {{ $vehicletypes->links() }}
    </div>
</div>
@endsection