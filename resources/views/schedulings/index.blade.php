@extends('layouts.app')
@section('title', 'Gestión de Programaciones — RSU Reciclaje')

@section('content')
<div class="space-y-8">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">📅 Gestión de Programaciones</h1>
            <p class="text-slate-500">Administra las programaciones de recolección por grupos y zonas.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('schedulings.calendar') }}"
               class="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
               <i class="fa-solid fa-calendar"></i> Vista Calendario
            </a>
            
            <a href="{{ route('schedulings.create') }}"
               data-turbo-frame="modal-frame"
               class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
               <i class="fa-solid fa-plus"></i> Nueva Programación
            </a>
            
            <a href="{{ route('schedulings.create-massive') }}"
               data-turbo-frame="modal-frame"
               class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
               <i class="fa-solid fa-calendar-plus"></i> Programación Masiva
            </a>
                <a href="{{ route('schedulings.edit-massive') }}"
                    data-turbo-frame="modal-frame"
                    class="flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
                    <i class="fa-solid fa-edit"></i> Editar Masivo
                </a>
        </div>
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

    {{-- FILTROS --}}
    <form method="GET" class="flex items-center gap-3 bg-white p-4 rounded-xl shadow border border-slate-100">
        <input type="text" name="search" placeholder="Buscar por grupo, horario, vehículo, zona, notas..."
               value="{{ $search }}"
               class="flex-1 border-none focus:ring-0 text-slate-700 placeholder-slate-400">
        
        <input type="date" name="date_filter" 
               value="{{ $dateFilter }}"
               class="px-3 py-2 border border-slate-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
        
        <button type="submit"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i class="fa-solid fa-search"></i>
        </button>
        
        @if($search || $dateFilter)
            <a href="{{ route('schedulings.index') }}"
               class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition">
                <i class="fa-solid fa-times"></i>
            </a>
        @endif
    </form>

    {{-- TABLA DE PROGRAMACIONES --}}
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-emerald-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">#</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Fecha</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Grupo</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Horario</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Vehículo</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Zona</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Estado</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse($schedulings as $scheduling)
                    <tr class="hover:bg-emerald-50/40 transition">
                        <td class="px-4 py-3 text-slate-600">{{ $scheduling->id }}</td>
                        
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-calendar text-emerald-600"></i>
                                <span class="text-slate-700 font-medium">
                                    {{ is_string($scheduling->date) ? \Carbon\Carbon::parse($scheduling->date)->format('d/m/Y') : $scheduling->date->format('d/m/Y') }}
                                </span>
                            </div>
                        </td>
                        
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-users text-blue-600"></i>
                                <span class="text-slate-700">{{ $scheduling->group->name ?? '—' }}</span>
                            </div>
                        </td>
                        
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-clock text-orange-600"></i>
                                <span class="text-slate-700">{{ $scheduling->schedule->name ?? '—' }}</span>
                            </div>
                        </td>
                        
                        <td class="px-4 py-3">
                            @if($scheduling->vehicle)
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-truck text-purple-600"></i>
                                    <span class="text-slate-700">{{ $scheduling->vehicle->name }}</span>
                                </div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        
                        <td class="px-4 py-3">
                            @if($scheduling->zone)
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-map-marker-alt text-red-600"></i>
                                    <span class="text-slate-700">{{ $scheduling->zone->name }}</span>
                                </div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        
                        <td class="px-4 py-3">
                            @php
                                $statusConfig = [
                                    0 => ['text' => 'Pendiente', 'class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fa-clock'],
                                    1 => ['text' => 'En Proceso', 'class' => 'bg-blue-100 text-blue-800', 'icon' => 'fa-play'],
                                    2 => ['text' => 'Completado', 'class' => 'bg-green-100 text-green-800', 'icon' => 'fa-check'],
                                    3 => ['text' => 'Cancelado', 'class' => 'bg-red-100 text-red-800', 'icon' => 'fa-times']
                                ];
                                $config = $statusConfig[$scheduling->status] ?? $statusConfig[0];
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $config['class'] }}">
                                <i class="fa-solid {{ $config['icon'] }} mr-1"></i>
                                {{ $config['text'] }}
                            </span>
                        </td>
                        
                        <td class="px-4 py-3 flex justify-center gap-2">
                            <a href="{{ route('schedulings.show', $scheduling->id) }}"
                               class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200"
                               title="Ver detalles">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            <a href="{{ route('schedulings.edit', $scheduling->id) }}"
                               data-turbo-frame="modal-frame"
                               class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200"
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <button type="button"
                                    class="btn-delete px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200"
                                    data-id="{{ $scheduling->id }}"
                                    data-url="{{ route('schedulings.destroy', $scheduling->id) }}"
                                    title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-slate-400">
                            <i class="fa-solid fa-calendar-times text-4xl mb-2"></i>
                            <p>No se encontraron programaciones.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="mt-4">
        {{ $schedulings->links() }}
    </div>
</div>
@endsection
