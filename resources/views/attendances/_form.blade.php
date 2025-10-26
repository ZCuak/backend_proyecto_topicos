<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- COLUMNA 1: DATOS DEL PERSONAL --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50/60 hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-user text-emerald-600"></i> Datos del Personal
        </legend>

        {{-- Usuario --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Personal <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <i class="fa-solid fa-user absolute left-3 top-2.5 text-slate-400"></i>
                <select name="user_id"
                        required
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Selecciona un trabajador...</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}"
                            {{ old('user_id', $attendance->user_id ?? '') == $u->id ? 'selected' : '' }}>
                            {{ $u->firstname }} {{ $u->lastname }} - DNI: {{ $u->dni }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('user_id')
                <p class="text-red-500 text-xs mt-1">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Estado --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Estado <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <i class="fa-solid fa-circle-check absolute left-3 top-2.5 text-slate-400"></i>
                <select name="status"
                        required
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="PRESENTE" {{ old('status', $attendance->status ?? 'PRESENTE') == 'PRESENTE' ? 'selected' : '' }}>
                        PRESENTE
                    </option>
                    <option value="TARDANZA" {{ old('status', $attendance->status ?? '') == 'TARDANZA' ? 'selected' : '' }}>
                        TARDANZA
                    </option>
                    <option value="AUSENTE" {{ old('status', $attendance->status ?? '') == 'AUSENTE' ? 'selected' : '' }}>
                        AUSENTE
                    </option>
                </select>
            </div>
            @error('status')
                <p class="text-red-500 text-xs mt-1">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Notas --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Notas / Observaciones
            </label>
            <textarea name="notes" 
                      rows="3"
                      class="w-full px-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                      placeholder="Ej: Llegó tarde por motivos personales...">{{ old('notes', $attendance->notes ?? '') }}</textarea>
            @error('notes')
                <p class="text-red-500 text-xs mt-1">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
                </p>
            @enderror
        </div>
    </fieldset>

    {{-- COLUMNA 2: FECHA Y HORARIOS --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-white hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-clock text-emerald-600"></i> Fecha y Horarios
        </legend>

        {{-- Fecha --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Fecha <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <i class="fa-solid fa-calendar absolute left-3 top-2.5 text-slate-400"></i>
                <input type="date" 
                       name="date"
                       value="{{ old('date', isset($attendance->date) ? \Carbon\Carbon::parse($attendance->date)->format('Y-m-d') : \Carbon\Carbon::today()->format('Y-m-d')) }}"
                       required
                       class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            @error('date')
                <p class="text-red-500 text-xs mt-1">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Hora de Entrada --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Hora de Entrada <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <i class="fa-solid fa-arrow-right-to-bracket absolute left-3 top-2.5 text-green-600"></i>
                <input type="time" 
                       name="check_in"
                       value="{{ old('check_in', isset($attendance->check_in) ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '') }}"
                       step="60"
                       required
                       class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <small class="text-slate-400 text-xs">Hora obligatoria (formato 24h)</small>
            @error('check_in')
                <p class="text-red-500 text-xs mt-1">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Hora de Salida --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Hora de Salida <span class="text-slate-400">(Opcional)</span>
            </label>
            <div class="relative">
                <i class="fa-solid fa-arrow-right-from-bracket absolute left-3 top-2.5 text-red-600"></i>
                <input type="time" 
                       name="check_out"
                       value="{{ old('check_out', isset($attendance->check_out) ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '') }}"
                       step="60"
                       class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <small class="text-slate-400 text-xs">Si no se marca salida, se registra solo como ENTRADA</small>
            @error('check_out')
                <p class="text-red-500 text-xs mt-1">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Información adicional --}}
        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700">
            <p class="font-semibold mb-1"><i class="fa-solid fa-info-circle mr-1"></i> Nota importante:</p>
            <ul class="space-y-1 pl-4">
                <li>• Si <strong>NO</strong> se marca salida → Tipo: <strong>ENTRADA</strong></li>
                <li>• Si <strong>SÍ</strong> se marca salida → Tipo: <strong>SALIDA</strong></li>
                <li>• El tipo se determina automáticamente</li>
            </ul>
        </div>
    </fieldset>
</div>

{{-- BOTONES DE ACCIÓN --}}
<div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
    <button type="button"
        onclick="FlyonUI.modal.close('{{ isset($attendance->id) ? 'editModal' : 'createModal' }}')"
        class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark mr-1"></i> Cancelar
    </button>

    <button type="submit"
        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
        <i class="fa-solid fa-save"></i> {{ $buttonText }}
    </button>
</div>