@extends('layouts.app')
@section('title', 'Gestión de Turnos — RSU Reciclaje')

@section('content')
<div class="space-y-8">

    <!-- ENCABEZADO -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-regular fa-clock text-emerald-600"></i>
                Gestión de Turnos
            </h1>
            <p class="text-slate-500">Administra los turnos de trabajo registrados en el sistema.</p>
        </div>

        <a href="{{ route('schedules.create') }}" 
           data-turbo-frame="modal-frame"
           class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i class="fa-solid fa-plus"></i> Nuevo Turno
        </a>
    </div>

    <!-- ALERTAS FLASH -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg shadow-sm">
            <i class="fa-solid fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @elseif(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg shadow-sm">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- BARRA DE BÚSQUEDA -->
    <form method="GET" class="flex items-center gap-3 bg-white p-4 rounded-xl shadow-sm border border-slate-100">
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

    <!-- TABLA DE TURNOS -->
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-emerald-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">#</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Nombre</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Inicio</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Fin</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Descripción</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($schedules as $s)
                    <tr class="hover:bg-emerald-50/50 transition">
                        <td class="px-4 py-3 text-slate-600">{{ $s->id }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $s->name }}</td>
                        <td class="px-4 py-3 text-slate-700">
{{ $s->time_start ? \Carbon\Carbon::parse($s->time_start)->format('H:i') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
{{ $s->time_end ? \Carbon\Carbon::parse($s->time_end)->format('H:i') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ \Illuminate\Support\Str::limit($s->description, 80) ?? '—' }}
                        </td>
                        <td class="px-4 py-3 flex justify-center gap-2">
                            <a href="{{ route('schedules.edit', $s->id) }}" 
                               data-turbo-frame="modal-frame"
                               class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200" 
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <button type="button" 
                                    class="btn-delete px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200"
                                    data-id="{{ $s->id }}"
                                    data-url="{{ route('schedules.destroy', $s->id) }}"
                                    title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>

                            <a href="{{ route('schedules.show', $s->id) }}" 
                               class="px-2 py-1 bg-slate-100 text-slate-700 rounded-md hover:bg-slate-200" 
                               title="Ver">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-slate-400 italic">
                            <i class="fa-regular fa-calendar-xmark mr-1"></i>
                            No se encontraron registros.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINACIÓN -->
    @if ($schedules->hasPages())
        <div class="mt-5">
            {{ $schedules->onEachSide(1)->links('pagination::tailwind') }}
        </div>
    @endif

</div>
@endsection
