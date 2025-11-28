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
                    <input type="date" name="date" value="{{ $selectedDate }}"
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
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            {{-- Total Zonas Programadas --}}
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Zonas</p>
                        <p class="text-3xl font-bold text-blue-700 mt-1" data-stat="total_zones">
                            {{ $stats['total_zones'] }}
                        </p>
                    </div>
                    <div class="bg-blue-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-clipboard-list text-2xl text-blue-700"></i>
                    </div>
                </div>
            </div>

            {{-- Grupos Completos --}}
            <button type="button" onclick="filterZones('ready')"
                class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-5 border-2 border-emerald-200 hover:border-emerald-400 hover:scale-105 transition-all duration-200 cursor-pointer text-left">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-emerald-600 font-medium">Grupos completos</p>
                        <p class="text-3xl font-bold text-emerald-700 mt-1" data-stat="ready_zones">
                            {{ $stats['ready_zones'] }}
                        </p>
                    </div>
                    <div class="bg-emerald-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-truck text-2xl text-emerald-700"></i>
                    </div>
                </div>
            </button>

            {{-- Personal Ausente --}}
            <button type="button" onclick="filterZones('not_ready')"
                class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-5 border-2 border-orange-200 hover:border-orange-400 hover:scale-105 transition-all duration-200 cursor-pointer text-left">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-orange-600 font-medium">Empleados Faltantes</p>
                        <p class="text-3xl font-bold text-orange-700 mt-1" data-stat="absent_personnel">
                            {{ $stats['absent_personnel'] }} 
                        </p>
                    </div>
                    <div class="bg-orange-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-2xl text-orange-700"></i>
                    </div>
                </div>
            </button>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium">En Recorrido</p>
                        <p class="text-3xl font-bold text-purple-700 mt-1" data-stat="in_process">
                            {{ $stats['in_process'] }}
                        </p>
                    </div>
                    <div class="bg-purple-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-spinner text-2xl text-purple-700"></i>
                    </div>
                </div>
            </div>

            {{-- Apoyos Disponibles --}}
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium">Apoyos disponibles</p>
                        <p class="text-3xl font-bold text-purple-700 mt-1">{{ $stats['apoyos_disponibles'] }}</p>
                    </div>
                    <div class="bg-purple-200 w-14 h-14 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-user text-2xl text-purple-700"></i>
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
                    <div class="w-4 h-4 bg-orange-500 rounded"></div>
                    <span class="text-slate-700">Faltan integrantes por confirmar asistencia</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-purple-500 rounded"></div>
                    <span class="text-slate-700">En proceso de recolección</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-blue-500 rounded"></div>
                    <span class="text-slate-700">Completada exitosamente</span>
                </div>
            </div>
        </div>

        {{-- LAYOUT DE DOS COLUMNAS --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- COLUMNA IZQUIERDA: PENDIENTES (8/12) --}}
            <div class="lg:col-span-9">
                <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-clock text-blue-600"></i>
                            Programaciones
                        </h2>
                    </div>

                    <div class="p-6 max-h-[600px] overflow-y-auto" id="pendingZonesContainer">
                        @include('Dashboard._ProgramacionesPendientes')
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: EN CURSO Y COMPLETADAS (4/12) --}}
            <div class="lg:col-span-3">
                <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">
                    <div class="bg-slate-50 px-4 py-3 border-b border-slate-200">
                        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-chart-line text-purple-600"></i>
                            En Recorrido y Finalizadas
                        </h2>
                    </div>

                    <div class="p-4 max-h-[600px] overflow-y-auto" id="activeCompletedZonesContainer">
                        @include('Dashboard._ProgramacionesOtras')
                    </div>
                </div>
            </div>
        </div>


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

        {{-- <div class="grid grid-cols-1 lg:grid-cols-3 gap-6"> --}}
            {{-- GRAFICO DE RECOLECCIÓN --}}

            {{-- <div class="lg:col-span-2 bg-white rounded-xl shadow-md border border-slate-100 p-6">
                <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-emerald-500"></i>
                    Progreso de recolección mensual
                </h2>
                <canvas id="recoleccionChart" height="120"></canvas>
            </div> --}}

            {{-- ACTIVIDAD RECIENTE --}}
            {{-- <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
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
            </div> --}}
        {{-- </div> --}}

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

            const statsElements = document.querySelectorAll('[data-stat]');

            statsElements.forEach(element => {

                const finalValue = parseInt(element.textContent.trim()) || 0;
                element.textContent = '0';
                animateNumber(element, finalValue);
            });
        });

        const scrollingElement = document.getElementById('mainContent');

        function filterZones(status) {
            const cards = document.querySelectorAll('.zone-card');

            cards.forEach(card => {
                const cardStatus = card.dataset.status;

                if (status === 'all') {
                    card.style.display = '';
                } else if (cardStatus === status) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function getFormFilters() {
            const dateInput = document.querySelector('input[name="date"]');
            const scheduleSelect = document.querySelector('select[name="schedule_id"]');

            return {
                date: dateInput ? dateInput.value : '',
                schedule_id: scheduleSelect ? scheduleSelect.value : ''
            };
        }

        // 🎨 Animación fade out
        function fadeOut(element) {
            return new Promise(resolve => {
                element.style.transition = 'opacity 0.3s ease-out';
                element.style.opacity = '0';
                setTimeout(() => resolve(), 300);
            });
        }

        // Animación de número (contador)
        function animateNumber(element, newValue) {
            const currentValue = parseInt(element.textContent) || 0;
            if (newValue === currentValue) {
                return;
            }
            const animationStartValue = 0;
            const difference = newValue - animationStartValue;
            if (difference === 0) {
                element.textContent = 0;
                return;
            }
            const steps = Math.abs(difference);
            const duration = 500; // ms
            const stepValue = difference / steps;
            const stepDuration = duration / steps;

            let currentStep = 0;

            const interval = setInterval(() => {
                currentStep++;
                const value = Math.round(animationStartValue + (stepValue * currentStep));
                element.textContent = value;

                if (currentStep >= steps) {
                    element.textContent = newValue;
                    clearInterval(interval);
                }
            }, stepDuration);
        }

        // 🎨 Animación fade in
        function fadeIn(element) {
            return new Promise(resolve => {
                element.style.opacity = '0';
                setTimeout(() => {
                    element.style.transition = 'opacity 0.3s ease-in';
                    element.style.opacity = '1';
                    setTimeout(() => resolve(), 300);
                }, 50);
            });
        }

        async function reloadSection(containerId, endpoint) {
            try {
                const container = document.getElementById(containerId);
                if (!container) return;

                // 🎨 Fade out
                await fadeOut(container);

                // 📡 Fetch data
                const filters = getFormFilters();
                // Crea un objeto URL
                const url = new URL(endpoint, window.location
                    .origin); // window.location.origin ayuda si el endpoint es relativo

                // Añade los parámetros de forma segura (esto los codifica si es necesario)
                url.searchParams.append('date', filters.date);
                url.searchParams.append('schedule_id', filters.schedule_id)

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });

                if (!response.ok) throw new Error(`Error al cargar ${endpoint}`);

                const html = await response.text();
                container.innerHTML = html;

                // 🎨 Fade in
                await fadeIn(container);

            } catch (error) {
                console.error('Error:', error);
                throw error;
            }
        }

        async function updateStats() {
            try {
                const filters = getFormFilters();
                // Crea un objeto URL
                const url = new URL('/dashboard/stats', window.location.origin);

                // Añade los parámetros de forma segura (esto los codifica si es necesario)
                url.searchParams.append('date', filters.date);
                url.searchParams.append('schedule_id', filters.schedule_id)

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Error al cargar estadísticas');

                const stats = await response.json();

                // Actualizar cada estadística con animación
                const statElements = {
                    'total_zones': stats.total_zones,
                    'ready_zones': stats.ready_zones,
                    'in_process': stats.in_process,
                    'absent_personnel': stats.absent_personnel
                };

                Object.keys(statElements).forEach(key => {
                    const newValue = statElements[key];
                    const element = document.querySelector(`[data-stat="${key}"]`);

                    if (element) {

                        animateNumber(element, statElements[key]);
                    }
                });
            } catch (error) {
                console.error('Error al actualizar estadísticas:', error);
            }
        }

        async function reloadPendingProg() {
            // pendingZonesContainer /dashboard/programacion-pendiente
            return reloadSection('pendingZonesContainer', '/dashboard/programacion-pendiente');
        }
        async function reloadOtrasProg() {
            // activeCompletedZonesContaine /dashboard/programaciones-otras
            return reloadSection('activeCompletedZonesContainer', '/dashboard/programaciones-otras');
        }

        async function changeSchedulingStatus(schedulingId, newStatus, title, text) {
            const currentScrollPosition = scrollingElement ? scrollingElement.scrollTop : 0;

            const result = await Swal.fire({
                title: title,
                text: text,
                icon: newStatus === 3 ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonColor: newStatus === 3 ? '#ef4444' : '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar',
                heightAuto: false,
                didOpen: () => {
                    // Mantener scroll en la posición original
                    if (scrollingElement) scrollingElement.scrollTo(0, currentScrollPosition);
                },
                didClose: () => {
                    // Prevenir salto al CERRAR (si se cancela)
                    if (scrollingElement) scrollingElement.scrollTo(0, currentScrollPosition);
                }
            });

            if (!result.isConfirmed) {
                return;
            }

            try {
                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                        if (scrollingElement) scrollingElement.scrollTo(0, currentScrollPosition);
                    }
                });

                const response = await fetch(`/schedulings/${schedulingId}/change-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    await Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false,
                        didOpen: () => {
                            if (scrollingElement) scrollingElement.scrollTo(0, currentScrollPosition);
                        }
                    });

                    // ✅ RECARGAR SOLO LAS SECCIONES
                    await Promise.all([
                        reloadPendingProg(),
                        reloadOtrasProg(),
                        updateStats()
                    ]);


                    // 📍 Restaurar scroll después de recargar
                    setTimeout(() => {
                        if (scrollingElement) {
                            // ¡LA MAGIA ESTÁ AQUÍ!
                            scrollingElement.scrollTo({
                                top: (currentScrollPosition - 100),
                                behavior: 'smooth' // <-- Esto le dice al navegador que anime el scroll
                            });
                        }
                    }, 0);

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Ocurrió un error'
                    });
                    if (scrollingElement) scrollingElement.scrollTo(0, currentScrollPosition);
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo conectar con el servidor',
                    heightAuto: false,
                    didOpen: () => {
                        if (scrollingElement) scrollingElement.scrollTo(0, currentScrollPosition);
                    }
                });
            }
        }

        function startScheduling(schedulingId) {
            changeSchedulingStatus(
                schedulingId,
                1,
                '¿Iniciar programación?',
                'La programación cambiará a estado "En Proceso"',
                'Programación iniciada correctamente'
            );
        }

        function completeScheduling(schedulingId) {
            changeSchedulingStatus(
                schedulingId,
                2,
                '¿Completar programación?',
                'La programación se marcará como completada',
                'Programación completada exitosamente'
            );
        }

        function cancelScheduling(schedulingId) {
            changeSchedulingStatus(
                schedulingId,
                3,
                '¿Cancelar programación?',
                'Esta acción marcará la programación como cancelada',
                'Programación cancelada'
            );
        }
    </script>
@endsection
