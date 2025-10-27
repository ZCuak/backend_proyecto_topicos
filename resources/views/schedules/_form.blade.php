<div class="grid grid-cols-1 lg:grid-cols-1">
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50/60 hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-clock text-emerald-600"></i> Datos del turno
        </legend>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $schedule->name ?? '') }}" class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Ej. Turno Mañana">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                <input type="text" name="description" value="{{ old('description', $schedule->description ?? '') }}" class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Opcional">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Hora inicio <span class="text-red-500">*</span></label>
                <input type="time" name="time_start" value="{{ old('time_start', isset($schedule->time_start) ? \Carbon\Carbon::parse($schedule->time_start)->format('H:i') : '') }}" class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Hora fin <span class="text-red-500">*</span></label>
                <input type="time" name="time_end" value="{{ old('time_end', isset($schedule->time_end) ? \Carbon\Carbon::parse($schedule->time_end)->format('H:i') : '') }}" class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
        </div>

    </fieldset>
</div>

<!-- BOTONES -->
<div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
    <button type="button" onclick="FlyonUI.modal.close('{{ isset($schedule->id) ? 'editModal' : 'createModal' }}')" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition"><i class="fa-solid fa-xmark mr-1"></i> Cancelar</button>
    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2"><i class="fa-solid fa-save"></i> {{ $buttonText }}</button>
</div>
