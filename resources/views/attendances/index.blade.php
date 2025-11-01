@extends('layouts.app')
@section('title', 'Gestión de Asistencias — RSU Reciclaje')

@section('content')
    <div class="space-y-8">

        {{-- ENCABEZADO CON TÍTULO Y BOTÓN CREAR --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">📋 Gestión de Asistencias</h1>
                <p class="text-slate-500">Control de entrada y salida del personal - {{ $today->format('d/m/Y') }}</p>
            </div>

            <div class="flex gap-2">
                {{-- Botón para página de marcado rápido --}}
                <a href="{{ route('attendance.mark.view') }}" target="_blank"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fa-solid fa-clock"></i> Marcar Asistencia
                </a>

                {{-- Botón para registrar manualmente (Admin) --}}
                <a href="{{ route('attendances.create') }}" data-turbo-frame="modal-frame"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                    <i class="fa-solid fa-plus"></i> Registrar Manualmente
                </a>
            </div>
        </div>

        {{-- ALERTAS DE ÉXITO/ERROR --}}
        @if (session('error'))
            <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <i class="fa-solid fa-circle-xmark mr-2"></i> {{ session('error') }}
            </div>
        @elseif(session('warning'))
            <div class="p-4 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i> {{ session('warning') }}
            </div>
        @elseif (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg">
                <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
            </div>
        @endif

        {{-- ESTADÍSTICAS DEL DÍA --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Total Presente --}}
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-5 border border-emerald-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-emerald-600 font-medium">Presente</p>
                        <p class="text-3xl font-bold text-emerald-700 mt-1">
                            {{ $attendances->where('status', 'PRESENTE')->count() }}
                        </p>
                    </div>
                    <div class="bg-emerald-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-circle-check text-2xl text-emerald-700"></i>
                    </div>
                </div>
            </div>

            {{-- Total con Salida --}}
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Salidas Registradas</p>
                        <p class="text-3xl font-bold text-blue-700 mt-1">
                            {{ $attendances->whereNotNull('check_out')->count() }}
                        </p>
                    </div>
                    <div class="bg-blue-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-door-open text-2xl text-blue-700"></i>
                    </div>
                </div>
            </div>

            {{-- Pendientes de Salida --}}
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-5 border border-amber-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-amber-600 font-medium">Sin Marcar Salida</p>
                        <p class="text-3xl font-bold text-amber-700 mt-1">
                            {{ $attendances->whereNull('check_out')->count() }}
                        </p>
                    </div>
                    <div class="bg-amber-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-clock text-2xl text-amber-700"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================
         BARRA DE BÚSQUEDA CON BOTÓN DE FILTROS
        ======================================== --}}
        <div class="flex flex-col sm:flex-row gap-3">
            {{-- Buscador general --}}
            <form method="GET" action="{{ route('attendances.index') }}"
                class="flex-1 flex items-center gap-3 bg-white p-4 rounded-xl shadow-md border border-slate-100">

                {{-- Mantener otros filtros activos --}}
                @if (request('start_date'))
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                @endif
                @if (request('end_date'))
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                @endif
                @if (request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                @if (request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                <input type="text" name="search" placeholder="Buscar por nombre, apellido o DNI..."
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
                @if (request()->hasAny(['start_date', 'type', 'status']))
                    <span class="px-2 py-0.5 bg-emerald-600 text-white text-xs rounded-full">
                        {{ count(array_filter([request('start_date'), request('type'), request('status')])) }}
                    </span>
                @endif
            </button>
        </div>

        {{--  FILTROS ACTIVOS (BADGES) --}}
        @if (request()->hasAny(['search', 'start_date', 'end_date', 'type', 'status']))
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-emerald-700">
                        <i class="fa-solid fa-filter mr-1"></i>
                        Filtros activos:
                    </span>

                    {{-- Badge: BÚSQUEDA --}}
                    @if (request('search'))
                        <span
                            class="px-3 py-1 bg-white border border-emerald-300 text-emerald-700 rounded-full text-xs font-medium">
                            Búsqueda: "{{ request('search') }}"
                            <a href="{{ route('attendances.index', array_filter(request()->except('search'))) }}"
                                class="ml-1 text-emerald-600 hover:text-emerald-800">×</a>
                        </span>
                    @endif

                    {{-- Badge: FECHA/RANGO (Lógica Unificada) --}}
                    @if (request('start_date'))
                        @php
                            $startDate = request('start_date');
                            $endDate = request('end_date');
                            $paramsToExcept = ['start_date', 'end_date']; // Siempre eliminamos ambos
                        @endphp

                        @if ($endDate && $endDate > $startDate)
                            {{-- Caso: RANGO COMPLETO --}}
                            <span
                                class="px-3 py-1 bg-white border border-emerald-300 text-emerald-700 rounded-full text-xs font-medium">
                                Rango: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} -
                                {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                                <a href="{{ route('attendances.index', array_filter(request()->except($paramsToExcept))) }}"
                                    class="ml-1 text-emerald-600 hover:text-emerald-800">×</a>
                            </span>
                        @else
                            {{-- Caso: FECHA ÚNICA (Solo start_date) --}}
                            <span
                                class="px-3 py-1 bg-white border border-emerald-300 text-emerald-700 rounded-full text-xs font-medium">
                                Fecha: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                                <a href="{{ route('attendances.index', array_filter(request()->except($paramsToExcept))) }}"
                                    class="ml-1 text-emerald-600 hover:text-emerald-800">×</a>
                            </span>
                        @endif
                    @endif

                    {{-- Badge: TIPO --}}
                    @if (request('type'))
                        <span
                            class="px-3 py-1 bg-white border border-emerald-300 text-emerald-700 rounded-full text-xs font-medium">
                            Tipo: {{ request('type') }}
                            <a href="{{ route('attendances.index', array_filter(request()->except('type'))) }}"
                                class="ml-1 text-emerald-600 hover:text-emerald-800">×</a>
                        </span>
                    @endif

                    {{-- Badge: ESTADO --}}
                    @if (request('status'))
                        <span
                            class="px-3 py-1 bg-white border border-emerald-300 text-emerald-700 rounded-full text-xs font-medium">
                            Estado: {{ request('status') }}
                            <a href="{{ route('attendances.index', array_filter(request()->except('status'))) }}"
                                class="ml-1 text-emerald-600 hover:text-emerald-800">×</a>
                        </span>
                    @endif

                    <a href="{{ route('attendances.index') }}"
                        class="ml-auto px-3 py-1 bg-emerald-600 text-white rounded-full text-xs font-medium hover:bg-emerald-700 transition">
                        Limpiar todos los filtros
                    </a>
                </div>
            </div>
        @endif

        {{-- TABLA DE ASISTENCIAS --}}
        <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-emerald-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">DNI</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Personal</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Entrada</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Salida</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Estado</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Notas</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-emerald-50/40 transition">
                            <td class="px-4 py-3 text-slate-500 font-mono">{{ $att->user->dni }}</td>
                            <td class="px-4 py-3 text-slate-700 font-medium">
                                {{ $att->user->firstname }} {{ $att->user->lastname }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">
                                {{ \Carbon\Carbon::parse($att->date)->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                    <i class="fa-solid fa-arrow-right-to-bracket mr-1"></i>
                                    {{ \Carbon\Carbon::parse($att->check_in)->format('H:i') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($att->check_out)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                        <i class="fa-solid fa-arrow-right-from-bracket mr-1"></i>
                                        {{ \Carbon\Carbon::parse($att->check_out)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">
                                        <i class="fa-solid fa-clock mr-1"></i>
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @switch($att->status)
                                    @case('PRESENTE')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">
                                            PRESENTE
                                        </span>
                                    @break

                                    @case('TARDANZA')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">
                                            TARDANZA
                                        </span>
                                    @break

                                    @case('AUSENTE')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                            AUSENTE
                                        </span>
                                    @break
                                @endswitch
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-xs max-w-xs truncate">
                                {{ $att->notes ?? '—' }}
                            </td>
                            <td class="px-4 py-3 flex justify-center gap-2">
                                {{-- Botón Editar --}}
                                <a href="{{ route('attendances.edit', $att->id) }}" data-turbo-frame="modal-frame"
                                    class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200 transition"
                                    title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                {{-- Botón Eliminar --}}
                                <button type="button"
                                    class="btn-delete px-2 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition"
                                    data-id="{{ $att->id }}"
                                    data-url="{{ route('attendances.destroy', $att->id) }}" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-8 text-slate-400">
                                    <i class="fa-solid fa-inbox text-4xl mb-2"></i>
                                    <p>No hay asistencias registradas el día de hoy.</p>
                                </td>
                            </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-4">
            {{ $attendances->links() }}
        </div>
    </div>
    @include('attendances._filter')
    <script>
        function openFiltersModal() {
            document.getElementById('filtersModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeFiltersModal() {
            document.getElementById('filtersModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        document.getElementById('filtersModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeFiltersModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeFiltersModal();
        });

        function clearFilters() {
            window.location.href = '{{ route('attendances.index') }}';
        }
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            const validateDateRange = () => {
                const startDateValue = startDateInput.value;
                const endDateValue = endDateInput.value;

                endDateInput.min = startDateValue;

                if (startDateValue && endDateValue && endDateValue < startDateValue) {
                    endDateInput.value = startDateValue;
                }
            };

            startDateInput.addEventListener('change', validateDateRange);

            validateDateRange();
        });
    </script>
@endsection
