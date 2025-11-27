<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del motivo <span class="text-red-500">*</span></label>
        <input type="text"
               name="name"
               value="{{ old('name', $motive->name ?? '') }}"
               required
               maxlength="255"
               class="w-full border-slate-300 rounded-lg py-2 px-3 focus:ring-emerald-500 focus:border-emerald-500"
               placeholder="Ej. Ajuste operativo">
        @error('name')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="flex justify-end gap-3 pt-4 border-t border-slate-200 mt-6">
    <a href="{{ route('motives.index') }}"
       class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark mr-1"></i> Cancelar
    </a>
    <button type="submit"
        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
        <i class="fa-solid fa-save"></i> {{ $buttonText }}
    </button>
</div>
