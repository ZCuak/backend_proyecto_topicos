<div class="grid grid-cols-1 lg:grid-cols-1">

    {{-- ===========================
         🧍 DATOS PERSONALES
    ============================ --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50/60 hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-id-card text-emerald-600"></i> Datos 
        </legend>

        
        {{-- Nombres y Código --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="name"
                    value="{{ old('name', $group->name ?? '') }}"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Ej. Corolla">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Zona </label>
                <div class="relative">
                    <select name="zone_id"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}"
                                {{ old('zone_id', $group->zone_id ?? '') == $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Turno </label>
                <div class="relative">
                    <select name="schedule_id"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                        @foreach($schedules as $schedule)
                            <option value="{{ $schedule->id }}"
                                {{ old('schedule_id', $group->schedule_id ?? '') == $schedule->id ? 'selected' : '' }}>
                                {{ $schedule->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Vehículo </label>
                <div class="relative">
                    <select name="vehicle_id"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}"
                                {{ old('vehicle_id', $group->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Días de la semana --}}
        <div class="mt-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Días</label>
            <div class="flex gap-3">
                @php
                    $days = [
                        'lunes' => 'Lun',
                        'martes' => 'Mar',
                        'miercoles' => 'Mié',
                        'jueves' => 'Jue',
                        'viernes' => 'Vie',
                        'sabado' => 'Sáb',
                        'domingo' => 'Dom',
                    ];
                    $selectedDays = old('days', $group->days ?? []);
                @endphp
                @foreach($days as $key => $label)
                    <label class="inline-flex items-center space-x-2">
                        <input type="checkbox" name="days[]" value="{{ $key }}"
                            class="h-7 w-7 rounded border-slate-300 text-emerald-600 focus:ring-2 focus:ring-emerald-500 transition"
                            {{ in_array($key, $selectedDays) ? 'checked' : '' }}>
                        <span class="text-slate-700 text-sm">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Conductor <span class="text-red-500">*</span></label>
                <div class="relative">
                    <select name="driver_id" class="h-7 w-7 rounded border-slate-300 text-emerald-600 focus:ring-2 focus:ring-emerald-500 transition">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ old('driver_id', $driver_id ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->firstname.' '.$user->lastname  }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Ayudante 1 <span class="text-red-500">*</span></label>
                <div class="relative">
                    <select name="user1_id" class="h-7 w-7 rounded border-slate-300 text-emerald-600 focus:ring-2 focus:ring-emerald-500 transition">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ old('user1_id', $user1_id ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->firstname.' '.$user->lastname  }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Ayudante 2 </label>
                <div class="relative">
                    <select name="user2_id" class="h-7 w-7 rounded border-slate-300 text-emerald-600 focus:ring-2 focus:ring-emerald-500 transition">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ old('user2_id', $user2_id ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->firstname.' '.$user->lastname  }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </fieldset>
</div>

{{-- BOTONES --}}
<div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
    <button type="button"
        onclick="FlyonUI.modal.close('{{ isset($group->id) ? 'editModal' : 'createModal' }}')"
        class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark mr-1"></i> Cancelar
    </button>
    <button type="submit"
        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
        <i class="fa-solid fa-save"></i> {{ $buttonText }}
    </button>
</div>
