<div class="grid grid-cols-1 lg:grid-cols-1">

    {{-- ===========================
         🛠 DATOS DEL HORARIO
    ============================ --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50/60 hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-calendar-days text-emerald-600"></i> Datos del Horario
        </legend>

        {{-- Mantenimiento --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Mantenimiento <span class="text-red-500">*</span>
            </label>
            <select name="maintenance_id"
                    id="maintenance_id"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    required>
                <option value="">Seleccione un mantenimiento</option>
                @foreach($maintenances as $maintenance)
                    <option value="{{ $maintenance->id }}" 
                            {{ old('maintenance_id', $preselectedMaintenanceId ?? $schedule->maintenance_id ?? '') == $maintenance->id ? 'selected' : '' }}>
                        {{ $maintenance->name }} 
                        ({{ \Carbon\Carbon::parse($maintenance->start_date)->format('d/m/Y') }} - 
                         {{ \Carbon\Carbon::parse($maintenance->end_date)->format('d/m/Y') }})
                    </option>
                @endforeach
            </select>
            @error('maintenance_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Responsable --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Responsable <span class="text-red-500">*</span>
            </label>
            <select name="responsible_id"
                    id="responsible_id"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    required>
                <option value="">Seleccione un responsable</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        {{ old('responsible_id', $schedule->responsible_id ?? '') == $user->id ? 'selected' : '' }}>
                        {{ $user->firstname }} {{ $user->lastname }} - DNI: {{ $user->dni }}
                    </option>
                @endforeach
            </select>
            @error('responsible_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Vehículo --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Vehículo <span class="text-red-500">*</span>
            </label>
            <select name="vehicle_id"
                    id="vehicle_id"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    required>
                <option value="">Seleccione un vehículo</option>
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" 
                            {{ old('vehicle_id', $schedule->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->plate }} - {{ $vehicle->name }}
                    </option>
                @endforeach
            </select>
            @error('vehicle_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tipo --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Tipo de Mantenimiento <span class="text-red-500">*</span>
            </label>
            <select name="type"
                    id="type"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    required>
                <option value="">Seleccione un tipo</option>
                <option value="PREVENTIVO" {{ old('type', $schedule->type ?? '') == 'PREVENTIVO' ? 'selected' : '' }}>
                    Preventivo
                </option>
                <option value="LIMPIEZA" {{ old('type', $schedule->type ?? '') == 'LIMPIEZA' ? 'selected' : '' }}>
                    Limpieza
                </option>
                <option value="REPARACIÓN" {{ old('type', $schedule->type ?? '') == 'REPARACIÓN' ? 'selected' : '' }}>
                    Reparación
                </option>
            </select>
            @error('type')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Día --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Día de la Semana <span class="text-red-500">*</span>
            </label>
            <select name="day"
                    id="day"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    required>
                <option value="">Seleccione un día</option>
                @foreach(['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO'] as $day)
                    <option value="{{ $day }}" {{ old('day', $schedule->day ?? '') == $day ? 'selected' : '' }}>
                        {{ ucfirst(strtolower($day)) }}
                    </option>
                @endforeach
            </select>
            @error('day')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Hora de Inicio --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Hora de Inicio <span class="text-red-500">*</span>
            </label>
            <input type="time" 
                   name="start_time"
                   id="start_time"
                   value="{{ old('start_time', $schedule->start_time ?? '') }}"
                   class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                   required>
            @error('start_time')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Hora de Fin --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Hora de Fin <span class="text-red-500">*</span>
            </label>
            <input type="time" 
                   name="end_time"
                   id="end_time"
                   value="{{ old('end_time', $schedule->end_time ?? '') }}"
                   class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                   required>
            @error('end_time')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </fieldset>
</div>

{{-- BOTONES --}}
<div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
    <button type="button"
        onclick="FlyonUI.modal.close('{{ isset($schedule->id) ? 'editModal' : 'createModal' }}')"
        class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark mr-1"></i> Cancelar
    </button>
    <button type="submit"
        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
        <i class="fa-solid fa-save"></i> {{ $buttonText }}
    </button>
</div>