@extends('layouts.app')
@section('title', 'Contratos')

@section('content')
<div class="space-y-8">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Contratos</h1>
            <p class="text-slate-500">Administración de contratos del personal.</p>
        </div>

        <a href="{{ route('contracts.create') }}"
           data-turbo-frame="modal-frame"
           class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
           <i class="fa-solid fa-plus"></i> Nuevo contrato
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
    <form method="GET" action="{{ route('contracts.index') }}" class="flex items-center gap-3 bg-white p-4 rounded-xl shadow border border-slate-100">
        <input type="text"
               name="search"
               placeholder="Buscar por usuario o tipo..."
               value="{{ $search ?? '' }}"
               class="flex-1 border-none focus:ring-0 text-slate-700 placeholder-slate-400">
        <button type="submit"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i class="fa-solid fa-search"></i>
        </button>
    </form>

    {{-- TABLA DE CONTRATOS --}}
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-emerald-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">#</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Usuario</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Tipo</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Inicio</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Fin</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Días vac.</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Activo</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse($contracts as $c)
                    <tr class="hover:bg-emerald-50/40 transition">
                        <td class="px-4 py-3 text-slate-600 font-mono">{{ $c->id }}</td>
                        <td class="px-4 py-3 text-slate-700 font-medium">{{ optional($c->user)->firstname ?? '—' }} {{ optional($c->user)->lastname ?? '' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ ucfirst($c->type) }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ optional($c->date_start)->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ optional($c->date_end)->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-4 py-3 text-center font-medium">{{ $c->vacation_days_per_year }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($c->is_active)
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Activo</span>
                            @else
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-600">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 flex justify-center gap-2">
                            <a href="{{ route('contracts.edit', $c->id) }}"
                               data-turbo-frame="modal-frame"
                               class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200 transition"
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>

                            <button type="button"
                                    class="btn-delete px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition"
                                    data-id="{{ $c->id }}"
                                    data-url="{{ route('contracts.destroy', $c->id) }}"
                                    title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-slate-400">
                            <i class="fa-solid fa-inbox text-4xl mb-2"></i>
                            <p>No se encontraron contratos.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="mt-4">
        {{ $contracts->links() }}
    </div>
</div>
@endsection