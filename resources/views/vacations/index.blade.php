@extends('layouts.app')
@section('title', 'Gestión de Vacaciones')

@section('content')
<div class="space-y-8">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Gestión de Vacaciones</h1>
            <p class="text-slate-500">Administración de solicitudes de vacación del personal.</p>
        </div>

        <a href="{{ route('vacations.create') }}"
           data-turbo-frame="modal-frame"
           class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
           <i class="fa-solid fa-plus"></i> Nueva vacación
        </a>
    </div>

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
    <form method="GET" action="{{ route('vacations.index') }}" class="flex items-center gap-3 bg-white p-4 rounded-xl shadow border border-slate-100">
        <input type="text"
               name="search"
               placeholder="Buscar por usuario, año o estado..."
               value="{{ $search ?? '' }}"
               class="flex-1 border-none focus:ring-0 text-slate-700 placeholder-slate-400">
        <button type="submit"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i class="fa-solid fa-search"></i>
        </button>
    </form>

    {{-- TABLA DE VACACIONES --}}
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-emerald-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">#</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Usuario</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Año</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Inicio</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Fin</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Días programados</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Días pendientes</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Estado</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse($vacations as $v)
                    <tr class="hover:bg-emerald-50/40 transition">
                        <td class="px-4 py-3 text-slate-600 font-mono">{{ $v->id }}</td>
                        <td class="px-4 py-3 text-slate-700 font-medium">{{ optional($v->user)->firstname ?? '—' }} {{ optional($v->user)->lastname ?? '' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $v->year }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ optional($v->start_date)->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ optional($v->end_date)->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-4 py-3 text-center font-medium">{{ $v->days_programmed }}</td>
                        <td class="px-4 py-3 text-center">{{ $v->days_pending }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $v->status === 'aprobada' ? 'bg-emerald-100 text-emerald-700' : ($v->status === 'rechazada' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ strtoupper($v->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 flex justify-center gap-2">
                            <a href="{{ route('vacations.edit', $v->id) }}"
                               data-turbo-frame="modal-frame"
                               class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200 transition"
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <button type="button"
                                    class="btn-delete px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition"
                                    data-id="{{ $v->id }}"
                                    data-url="{{ route('vacations.destroy', $v->id) }}"
                                    title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-8 text-slate-400">
                            <i class="fa-solid fa-inbox text-4xl mb-2"></i>
                            <p>No se encontraron solicitudes de vacación.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="mt-4">
        {{ $vacations->links() }}
    </div>
</div>
@endsection