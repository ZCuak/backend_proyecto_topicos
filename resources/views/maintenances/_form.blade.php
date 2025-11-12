<div class="grid grid-cols-1 lg:grid-cols-1">

    {{-- ===========================
         🛠 DATOS DEL MANTENIMIENTO
    ============================ --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50/60 hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-id-card text-emerald-600"></i> Datos del Mantenimiento
        </legend>

        {{-- Nombre --}}
        <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="name"
                    value="{{ old('name', $maintenance->name ?? '') }}"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Ej. Mantenimiento General">
            </div>
        </div>

        {{-- Fecha de Inicio --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de Inicio <span class="text-red-500">*</span></label>
            <input type="date" name="start_date"
                   value="{{ old('start_date', $maintenance->start_date ?? '') }}"
                   class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
        </div>

        {{-- Fecha de Fin --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de Fin <span class="text-red-500">*</span></label>
            <input type="date" name="end_date"
                   value="{{ old('end_date', $maintenance->end_date ?? '') }}"
                   class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
        </div>
    </fieldset>
</div>

{{-- BOTONES --}}
<div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
    <button type="button"
        onclick="FlyonUI.modal.close('{{ isset($maintenance->id) ? 'editModal' : 'createModal' }}')"
        class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark mr-1"></i> Cancelar
    </button>
    <button type="submit"
        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
        <i class="fa-solid fa-save"></i> {{ $buttonText }}
    </button>
</div>