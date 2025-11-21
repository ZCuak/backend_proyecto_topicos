<div class="grid grid-cols-1 lg:grid-cols-1">

    {{-- ===========================
         🛠 DATOS DEL REGISTRO
    ============================ --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50/60 hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-id-card text-emerald-600"></i> Datos del Registro
        </legend>

        {{-- Horario --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Horario <span class="text-red-500">*</span></label>
            <select name="schedule_id"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">Seleccione un horario</option>
                @foreach($schedules as $schedule)
                    <option value="{{ $schedule->id }}" 
                            {{ old('schedule_id', $record->schedule_id ?? '') == $schedule->id ? 'selected' : '' }}>
                        {{ $schedule->maintenance->name }} - {{ $schedule->vehicle->name }} ({{ $schedule->day }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Fecha --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha <span class="text-red-500">*</span></label>
            <input type="date" name="date"
                   value="{{ old('date', $record->date ?? '') }}"
                   class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
        </div>

        {{-- Descripción --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
            <textarea name="description"
                      class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                      rows="4"
                      placeholder="Ingrese una descripción del mantenimiento">{{ old('description', $record->description ?? '') }}</textarea>
        </div>

        {{-- Realizado --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Realizado <span class="text-red-500">*</span></label>
            <select name="status"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">Seleccione un valor</option>
                <option value="SI" {{ old('status', $record->status ?? '') == 'SI' ? 'selected' : '' }}>SI</option>
                <option value="NO" {{ old('status', $record->status ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
            </select>
        </div>

        {{-- Imagen --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Imagen (opcional)</label>
            <input type="file" name="image_path"
                   class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
            @if(isset($record->image_path) && $record->image_path)
                <p class="mt-2 text-sm text-slate-500">Imagen actual: <a href="{{ asset('storage/' . $record->image_path) }}" target="_blank" class="text-emerald-600 hover:underline">Ver imagen</a></p>
            @endif
        </div>
    </fieldset>
</div>

{{-- BOTONES --}}
<div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
    <button type="button"
        onclick="FlyonUI.modal.close('{{ isset($record->id) ? 'editModal' : 'createModal' }}')"
        class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark mr-1"></i> Cancelar
    </button>
    <button type="submit"
        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
        <i class="fa-solid fa-save"></i> {{ $buttonText }}
    </button>
</div>