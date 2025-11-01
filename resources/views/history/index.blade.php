@extends('layouts.app')
@section('title', 'Historial de Cambios — RSU Reciclaje')

@section('content')
    <div class="space-y-8">

        {{-- ENCABEZADO --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">📜 Historial de Cambios</h1>
                <p class="text-slate-500">Registro de todas las modificaciones realizadas en el sistema</p>
            </div>
        </div>

        {{-- ALERTAS --}}
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @elseif(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-2">
                <i class="fa-solid fa-circle-xmark"></i>
                <span>{{ session('error') }}</span>
            </div>
        @elseif(session('info'))
            <div class="p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg flex items-center gap-2">
                <i class="fa-solid fa-circle-info"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        {{-- ESTADÍSTICAS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Total de Cambios --}}
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Total de Cambios</p>
                        <p class="text-3xl font-bold text-blue-700 mt-1">{{ $audits->total() }}</p>
                    </div>
                    <div class="bg-blue-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-clock-rotate-left text-2xl text-blue-700"></i>
                    </div>
                </div>
            </div>

            {{-- Módulos Auditados --}}
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium">Módulos Auditados</p>
                        <p class="text-3xl font-bold text-purple-700 mt-1">{{ $auditableTypes->count() }}</p>
                    </div>
                    <div class="bg-purple-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-layer-group text-2xl text-purple-700"></i>
                    </div>
                </div>
            </div>

            {{-- Usuarios Activos --}}
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-5 border border-emerald-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-emerald-600 font-medium">Usuarios Registrados</p>
                        <p class="text-3xl font-bold text-emerald-700 mt-1">
                            {{ \App\Models\Audit::distinct('user_name')->count('user_name') }}
                        </p>
                    </div>
                    <div class="bg-emerald-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-users text-2xl text-emerald-700"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- BARRA DE BÚSQUEDA CON BOTÓN DE FILTROS --}}
        <div class="flex flex-col sm:flex-row gap-3">
            {{-- Buscador general --}}
            <form method="GET" action="{{ route('history.index') }}"
                class="flex-1 flex items-center gap-3 bg-white p-4 rounded-xl shadow-md border border-slate-100">

                {{-- Mantener otros filtros activos --}}
                @if (request('auditable_type'))
                    <input type="hidden" name="auditable_type" value="{{ request('auditable_type') }}">
                @endif
                @if (request('user_name'))
                    <input type="hidden" name="user_name" value="{{ request('user_name') }}">
                @endif
                @if (request('start_date'))
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                @endif
                @if (request('end_date'))
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                @endif
                @if (request('campo_modificado'))
                    <input type="hidden" name="campo_modificado" value="{{ request('campo_modificado') }}">
                @endif

                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                <input type="text" name="search" placeholder="Buscar por usuario, campo, valor anterior/nuevo..."
                    value="{{ request('search') }}"
                    class="flex-1 border-none focus:ring-0 text-slate-700 placeholder-slate-400 p-0">
                <button type="submit"
                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                    <i class="fa-solid fa-search"></i>
                </button>
            </form>

            {{-- Botón para abrir modal de filtros --}}
            <button type="button" onclick="openFiltersModal()"
                class="flex items-center gap-2 px-5 py-4 bg-white border-2 border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 hover:border-emerald-500 transition shadow-md">
                <i class="fa-solid fa-filter text-emerald-600"></i>
                <span class="font-medium">Filtros Avanzados</span>
                @if (request()->hasAny(['auditable_type', 'user_name', 'start_date', 'campo_modificado']))
                    <span class="px-2 py-0.5 bg-emerald-600 text-white text-xs rounded-full">
                        {{ count(array_filter([request('auditable_type'), request('user_name'), request('start_date'), request('campo_modificado')])) }}
                    </span>
                @endif
            </button>
        </div>

        {{-- FILTROS ACTIVOS (BADGES) --}}
        @if (request()->hasAny(['search', 'auditable_type', 'user_name', 'start_date', 'campo_modificado']))
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-emerald-700">
                        <i class="fa-solid fa-filter mr-1"></i>
                        Filtros activos:
                    </span>

                    @if (request('search'))
                        <span
                            class="inline-flex items-center gap-2 px-3 py-1 bg-white border border-emerald-300 text-emerald-700 rounded-full text-xs font-medium">
                            Búsqueda: "{{ request('search') }}"
                            <a href="{{ route('history.index', array_filter(request()->except('search'))) }}"
                                class="hover:text-emerald-900">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </span>
                    @endif

                    @if (request('auditable_type'))
                        <span
                            class="inline-flex items-center gap-2 px-3 py-1 bg-white border border-emerald-300 text-emerald-700 rounded-full text-xs font-medium">
                            Módulo: {{ request('auditable_type') }}
                            <a href="{{ route('history.index', array_filter(request()->except('auditable_type'))) }}"
                                class="hover:text-emerald-900">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </span>
                    @endif

                    @if (request('user_name'))
                        <span
                            class="inline-flex items-center gap-2 px-3 py-1 bg-white border border-emerald-300 text-emerald-700 rounded-full text-xs font-medium">
                            Usuario: {{ request('user_name') }}
                            <a href="{{ route('history.index', array_filter(request()->except('user_name'))) }}"
                                class="hover:text-emerald-900">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </span>
                    @endif

                    @if (request('start_date'))
                        @if (request('end_date'))
                            <span
                                class="inline-flex items-center gap-2 px-3 py-1 bg-white border border-emerald-300 text-emerald-700 rounded-full text-xs font-medium">
                                📅 {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} -
                                {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                                <a href="{{ route('history.index', array_filter(request()->except(['start_date', 'end_date']))) }}"
                                    class="hover:text-emerald-900">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-2 px-3 py-1 bg-white border border-emerald-300 text-emerald-700 rounded-full text-xs font-medium">
                                📅 {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }}
                                <a href="{{ route('history.index', array_filter(request()->except('start_date'))) }}"
                                    class="hover:text-emerald-900">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            </span>
                        @endif
                    @endif

                    @if (request('campo_modificado'))
                        <span
                            class="inline-flex items-center gap-2 px-3 py-1 bg-white border border-emerald-300 text-emerald-700 rounded-full text-xs font-medium">
                            Campo: {{ request('campo_modificado') }}
                            <a href="{{ route('history.index', array_filter(request()->except('campo_modificado'))) }}"
                                class="hover:text-emerald-900">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </span>
                    @endif

                    <a href="{{ route('history.index') }}"
                        class="ml-auto px-3 py-1 bg-emerald-600 text-white rounded-full text-xs font-medium hover:bg-emerald-700 transition">
                        <i class="fa-solid fa-rotate-left mr-1"></i>
                        Limpiar todos
                    </a>
                </div>
            </div>
        @endif

        {{-- TABLA DE HISTORIAL --}}
        <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Fecha y
                                Hora</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Módulo
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Registro
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Tipo de
                                cambio
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Valor
                                Anterior</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Valor
                                Nuevo</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Usuario
                            </th>
                            {{-- <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase tracking-wider">
                                Acciones</th> --}}
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($audits as $audit)
                            <tr class="hover:bg-slate-50 transition">
                                {{-- Fecha y Hora --}}
                                <td class="px-4 py-3 text-slate-600">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $audit->created_at->format('d/m/Y') }}</span>
                                        <span
                                            class="text-xs text-slate-400">{{ $audit->created_at->format('H:i:s') }}</span>
                                    </div>
                                </td>

                                {{-- Módulo --}}
                                <td class="px-4 py-3">

                                    {{ $audit->auditable_type }}

                                </td>

                                {{-- ID del Registro --}}
                                <td class="px-4 py-3 text-slate-600 font-mono text-xs">
                                    #{{ $audit->auditable_id }}
                                </td>

                                {{-- Campo Modificado --}}
                                <td class="px-4 py-3 text-slate-700 font-medium">
                                    {{ $audit->campo_modificado }}
                                </td>

                                {{-- Valor Anterior --}}
                                <td class="px-4 py-3">
                                    <span class="inline-block max-w-xs truncate text-slate-500"
                                        title="{{ $audit->valor_anterior }}">
                                        {{ $audit->valor_anterior ?? '—' }}
                                    </span>
                                </td>

                                {{-- Valor Nuevo --}}
                                <td class="px-4 py-3">
                                    <span class="inline-block max-w-xs truncate text-slate-700 font-medium"
                                        title="{{ $audit->valor_nuevo }}">
                                        {{ $audit->valor_nuevo ?? '—' }}
                                    </span>
                                </td>

                                {{-- Usuario --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                                            <i class="fa-solid fa-user text-emerald-600 text-xs"></i>
                                        </div>
                                        <span class="text-slate-600 text-xs">{{ $audit->user_name }}</span>
                                    </div>
                                </td>

                                {{-- Acciones --}}
                                {{-- <td class="px-4 py-3 text-center">
                                    <a href="#"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition text-xs"
                                        title="Ver historial completo">
                                        <i class="fa-solid fa-history"></i>
                                        <span>Ver historial</span>
                                    </a>
                                </td> --}}
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400">
                                    <i class="fa-solid fa-inbox text-5xl mb-3 text-slate-300"></i>
                                    <p class="text-lg font-medium">No hay cambios registrados</p>
                                    <p class="text-sm">Los cambios aparecerán aquí cuando se realicen modificaciones</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-6">
            {{ $audits->appends(request()->query())->links() }}
        </div>
    </div>

    @include('history.filtros')
@endsection
