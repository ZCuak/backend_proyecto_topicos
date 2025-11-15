@if ($groupedBySchedule->isEmpty())
    {{-- Mensaje cuando está vacío --}}
    <div class="text-center py-8 text-slate-400">
        <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300"></i>
        <p class="text-xs">No hay programaciones activas/completadas</p>
    </div>
@else
    @foreach ($groupedBySchedule as $scheduleId => $zones)
        @php
            $schedule = $zones->first()['scheduling']->schedule;
        @endphp

        <div class="mb-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-sun text-amber-500"></i>
                Turno {{ $schedule->name }}
                ({{ Carbon\Carbon::parse($schedule->time_start)->format('H:i') }} -
                {{ Carbon\Carbon::parse($schedule->time_end)->format('H:i') }})
            </h3>

            {{-- EN PROCESO --}}
            @php
                $inProcessZones = $zones->where('scheduling.status', 1);
            @endphp

            @if ($inProcessZones->count() > 0)
                <div class="mb-4">
                    <p class="text-xs font-semibold text-purple-600 mb-2 flex items-center gap-1">
                        <i class="fa-solid fa-spinner"></i>
                        En Proceso ({{ $inProcessZones->count() }})
                    </p>
                    <div class="space-y-2">
                        @foreach ($inProcessZones as $zoneData)
                            {{-- Tarjeta EN PROCESO --}}
                            <div class="bg-white rounded-lg shadow-sm border-2 border-purple-400 p-3">
                                <div class="mb-2">
                                    <p class="font-bold text-slate-800 text-sm">
                                        {{ $zoneData['scheduling']->zone->name }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $zoneData['scheduling']->vehicle->plate }}</p>
                                </div>
                                <div class="mb-2 p-2 bg-purple-50 rounded text-center">
                                    <p class="text-xs text-purple-700 font-medium">
                                        <i class="fa-solid fa-clock mr-1"></i>
                                        En proceso desde
                                        {{ $zoneData['scheduling']->updated_at->format('H:i') }}
                                    </p>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button"
                                        onclick="completeScheduling({{ $zoneData['scheduling']->id }})"
                                        class="flex items-center justify-center gap-1 px-2 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded hover:bg-emerald-700 transition cursor-pointer">
                                        <i class="fa-solid fa-check"></i>
                                        Completar
                                    </button>
                                    <button type="button" onclick="cancelScheduling({{ $zoneData['scheduling']->id }})"
                                        class="flex items-center justify-center gap-1 px-2 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition cursor-pointer">
                                        <i class="fa-solid fa-xmark"></i>
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- COMPLETADAS --}}
            @php
                $completedZones = $zones->where('scheduling.status', 2);
            @endphp

            @if ($completedZones->count() > 0)
                <div>
                    <p class="text-xs font-semibold text-blue-600 mb-2 flex items-center gap-1">
                        <i class="fa-solid fa-check-circle"></i>
                        Completadas ({{ $completedZones->count() }})
                    </p>
                    <div class="space-y-2">
                        @foreach ($completedZones as $zoneData)
                            {{-- Tarjeta COMPLETADA --}}
                            <div class="bg-white rounded-lg shadow-sm border-2 border-blue-400 p-3">
                                <div class="mb-2">
                                    <p class="font-bold text-slate-800 text-sm">
                                        {{ $zoneData['scheduling']->zone->name }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $zoneData['scheduling']->vehicle->plate }}</p>
                                </div>
                                <div class="p-2 bg-blue-50 rounded text-center">
                                    <p class="text-xs text-blue-700 font-medium">
                                        <i class="fa-solid fa-check-circle mr-1"></i>
                                        Completada a las
                                        {{ $zoneData['scheduling']->updated_at->format('H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endforeach
@endif
