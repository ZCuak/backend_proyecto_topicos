@extends('layouts.app')
@section('title', 'Gestión de Personal — RSU Reciclaje')

@section('content')
<div class="space-y-8">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">👥 Gestión de Personal</h1>
            <p class="text-slate-500">Administra los miembros del equipo de recolección y gestión del RSU.</p>
        </div>
        <a href="{{ route('personal.create') }}" 
           class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i class="fa-solid fa-user-plus"></i> Nuevo Personal
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
        <input type="text" name="search" placeholder="Buscar por nombre, usuario o correo..."
               value="{{ $search }}" class="flex-1 border-none focus:ring-0 text-slate-700 placeholder-slate-400">
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i class="fa-solid fa-search"></i>
        </button>
    </form>

    {{-- TABLA --}}
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-emerald-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Correo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Rol</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($personales as $p)
                    <tr class="hover:bg-emerald-50/50 transition">
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $p->id }}</td>
                        <td class="px-6 py-4 text-sm text-slate-700 font-medium">{{ $p->firstname }} {{ $p->lastname }}</td>
                        <td class="px-6 py-4 text-sm text-slate-700">{{ $p->username }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $p->email }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $p->usertype->name ?? '—' }}</td>
                        <td class="px-6 py-4 flex justify-center gap-2">
                            <a href="{{ route('personal.show', $p->id) }}" 
                               class="px-2 py-1 bg-sky-100 text-sky-600 rounded-md hover:bg-sky-200" title="Ver">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('personal.edit', $p->id) }}" 
                               class="px-2 py-1 bg-yellow-100 text-yellow-600 rounded-md hover:bg-yellow-200" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form action="{{ route('personal.destroy', $p->id) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar este registro?')">
                                @csrf @method('DELETE')
                                <button class="px-2 py-1 bg-red-100 text-red-600 rounded-md hover:bg-red-200" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-slate-400">No se encontraron registros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="mt-4">
        {{ $personales->links() }}
    </div>
</div>
@endsection
