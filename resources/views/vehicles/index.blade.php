@extends('layouts.app')
@section('title', 'Gestión de Vehículos — RSU Reciclaje')

@section('content')
<div class="space-y-8">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">🚛 Gestión de Vehículos</h1>
            <p class="text-slate-500">Administra la flota de vehículos de recolección.</p>
        </div>

        <a href="{{ route('vehicles.create') }}"
           data-turbo-frame="modal-frame"
           class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
           <i class="fa-solid fa-plus"></i> Nuevo Vehículo
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
        <input type="text" name="search" placeholder="Buscar por nombre, código, placa, año, marca, modelo..."
               value="{{ $search }}"
               class="flex-1 border-none focus:ring-0 text-slate-700 placeholder-slate-400">
        <button type="submit"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i class="fa-solid fa-search"></i>
        </button>
    </form>

    {{-- TABLA DE VEHÍCULOS --}}
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-emerald-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">#</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Vehículo</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Placa</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Año</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Marca/Modelo</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Tipo</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Estado</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse($vehicles as $vehicle)
                    <tr class="hover:bg-emerald-50/40 transition">
                        <td class="px-4 py-3 text-slate-600">{{ $vehicle->id }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center overflow-hidden">
                                    @if($vehicle->profileImage)
                                        <img src="{{ $vehicle->profileImage->url }}" 
                                             alt="{{ $vehicle->name }}" 
                                             class="w-full h-full object-cover">
                                    @else
                                        <i class="fa-solid fa-truck text-slate-600"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-slate-700 font-medium">{{ $vehicle->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $vehicle->code }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-mono">
                                {{ $vehicle->plate }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $vehicle->year }}</td>
                        <td class="px-4 py-3">
                            <div>
                                <p class="text-slate-700 font-medium">{{ $vehicle->brand->name ?? '—' }}</p>
                                <p class="text-xs text-slate-500">{{ $vehicle->model->name ?? '—' }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $vehicle->type->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = [
                                    'DISPONIBLE' => 'bg-green-100 text-green-800',
                                    'OCUPADO' => 'bg-yellow-100 text-yellow-800',
                                    'MANTENIMIENTO' => 'bg-orange-100 text-orange-800',
                                    'INACTIVO' => 'bg-red-100 text-red-800'
                                ];
                                $statusIcons = [
                                    'DISPONIBLE' => 'fa-check-circle',
                                    'OCUPADO' => 'fa-clock',
                                    'MANTENIMIENTO' => 'fa-wrench',
                                    'INACTIVO' => 'fa-times-circle'
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$vehicle->status] ?? 'bg-slate-100 text-slate-800' }}">
                                <i class="fa-solid {{ $statusIcons[$vehicle->status] ?? 'fa-question' }} mr-1"></i>
                                {{ $vehicle->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 flex justify-center gap-2">
                            <a href="{{ route('vehicles.show', $vehicle->id) }}"
                               class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200"
                               title="Ver detalles">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            <a href="{{ route('vehicles.edit', $vehicle->id) }}"
                               data-turbo-frame="modal-frame"
                               class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200"
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <button type="button"
                                    class="btn-delete px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200"
                                    data-id="{{ $vehicle->id }}"
                                    data-url="{{ route('vehicles.destroy', $vehicle->id) }}"
                                    title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-slate-400">No se encontraron vehículos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="mt-4">
        {{ $vehicles->links() }}
    </div>
</div>
@endsection
