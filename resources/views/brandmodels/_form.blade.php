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
                    value="{{ old('name', $model->name ?? '') }}"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Ej. Corolla">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Código <span class="text-red-500">*</span></label>
                <input type="text" name="code"
                    value="{{ old('code', $model->code ?? '') }}"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Ej. ABC123">
            </div>
        </div>

        {{-- Descripción --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
            <div class="relative">
                <textarea type="text" name="description"
                    value=""
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Ej. Modelo usado en camiones">{{ old('description', $model->description ?? '') }}</textarea>
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Marca</label>
            <div class="relative">
                <i class="fa-solid fa-users absolute left-3 top-2.5 text-slate-400"></i>
                <select name="brand_id"
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                    @foreach(App\Models\Brand::all() as $brand)
                        <option value="{{ $brand->id }}"
                            {{ old('brand_id', $user->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </fieldset>
</div>

{{-- BOTONES --}}
<div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
    <button type="button"
        onclick="FlyonUI.modal.close('{{ isset($model->id) ? 'editModal' : 'createModal' }}')"
        class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark mr-1"></i> Cancelar
    </button>
    <button type="submit"
        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
        <i class="fa-solid fa-save"></i> {{ $buttonText }}
    </button>
</div>
