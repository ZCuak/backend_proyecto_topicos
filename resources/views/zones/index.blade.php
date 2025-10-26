@extends('layouts.app')
@section('title', 'Gestión de Zonas — RSU Reciclaje')

@section('content')
<div class="space-y-8">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">🗺️ Gestión de Zonas</h1>
            <p class="text-slate-500">Administra las zonas de recolección y sus perímetros geográficos.</p>
        </div>

        <a href="{{ route('zones.create') }}"
           data-turbo-frame="modal-frame"
           class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
           <i class="fa-solid fa-plus"></i> Nueva Zona
        </a>
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
        <input type="text" name="search" placeholder="Buscar por nombre, descripción, distrito o sector..."
               value="{{ $search }}"
               class="flex-1 border-none focus:ring-0 text-slate-700 placeholder-slate-400">
        <button type="submit"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i class="fa-solid fa-search"></i>
        </button>
    </form>

    {{-- TABLA DE ZONAS --}}
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-emerald-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">#</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Nombre</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Distrito</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Sector</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Área (km²)</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Coordenadas</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Descripción</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse($zones as $zone)
                    <tr class="hover:bg-emerald-50/40 transition">
                        <td class="px-4 py-3 text-slate-600">{{ $zone->id }}</td>
                        <td class="px-4 py-3 text-slate-700 font-medium">{{ $zone->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $zone->district->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $zone->sector->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">
                            @if($zone->area)
                                {{ number_format($zone->area, 2) }} km²
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500">
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">
                                {{ $zone->coordinates->count() }} puntos
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 max-w-xs truncate">
                            {{ $zone->description ?? '—' }}
                        </td>
                        <td class="px-4 py-3 flex justify-center gap-2">
                            <a href="{{ route('zones.show', $zone->id) }}"
                               class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200"
                               title="Ver detalles">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            <a href="{{ route('zones.edit', $zone->id) }}"
                               data-turbo-frame="modal-frame"
                               class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200"
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <button type="button"
                                    class="btn-delete px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200"
                                    data-id="{{ $zone->id }}"
                                    data-url="{{ route('zones.destroy', $zone->id) }}"
                                    title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-slate-400">No se encontraron zonas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="mt-4">
        {{ $zones->links() }}
    </div>
</div>
@endsection