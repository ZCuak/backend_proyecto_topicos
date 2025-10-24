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
        <button class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            <i class="fa-solid fa-download"></i> Exportar reporte
        </button>
    </div>

    {{-- TARJETAS DE INDICADORES --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl p-5 shadow-md border border-slate-100 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-500">Recolección Total</h3>
                <i class="fa-solid fa-truck text-emerald-500 text-xl"></i>
            </div>
            <p class="text-3xl font-bold text-slate-800 mt-2">12.4 <span class="text-base font-normal text-slate-400">ton</span></p>
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

    {{-- DISTRIBUCIÓN DE MATERIALES --}}
    <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-pie text-indigo-500"></i>
            Distribución de materiales reciclados
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
            <canvas id="materialChart" height="180"></canvas>
            <ul class="space-y-3 text-sm text-slate-600">
                <li><span class="inline-block w-3 h-3 bg-emerald-400 rounded-full mr-2"></span> Plástico — 45%</li>
                <li><span class="inline-block w-3 h-3 bg-blue-400 rounded-full mr-2"></span> Vidrio — 20%</li>
                <li><span class="inline-block w-3 h-3 bg-amber-400 rounded-full mr-2"></span> Papel y Cartón — 25%</li>
                <li><span class="inline-block w-3 h-3 bg-gray-400 rounded-full mr-2"></span> Otros — 10%</li>
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
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                x: { grid: { display: false } }
            }
        }
    });

    const matCtx = document.getElementById('materialChart');
    new Chart(matCtx, {
        type: 'doughnut',
        data: {
            labels: ['Plástico', 'Vidrio', 'Papel y Cartón', 'Otros'],
            datasets: [{
                data: [45, 20, 25, 10],
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#9ca3af'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            cutout: '70%'
        }
    });
});
</script>
@endsection
