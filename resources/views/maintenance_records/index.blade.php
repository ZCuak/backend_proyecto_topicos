@extends('layouts.app')
@section('title', 'Gestión de Registros de Mantenimiento — RSU Reciclaje')

@section('content')
<div class="space-y-8">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Mantenimientos realizados</h1>
        </div>

        <!-- <a href="{{ route('maintenance-records.create') }}"
           data-turbo-frame="modal-frame"
           class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
           <i class="fa-solid fa-plus"></i> Nuevo Registro
        </a> -->
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg">
            {{ session('success') }}
        </div>
    @elseif(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- BUSCADOR --}}
    <form method="GET" class="flex items-center gap-3 bg-white p-4 rounded-xl shadow border border-slate-100">
        <input type="text" name="search" placeholder="Buscar por descripción o mantenimiento"
               value="{{ $search }}"
               class="flex-1 border-none focus:ring-0 text-slate-700 placeholder-slate-400">
        <button type="submit"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i class="fa-solid fa-search"></i>
        </button>
    </form>

    {{-- TABLA DE REGISTROS --}}
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-emerald-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">#</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Mantenimiento</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Vehículo</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Observación</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Foto</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Estado</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse($records as $record)
                    <tr class="hover:bg-emerald-50/40 transition">
                        <td class="px-4 py-3 text-slate-600">{{ $record->id }}</td>
                        <td class="px-4 py-3 font-mono text-slate-700">{{ $record->schedule->maintenance->name }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $record->schedule->vehicle->name }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $record->date }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $record->description }}</td>
                        <td class="px-4 py-3">
                            @if($record->image_path)
                                <a href="{{ asset('storage/' . $record->image_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $record->image_path) }}" alt="Imagen del registro" class="w-16 h-16 object-cover rounded-lg border">
                                </a>
                            @else
                                <span class="text-slate-400">Sin imagen</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $record->status }}</td>
                        <td class="px-4 py-3 flex justify-center gap-2">
                            <a href="{{ route('maintenance-records.edit', $record->id) }}"
                               data-turbo-frame="modal-frame"
                               class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200"
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <button type="button"
                                    class="btn-delete px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200"
                                    data-id="{{ $record->id }}"
                                    data-url="{{ route('maintenance-records.destroy', $record->id) }}"
                                    title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-slate-400">No se encontraron registros de mantenimiento.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="mt-4">
        {{ $records->links() }}
    </div>
</div>
@endsection