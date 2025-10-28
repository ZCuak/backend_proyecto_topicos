<div class="space-y-4">
    {{-- INFORMACIÓN GENERAL --}}
    <div class="bg-slate-50 rounded-lg p-4">
        <h4 class="font-semibold text-slate-700 mb-3 flex items-center">
            <i class="fa-solid fa-info-circle mr-2"></i>Información General
        </h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-slate-500">ID:</span>
                <span class="font-medium">#{{ $scheduling->id }}</span>
            </div>
            <div>
                <span class="text-slate-500">Fecha:</span>
                <span class="font-medium">{{ is_string($scheduling->date) ? \Carbon\Carbon::parse($scheduling->date)->format('d/m/Y') : $scheduling->date->format('d/m/Y') }}</span>
            </div>
            <div>
                <span class="text-slate-500">Horario:</span>
                <span class="font-medium">{{ $scheduling->schedule->name ?? 'Sin horario' }}</span>
            </div>
            <div>
                <span class="text-slate-500">Grupo:</span>
                <span class="font-medium">{{ $scheduling->group->name ?? 'Sin grupo' }}</span>
            </div>
            @if($scheduling->vehicle)
            <div>
                <span class="text-slate-500">Vehículo:</span>
                <span class="font-medium">{{ $scheduling->vehicle->name }}</span>
            </div>
            @endif
            @if($scheduling->zone)
            <div>
                <span class="text-slate-500">Zona:</span>
                <span class="font-medium">{{ $scheduling->zone->name }}</span>
            </div>
            @endif
        </div>
        
        @php
            $statusConfig = [
                0 => ['text' => 'Pendiente', 'class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fa-clock'],
                1 => ['text' => 'En Proceso', 'class' => 'bg-blue-100 text-blue-800', 'icon' => 'fa-play'],
                2 => ['text' => 'Completado', 'class' => 'bg-green-100 text-green-800', 'icon' => 'fa-check'],
                3 => ['text' => 'Cancelado', 'class' => 'bg-red-100 text-red-800', 'icon' => 'fa-times']
            ];
            $config = $statusConfig[$scheduling->status] ?? $statusConfig[0];
        @endphp
        
        <div class="mt-3">
            <span class="text-slate-500">Estado:</span>
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $config['class'] }} ml-2">
                <i class="fa-solid {{ $config['icon'] }} mr-1"></i>
                {{ $config['text'] }}
            </span>
        </div>
    </div>

    {{-- DETALLES DEL TURNO --}}
    @if($scheduling->schedule)
        @php
            $timeStart = \Carbon\Carbon::parse($scheduling->schedule->time_start);
            $timeEnd = \Carbon\Carbon::parse($scheduling->schedule->time_end);
            $isMorning = $timeStart->hour < 12;
            $turnColor = $isMorning ? 'orange' : 'blue';
        @endphp
        
        <div class="bg-{{ $turnColor }}-50 rounded-lg p-4">
            <h4 class="font-semibold text-{{ $turnColor }}-700 mb-3 flex items-center">
                <i class="fa-solid {{ $isMorning ? 'fa-sun' : 'fa-moon' }} mr-2"></i>
                Turno {{ $isMorning ? 'Mañana' : 'Tarde' }}
            </h4>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">{{ $scheduling->schedule->name }}</span>
                    <span class="text-xs bg-{{ $turnColor }}-200 text-{{ $turnColor }}-800 px-2 py-1 rounded">
                        {{ $timeStart->format('H:i') }} - {{ $timeEnd->format('H:i') }}
                    </span>
                </div>
                @if($scheduling->schedule->description)
                    <p class="text-sm text-{{ $turnColor }}-600">{{ $scheduling->schedule->description }}</p>
                @endif
            </div>
        </div>
    @endif

    {{-- PERSONAL ASIGNADO --}}
    @if($scheduling->details->isNotEmpty())
        <div class="bg-emerald-50 rounded-lg p-4">
            <h4 class="font-semibold text-emerald-700 mb-3 flex items-center">
                <i class="fa-solid fa-users mr-2"></i>Personal Asignado
            </h4>
            <div class="space-y-2">
                @foreach($scheduling->details->sortBy('position_order') as $detail)
                    <div class="flex items-center justify-between bg-white rounded p-2">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium">{{ $detail->user->firstname }} {{ $detail->user->lastname }}</span>
                            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded">
                                {{ $detail->userType->name ?? 'Sin tipo' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500">Posición {{ $detail->position_order }}</span>
                            @php
                                $attendanceConfig = [
                                    'pendiente' => ['class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fa-clock'],
                                    'presente' => ['class' => 'bg-green-100 text-green-800', 'icon' => 'fa-check'],
                                    'ausente' => ['class' => 'bg-red-100 text-red-800', 'icon' => 'fa-times'],
                                    'justificado' => ['class' => 'bg-blue-100 text-blue-800', 'icon' => 'fa-exclamation']
                                ];
                                $attConfig = $attendanceConfig[$detail->attendance_status] ?? $attendanceConfig['pendiente'];
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $attConfig['class'] }}">
                                <i class="fa-solid {{ $attConfig['icon'] }} mr-1"></i>
                                {{ ucfirst($detail->attendance_status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- NOTAS --}}
    @if($scheduling->notes)
        <div class="bg-yellow-50 rounded-lg p-4">
            <h4 class="font-semibold text-yellow-700 mb-2 flex items-center">
                <i class="fa-solid fa-sticky-note mr-2"></i>Notas
            </h4>
            <p class="text-sm text-yellow-800">{{ $scheduling->notes }}</p>
        </div>
    @endif

    {{-- ACCIONES --}}
    <div class="flex gap-2 pt-4">
        <a href="{{ route('schedulings.show', $scheduling->id) }}"
           class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center">
            <i class="fa-solid fa-eye mr-2"></i>Ver Detalles Completos
        </a>
        <a href="{{ route('schedulings.edit', $scheduling->id) }}"
           data-turbo-frame="modal-frame"
           class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition text-center">
            <i class="fa-solid fa-pen mr-2"></i>Editar
        </a>
    </div>
</div>
