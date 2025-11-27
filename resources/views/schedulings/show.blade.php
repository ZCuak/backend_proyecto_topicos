@extends('layouts.app')
@section('title', 'Detalles de Programación — RSU Reciclaje')

@section('content')
<div class="space-y-8">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">📅 Programación #{{ $scheduling->id }}</h1>
            <p class="text-slate-500">Detalles de la programación de recolección.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('schedulings.edit', $scheduling->id) }}"
               data-turbo-frame="modal-frame"
               class="flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
               <i class="fa-solid fa-pen"></i> Editar
            </a>
            <a href="{{ route('schedulings.index') }}"
               class="flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition">
               <i class="fa-solid fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- INFORMACIÓN GENERAL --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Datos básicos --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-info-circle text-emerald-600"></i>
                    Información General
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-500">Fecha</label>
                        <p class="text-slate-800 font-medium flex items-center gap-2">
                            <i class="fa-solid fa-calendar text-emerald-600"></i>
                            {{ is_string($scheduling->date) ? \Carbon\Carbon::parse($scheduling->date)->format('d/m/Y') : $scheduling->date->format('d/m/Y') }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-slate-500">Estado</label>
                        @php
                            $statusConfig = [
                                0 => ['text' => 'Pendiente', 'class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fa-clock'],
                                1 => ['text' => 'En Proceso', 'class' => 'bg-blue-100 text-blue-800', 'icon' => 'fa-play'],
                                2 => ['text' => 'Completado', 'class' => 'bg-green-100 text-green-800', 'icon' => 'fa-check'],
                                3 => ['text' => 'Cancelado', 'class' => 'bg-red-100 text-red-800', 'icon' => 'fa-times']
                            ];
                            $config = $statusConfig[$scheduling->status] ?? $statusConfig[0];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $config['class'] }}">
                            <i class="fa-solid {{ $config['icon'] }} mr-2"></i>
                            {{ $config['text'] }}
                        </span>
                    </div>
                    
                    @if($scheduling->notes)
                    <div>
                        <label class="text-sm font-medium text-slate-500">Notas</label>
                        <p class="text-slate-800">{{ $scheduling->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Detalles de la programación --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-cogs text-emerald-600"></i>
                    Detalles de la Programación
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Grupo y Horario --}}
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-slate-500">Grupo de Empleados</label>
                            <div class="flex items-center gap-2 mt-1">
                                <i class="fa-solid fa-users text-blue-600"></i>
                                <span class="text-slate-800 font-medium">{{ $scheduling->group->name ?? '—' }}</span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-slate-500">Horario</label>
                            <div class="flex items-center gap-2 mt-1">
                                <i class="fa-solid fa-clock text-orange-600"></i>
                                <span class="text-slate-800">{{ $scheduling->schedule->name ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Vehículo y Zona --}}
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-slate-500">Vehículo</label>
                            @if($scheduling->vehicle)
                                <div class="flex items-center gap-2 mt-1">
                                    <i class="fa-solid fa-truck text-purple-600"></i>
                                    <span class="text-slate-800">{{ $scheduling->vehicle->plate }} - {{ $scheduling->vehicle->name }}</span>
                                </div>
                            @else
                                <span class="text-slate-400">No asignado</span>
                            @endif
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-slate-500">Zona</label>
                            @if($scheduling->zone)
                                <div class="flex items-center gap-2 mt-1">
                                    <i class="fa-solid fa-map-marker-alt text-red-600"></i>
                                    <span class="text-slate-800">{{ $scheduling->zone->name }}</span>
                                </div>
                            @else
                                <span class="text-slate-400">No asignada</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 🔹 EMPLEADOS ASIGNADOS --}}
    <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-users text-emerald-600"></i>
            Empleados Asignados a la Programación
        </h3>

        @if($details->isEmpty())
            <p class="text-slate-500">No hay empleados asignados a esta programación.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full border border-slate-200 text-sm text-slate-700">
                    <thead class="bg-slate-100 text-slate-600">
                        <tr>
                            <th class="px-4 py-2 border text-left">#</th>
                            <th class="px-4 py-2 border text-left">Nombre</th>
                            <th class="px-4 py-2 border text-left">DNI</th>
                            <th class="px-4 py-2 border text-left">Rol</th>
                            <th class="px-4 py-2 border text-left">Estado Asistencia</th>
                            <th class="px-4 py-2 border text-left">Notas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($details as $index => $detail)
                            @php
                                $attendance = $attendancesByUser[$detail->user_id] ?? null;
                                //dd($attendancesByUser);
                                $statusKey = $attendance ? strtoupper($attendance->status) : strtolower($detail->attendance_status ?? '');
                                $statusColors = [
                                    'PRESENTE' => 'bg-green-100 text-green-800',
                                    'AUSENTE' => 'bg-red-100 text-red-800',
                                    'TARDANZA' => 'bg-yellow-100 text-yellow-800',
                                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                                    'presente' => 'bg-green-100 text-green-800',
                                    'ausente' => 'bg-red-100 text-red-800',
                                    'justificado' => 'bg-blue-100 text-blue-800',
                                ];
                                $color = $statusColors[$statusKey] ?? 'bg-slate-100 text-slate-700';
                                $statusLabel = $attendance
                                    ? ucfirst(strtolower($attendance->status))
                                    : ($detail->attendance_status_name ?? 'Pendiente');
                            @endphp
                            <tr class="{{ $loop->even ? 'bg-slate-50' : '' }}">
                                <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                                <td class="px-4 py-2 border">{{ $detail->user->firstname ?? '' }} {{ $detail->user->lastname ?? '' }}</td>
                                <td class="px-4 py-2 border">{{ $detail->user->dni ?? '—' }}</td>
                                <td class="px-4 py-2 border">{{ $detail->role_name }}</td>
                                <td class="px-4 py-2 border">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium {{ $color }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 border">{{ $detail->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- HISTORIAL DE CAMBIOS --}}
    @if (isset($audits))
        @include('history._history_table', ['audits' => $audits])
    @endif
</div>
@endsection
