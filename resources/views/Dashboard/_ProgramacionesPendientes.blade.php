@if ($pendingZones->isEmpty())
    <div class="text-center py-12 text-slate-400">
        <i class="fa-solid fa-calendar-xmark text-5xl mb-3 text-slate-300"></i>
        <p class="text-lg font-medium">No hay programaciones pendientes</p>
    </div>
@else
    {{-- 2 COLUMNAS × N FILAS --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        @foreach ($pendingZones as $zoneData)
            <div class="zone-card bg-white rounded-xl shadow-sm border-2 p-5 hover:shadow-md transition
                                    {{ $zoneData['status'] === 'ready' ? 'border-emerald-400' : 'border-amber-400' }}"
                data-status="{{ $zoneData['status'] }}">

                {{-- Header de Zona --}}
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">
                            Zona: {{ $zoneData['scheduling']->zone->name }}
                        </h3>
                        <p class="text-sm text-slate-500">
                            {{ $zoneData['scheduling']->schedule->name }} -
                            {{ $zoneData['scheduling']->vehicle->plate }}
                        </p>
                    </div>
                    <div
                        class="w-3 h-3 rounded-full {{ $zoneData['status'] === 'ready' ? 'bg-emerald-500' : 'bg-amber-500' }}">
                    </div>
                </div>

                {{-- Estado --}}
                <div
                    class="mb-4 p-3 rounded-lg {{ $zoneData['status'] === 'ready' ? 'bg-emerald-50' : 'bg-amber-50' }}">
                    <p
                        class="text-sm font-semibold {{ $zoneData['status'] === 'ready' ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $zoneData['reason'] }}
                    </p>
                </div>

                {{-- Personal Ausente --}}
                @if ($zoneData['status'] === 'not_ready' && !empty($zoneData['absent_personnel']))
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-slate-600 mb-2">Personal faltante:</p>
                        <ul class="space-y-1">
                            @foreach ($zoneData['absent_personnel'] as $absent)
                                <li class="text-xs text-slate-600 flex items-start gap-2">
                                    <i class="fa-solid fa-user-xmark text-amber-500 mt-0.5"></i>
                                    <span>
                                        {{ $absent['user']->firstname }}
                                        {{ $absent['user']->lastname }}
                                        <span class="text-slate-400">({{ $absent['role'] }})</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Botón Realizar Cambios --}}
                    <a href="{{ route('schedulings.edit', $zoneData['scheduling']->id) }}?date={{ $zoneData['date'] }}"
                        data-turbo-frame="modal-frame"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600 transition">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        Realizar cambios
                    </a>
                @else
                    {{-- Texto "Listo para operar" --}}
                    <div class="mb-3 text-center">
                        <p class="text-emerald-600 font-semibold flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-check text-lg"></i>
                            Listo para operar
                        </p>
                    </div>

                    {{-- Botón INICIAR PROGRAMACIÓN --}}
                    <button type="button" onclick="startScheduling({{ $zoneData['scheduling']->id }})"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition cursor-pointer">
                        <i class="fa-solid fa-play"></i>
                        INICIAR PROGRAMACIÓN
                    </button>
                @endif
            </div>
        @endforeach
    </div>
@endif
