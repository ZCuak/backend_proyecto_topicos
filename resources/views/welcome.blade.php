@extends('layouts.app')

@section('title', 'Dashboard — RSU Reciclaje')

@section('content')
    <div class="space-y-8">

        {{-- ENCABEZADO --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Panel de Control RSU ♻️</h1>
                <p class="text-slate-500">Monitoreo general de operaciones, rutas y reciclaje urbano sostenible.</p>
            </div>
            <button
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                <i class="fa-solid fa-download"></i> Exportar reporte
            </button>
        </div>

        {{-- FILTROS: FECHA Y TURNO --}}
        <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col sm:flex-row gap-4 items-end">

                {{-- Filtro de Fecha --}}
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <i class="fa-solid fa-calendar-day text-emerald-600 mr-1"></i>
                        Seleccione una fecha:
                    </label>
                    <input type="date" name="date" value="{{ $selectedDate }}" min="{{ now()->format('Y-m-d') }}"
                        class="w-full px-4 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                {{-- Filtro de Turno --}}
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        <i class="fa-solid fa-clock text-emerald-600 mr-1"></i>
                        Seleccione un turno:
                    </label>
                    <select name="schedule_id"
                        class="w-full px-4 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todos los turnos</option>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->id }}"
                                {{ $selectedScheduleId == $schedule->id ? 'selected' : '' }}>
                                {{ $schedule->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Botón Buscar --}}
                <div>
                    <button type="submit"
                        class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition shadow-md">
                        <i class="fa-solid fa-search mr-2"></i>
                        Buscar programación
                    </button>
                </div>
            </form>
        </div>

        {{-- ESTADÍSTICAS --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Total Zonas Programadas --}}
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Zonas</p>
                        <p class="text-3xl font-bold text-blue-700 mt-1">{{ $stats['total_zones'] }}</p>
                    </div>
                    <div class="bg-blue-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-clipboard-list text-2xl text-blue-700"></i>
                    </div>
                </div>
            </div>

            {{-- Grupos Completos --}}
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-5 border border-emerald-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-emerald-600 font-medium">Grupos completos</p>
                        <p class="text-3xl font-bold text-emerald-700 mt-1">{{ $stats['ready_zones'] }}</p>
                    </div>
                    <div class="bg-emerald-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-truck text-2xl text-emerald-700"></i>
                    </div>
                </div>
            </div>

            {{-- Apoyos Disponibles --}}
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium">Apoyos disponibles</p>
                        <p class="text-3xl font-bold text-purple-700 mt-1">0</p>
                    </div>
                    <div class="bg-purple-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-user text-2xl text-purple-700"></i>
                    </div>
                </div>
            </div>

            {{-- Personal Ausente --}}
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-5 border border-red-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-600 font-medium">Faltan</p>
                        <p class="text-3xl font-bold text-red-700 mt-1">{{ $stats['absent_personnel'] }}</p>
                    </div>
                    <div class="bg-red-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-2xl text-red-700"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- LEYENDA DE COLORES --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="font-semibold text-blue-700 mb-2">
                <i class="fa-solid fa-info-circle mr-1"></i>
                Leyenda de colores:
            </p>
            <div class="flex flex-col sm:flex-row gap-3 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-emerald-500 rounded"></div>
                    <span class="text-slate-700">Grupo completo y listo para operar</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-red-500 rounded"></div>
                    <span class="text-slate-700">Faltan integrantes por llegar o confirmar asistencia</span>
                </div>
            </div>
        </div>

        {{-- TARJETAS DE ZONAS --}}
        @if ($zonesData->isEmpty())
            <div class="bg-white rounded-xl shadow-md border border-slate-100 p-12 text-center">
                <i class="fa-solid fa-calendar-xmark text-5xl text-slate-300 mb-3"></i>
                <p class="text-lg font-medium text-slate-600">No hay programaciones para esta fecha y turno</p>
                <p class="text-sm text-slate-400">Intenta seleccionar otra fecha o turno</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($zonesData as $zoneData)
                    <div
                        class="bg-white rounded-xl shadow-md border-2 
                    {{ $zoneData['status'] === 'ready' ? 'border-emerald-200' : 'border-red-200' }} 
                    p-6 hover:shadow-lg transition">

                        {{-- Encabezado de Zona --}}
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">
                                    Zona: {{ $zoneData['scheduling']->zone->name }}
                                </h3>
                                <p class="text-sm text-slate-500">
                                    {{ $zoneData['scheduling']->schedule->name }} -
                                    {{ $zoneData['scheduling']->vehicle->plate }}
                                </p>
                            </div>
                            <div
                                class="w-3 h-3 rounded-full {{ $zoneData['status'] === 'ready' ? 'bg-emerald-500' : 'bg-red-500' }}">
                            </div>
                        </div>

                        {{-- Estado --}}
                        <div
                            class="mb-4 p-3 rounded-lg {{ $zoneData['status'] === 'ready' ? 'bg-emerald-50' : 'bg-red-50' }}">
                            <p
                                class="text-sm font-medium {{ $zoneData['status'] === 'ready' ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ $zoneData['reason'] }}
                            </p>
                        </div>

                        {{-- Personal Ausente --}}
                        @if ($zoneData['status'] === 'not_ready')
                            <div class="mb-4">
                                <p class="text-xs font-semibold text-slate-600 mb-2">Personal faltante:</p>
                                <ul class="space-y-1">
                                    @foreach ($zoneData['absent_personnel'] as $absent)
                                        <li class="text-xs text-slate-600 flex items-center gap-2">
                                            <i class="fa-solid fa-user-xmark text-red-500"></i>
                                            {{ $absent['user']->firstname }} {{ $absent['user']->lastname }}
                                            <span class="text-slate-400">({{ $absent['role'] }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            {{-- Botón para Editar --}}
                            <a href="{{ route('schedulings.edit', ['scheduling' => $zoneData['scheduling']->id, 'date' => $zoneData['date']]) }}"
                                data-turbo-frame="modal-frame" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition">
                                <i class="fa-solid fa-arrows-rotate"></i> Realizar cambios
                            </a>
                        @else
                            <div class="flex items-center justify-center gap-2 text-emerald-600 py-2">
                                <i class="fa-solid fa-circle-check text-xl"></i>
                                <span class="font-medium">Listo para operar</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- TARJETAS DE INDICADORES --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl p-5 shadow-md border border-slate-100 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-500">Recolección Total</h3>
                    <i class="fa-solid fa-truck text-emerald-500 text-xl"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2">12.4 <span
                        class="text-base font-normal text-slate-400">ton</span></p>
                <p class="text-xs text-emerald-600 font-medium mt-1">+8% respecto al mes pasado</p>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-md border border-slate-100 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-500">Vehículos Activos</h3>
                    <i class="fa-solid fa-truck-fast text-blue-500 text-xl"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2">18</p>
                <p class="text-xs text-blue-600 font-medium mt-1">4 en mantenimiento</p>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-md border border-slate-100 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-500">Zonas Cubiertas</h3>
                    <i class="fa-solid fa-map-location-dot text-yellow-500 text-xl"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2">32</p>
                <p class="text-xs text-yellow-600 font-medium mt-1">+3 nuevas zonas este mes</p>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-md border border-slate-100 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-500">Tasa de Reciclaje</h3>
                    <i class="fa-solid fa-leaf text-green-500 text-xl"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2">78%</p>
                <p class="text-xs text-green-600 font-medium mt-1">Objetivo: 85% al cierre del trimestre</p>
            </div>
        </div>

        {{-- SECCIÓN PRINCIPAL --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        </div>

        {{-- DISTRIBUCIÓN DE MATERIALES --}}
        {{-- <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6"> </div> --}}

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- GRAFICO DE RECOLECCIÓN --}}

            <div class="lg:col-span-2 bg-white rounded-xl shadow-md border border-slate-100 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-emerald-500"></i>
                    Progreso de recolección mensual
                </h2>
                <canvas id="recoleccionChart" height="120"></canvas>
            </div>

            {{-- ACTIVIDAD RECIENTE --}}
            <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-bell text-amber-500"></i>
                    Últimas alertas
                </h2>
                <ul class="divide-y divide-slate-100">
                    <li class="py-3">
                        <p class="text-sm font-medium text-slate-700">🚛 Vehículo N°14 inició ruta</p>
                        <span class="text-xs text-slate-400">Hace 10 min</span>
                    </li>
                    <li class="py-3">
                        <p class="text-sm font-medium text-slate-700">⚙️ Mantenimiento programado — Unidad 7</p>
                        <span class="text-xs text-slate-400">Hace 1 hora</span>
                    </li>
                    <li class="py-3">
                        <p class="text-sm font-medium text-slate-700">📦 Centro Zaña completó recolección</p>
                        <span class="text-xs text-slate-400">Hace 2 horas</span>
                    </li>
                    <li class="py-3">
                        <p class="text-sm font-medium text-slate-700">🌿 1.3 ton recicladas hoy</p>
                        <span class="text-xs text-slate-400">Hace 4 horas</span>
                    </li>
                </ul>
            </div>
        </div>

    </div>

    {{-- GRÁFICOS CHART.JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('recoleccionChart');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago'],
                    datasets: [{
                        label: 'Toneladas recolectadas',
                        data: [8.2, 9.1, 10.4, 9.8, 11.2, 12.0, 12.4, 12.9],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        tension: 0.4,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f0f0f0'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
