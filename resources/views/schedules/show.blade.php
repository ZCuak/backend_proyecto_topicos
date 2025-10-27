@extends('layouts.app')
@section('title', 'Detalle del Turno')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">{{ $schedule->name }}</h2>
            <p class="text-sm text-slate-500">{{ $schedule->description }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('schedules.edit', $schedule->id) }}" data-turbo-frame="modal-frame" class="px-3 py-2 bg-yellow-100 text-yellow-700 rounded-md">Editar</a>
            <a href="{{ route('schedules.index') }}" class="px-3 py-2 bg-slate-100 text-slate-700 rounded-md">Volver</a>
        </div>
    </div>

    <div class="bg-white rounded-xl p-6 border border-slate-100 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
            <div>
                <p class="text-slate-700"><strong>Inicio:</strong> {{ \Carbon\Carbon::parse($schedule->time_start)->format('H:i') }}</p>
                <p class="text-slate-700"><strong>Fin:</strong> {{ \Carbon\Carbon::parse($schedule->time_end)->format('H:i') }}</p>
            </div>
            <div>
                <p class="text-slate-700"><strong>Descripción:</strong></p>
                <p class="text-slate-500">{{ $schedule->description ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
