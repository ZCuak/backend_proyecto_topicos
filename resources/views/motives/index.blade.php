@extends('layouts.app')
@section('title', 'Motivos - RSU Reciclaje')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Catálogo de Motivos</h1>
            <p class="text-slate-500">Gestiona los motivos usados en auditoría y registros.</p>
        </div>
        <a href="{{ route('motives.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i class="fa-solid fa-plus"></i> Nuevo Motivo
        </a>
    </div>

    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">#</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Nombre</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($motives as $motive)
                    <tr class="hover:bg-emerald-50/40 transition">
                        <td class="px-4 py-3 text-slate-600">{{ $motive->id }}</td>
                        <td class="px-4 py-3 text-slate-700 font-medium">{{ $motive->name }}</td>
                        <td class="px-4 py-3 text-center space-x-2">
                            <a href="{{ route('motives.edit', $motive) }}"
                               class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200"
                               title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('motives.destroy', $motive) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('¿Eliminar este motivo?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200"
                                    title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-6 text-slate-400">No hay motivos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $motives->links() }}
    </div>
</div>
@endsection
