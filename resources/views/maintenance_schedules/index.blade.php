@extends('layouts.app')
@section('title', 'Gestión de Horarios de Mantenimiento — RSU Reciclaje')

@section('content')
<div class="space-y-8">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">🕒 Gestión de Horarios de Mantenimiento</h1>
            <p class="text-slate-500">Administra los horarios programados para los mantenimientos.</p>
        </div>

        <a href="{{ route('maintenance-schedules.create') }}"
           data-turbo-frame="modal-frame"
           class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
           <i class="fa-solid fa-plus"></i> Nuevo Horario
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
        <input type="text" name="search" placeholder="Buscar por mantenimiento"
               value="{{ $search }}"
               class="flex-1 border-none focus:ring-0 text-slate-700 placeholder-slate-400">
        <button type="submit"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i class="fa-solid fa-search"></i>
        </button>
    </form>

    {{-- TABLA DE HORARIOS --}}
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-emerald-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700 uppercase tracking-wider">Día</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700 uppercase tracking-wider">Vehículo</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700 uppercase tracking-wider">Responsable</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700 uppercase tracking-wider">Tipo</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wider">Inicio</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wider">Fin</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wider">
                        <i class="fa-solid fa-eye"></i> VER
                    </th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wider">
                        <i class="fa-solid fa-pen"></i> acciones
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($schedules as $schedule)
                    <tr class="hover:bg-emerald-50/40 transition">
                        {{-- DÍA --}}
                        <td class="px-4 py-3 font-medium text-slate-800">
                            {{ ucfirst(strtolower($schedule->day)) }}
                        </td>

                        {{-- VEHÍCULO --}}
                        <td class="px-4 py-3 text-slate-700">
                            {{ $schedule->vehicle->plate }} - {{ $schedule->vehicle->name }}
                        </td>

                        {{-- RESPONSABLE --}}
                        <td class="px-4 py-3 text-slate-700">
                            {{ $schedule->responsible->firstname ?? 'N/A' }} {{ $schedule->responsible->lastname ?? '' }}
                        </td>

                        {{-- TIPO --}}
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs font-medium
                                {{ $schedule->type === 'PREVENTIVO' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $schedule->type === 'LIMPIEZA' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $schedule->type === 'REPARACIÓN' ? 'bg-orange-100 text-orange-700' : '' }}">
                                {{ $schedule->type }}
                            </span>
                        </td>

                        {{-- HORA INICIO --}}
                        <td class="px-4 py-3 text-center text-slate-600">
                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                        </td>

                        {{-- HORA FIN --}}
                        <td class="px-4 py-3 text-center text-slate-600">
                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                        </td>

                        {{-- VER DÍAS GENERADOS --}}
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('maintenance-records.show', $schedule->id) }}"
                               class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-purple-100 text-purple-700 hover:bg-purple-200 transition"
                               title="Ver días generados">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>

                        <td class="px-4 py-3 flex justify-center gap-2">
                            <a href="{{ route('maintenance-schedules.edit', $schedule->id) }}"
                               data-turbo-frame="modal-frame"
                               class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200"
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <button type="button"
                                    class="btn-delete px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200"
                                    data-id="{{ $schedule->id }}"
                                    data-url="{{ route('maintenance-schedules.destroy', $schedule->id) }}"
                                    title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-8">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <i class="fa-solid fa-inbox text-4xl"></i>
                                <p class="text-sm">No se encontraron horarios registrados</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>

            
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="mt-4">
        {{ $schedules->links() }}
    </div>
</div>
@endsection