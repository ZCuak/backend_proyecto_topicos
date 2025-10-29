@extends('layouts.app')
@section('title', 'Calendario de Programaciones — RSU Reciclaje')

@section('content')
<div class="space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">📅 Calendario de Programaciones</h1>
            <p class="text-slate-500">Visualiza las programaciones organizadas por turnos (mañana y tarde).</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('schedulings.index') }}"
               class="flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition">
               <i class="fa-solid fa-table"></i> Vista de Tabla
            </a>

            <a href="{{ route('schedulings.create') }}"
               data-turbo-frame="modal-frame"
               class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
               <i class="fa-solid fa-plus"></i> Nueva Programación
            </a>

            <a href="{{ route('schedulings.create-massive') }}"
               data-turbo-frame="modal-frame"
               class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
               <i class="fa-solid fa-calendar-plus"></i> Programación Masiva
            </a>
        </div>
    </div>

    {{-- NAVEGACIÓN DEL CALENDARIO --}}
    <div class="bg-white rounded-xl shadow-md border border-slate-100 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h2 class="text-2xl font-bold text-slate-800">
                    {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                </h2>

                <div class="flex gap-2">
                    <a href="{{ route('schedulings.calendar', ['year' => $month == 1 ? $year - 1 : $year, 'month' => $month == 1 ? 12 : $month - 1]) }}"
                       class="px-3 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>

                    <a href="{{ route('schedulings.calendar', ['year' => now()->year, 'month' => now()->month]) }}"
                       class="px-3 py-2 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 transition">
                        Hoy
                    </a>

                    <a href="{{ route('schedulings.calendar', ['year' => $month == 12 ? $year + 1 : $year, 'month' => $month == 12 ? 1 : $month + 1]) }}"
                       class="px-3 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </div>
            </div>

            {{-- LEYENDA DE TURNOS --}}
            <div class="flex items-center gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-orange-200 rounded"></div>
                    <span class="text-slate-600">Turno Mañana</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-blue-200 rounded"></div>
                    <span class="text-slate-600">Turno Tarde</span>
                </div>
            </div>
        </div>
    </div>

    {{-- CALENDARIO --}}
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">
        {{-- CABECERA DE DÍAS DE LA SEMANA --}}
        <div class="grid grid-cols-7 bg-slate-50 border-b border-slate-200">
            @php
                $daysOfWeek = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
            @endphp
            @foreach($daysOfWeek as $day)
                <div class="p-3 text-center font-semibold text-slate-600 text-sm">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        {{-- DÍAS DEL CALENDARIO --}}
        <div class="grid grid-cols-7">
            @foreach($calendarDays as $day)
                <div class="min-h-[120px] border-r border-b border-slate-200 p-2 {{ $day['isCurrentMonth'] ? 'bg-white' : 'bg-slate-50' }} {{ $day['isToday'] ? 'bg-emerald-50 border-emerald-200' : '' }}">
                    {{-- NÚMERO DEL DÍA --}}
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium {{ $day['isCurrentMonth'] ? 'text-slate-700' : 'text-slate-400' }} {{ $day['isToday'] ? 'text-emerald-700 font-bold' : '' }}">
                            {{ $day['date']->format('j') }}
                        </span>

                        @if($day['isCurrentMonth'] && $day['date']->isToday())
                            <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                        @endif
                    </div>

                    {{-- PROGRAMACIONES --}}
                    <div class="space-y-1">
                        @if(!empty($day['schedulings']))
                            @php
                                $morningSchedules = [];
                                $afternoonSchedules = [];

                                foreach($day['schedulings'] as $scheduleId => $schedulings) {
                                    $schedule = $schedulings[0]->schedule;
                                    $timeStart = \Carbon\Carbon::parse($schedule->time_start);

                                    // Lógica simplificada:
                                    // Turno mañana: inicio antes de las 13:00 (1:00 PM)
                                    // Turno tarde: inicio a las 13:00 o después
                                    $isMorning = $timeStart->hour < 13;

                                    if ($isMorning) {
                                        $morningSchedules[$scheduleId] = $schedulings;
                                    } else {
                                        $afternoonSchedules[$scheduleId] = $schedulings;
                                    }
                                }
                            @endphp

                            {{-- TURNO MAÑANA --}}
                            @if(!empty($morningSchedules))
                                <div class="bg-orange-100 border border-orange-200 rounded p-1">
                                    <div class="text-xs font-medium text-orange-800 mb-1">
                                        <i class="fa-solid fa-sun mr-1"></i>Mañana
                                    </div>
                                    @foreach($morningSchedules as $scheduleId => $schedulings)
                                        <div class="text-xs text-orange-700 cursor-pointer hover:bg-orange-200 rounded p-1 mb-1 transition"
                                             data-scheduling-id="{{ $schedulings[0]->id }}"
                                             onclick="showSchedulingDetails(this.dataset.schedulingId)">
                                            <div class="font-medium truncate">
                                                {{ $schedulings[0]->schedule->name }}
                                            </div>
                                            <div class="truncate">
                                                {{ $schedulings[0]->group->name ?? 'Sin grupo' }}
                                            </div>
                                            @if(count($schedulings) > 1)
                                                <div class="text-xs opacity-75">
                                                    +{{ count($schedulings) - 1 }} más
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- TURNO TARDE --}}
                            @if(!empty($afternoonSchedules))
                                <div class="bg-blue-100 border border-blue-200 rounded p-1">
                                    <div class="text-xs font-medium text-blue-800 mb-1">
                                        <i class="fa-solid fa-moon mr-1"></i>Tarde
                                    </div>
                                    @foreach($afternoonSchedules as $scheduleId => $schedulings)
                                        <div class="text-xs text-blue-700 cursor-pointer hover:bg-blue-200 rounded p-1 mb-1 transition"
                                             data-scheduling-id="{{ $schedulings[0]->id }}"
                                             onclick="showSchedulingDetails(this.dataset.schedulingId)">
                                            <div class="font-medium truncate">
                                                {{ $schedulings[0]->schedule->name }}
                                            </div>
                                            <div class="truncate">
                                                {{ $schedulings[0]->group->name ?? 'Sin grupo' }}
                                            </div>
                                            @if(count($schedulings) > 1)
                                                <div class="text-xs opacity-75">
                                                    +{{ count($schedulings) - 1 }} más
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- MODAL DE DETALLES --}}
    <div id="schedulingModal" class="fixed inset-0 bg-transparent hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-slate-800">Detalles de Programación</h3>
                        <button onclick="closeSchedulingModal()" class="text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-times text-xl"></i>
                        </button>
                    </div>

                    <div id="schedulingDetails">
                        {{-- Contenido cargado dinámicamente --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showSchedulingDetails(schedulingId) {
    // Mostrar modal
    document.getElementById('schedulingModal').classList.remove('hidden');

    // Mostrar loading
    document.getElementById('schedulingDetails').innerHTML = `
        <div class="text-center py-8">
            <i class="fa-solid fa-spinner fa-spin text-2xl text-slate-400 mb-2"></i>
            <p class="text-slate-500">Cargando detalles...</p>
        </div>
    `;

    // Cargar detalles desde el servidor
    fetch(`/schedulings/${schedulingId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('schedulingDetails').innerHTML = data.html;
            } else {
                document.getElementById('schedulingDetails').innerHTML = `
                    <div class="text-center py-8">
                        <i class="fa-solid fa-exclamation-triangle text-2xl text-red-400 mb-2"></i>
                        <p class="text-red-500">Error al cargar los detalles</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('schedulingDetails').innerHTML = `
                <div class="text-center py-8">
                    <i class="fa-solid fa-exclamation-triangle text-2xl text-red-400 mb-2"></i>
                    <p class="text-red-500">Error al cargar los detalles</p>
                </div>
            `;
        });
}

function closeSchedulingModal() {
    document.getElementById('schedulingModal').classList.add('hidden');
}

// Cerrar modal al hacer clic fuera de él
document.getElementById('schedulingModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSchedulingModal();
    }
});
</script>
@endsection
