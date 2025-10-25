<div class="space-y-6">

    {{-- CAMPO: NOMBRE  --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Nombre de la Función <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <i class="fa-solid fa-tag absolute left-3 top-2.5 text-slate-400"></i>
            <input type="text" 
                   name="name"
                   value="{{ old('name', $usertype->name ?? '') }}"
                   class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                   placeholder="Ej. Conductor, Ayudante, Supervisor..."
                   required>
        </div>
        @error('name')
            <p class="text-red-500 text-xs mt-1">
                <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
            </p>
        @enderror
    </div>

    {{-- CAMPO: DESCRIPCIÓN  --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Descripción
        </label>
        <div class="relative">
            <i class="fa-solid fa-align-left absolute left-3 top-2.5 text-slate-400"></i>
            <textarea name="description" 
                      rows="3"
                      class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                      placeholder="Describe las responsabilidades de esta función...">{{ old('description', $usertype->description ?? '') }}</textarea>
        </div>
        @error('description')
            <p class="text-red-500 text-xs mt-1">
                <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
            </p>
        @enderror
    </div>

    @if(isset($usertype->id) && $usertype->is_system)
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-info-circle text-blue-600"></i>
                <div>
                    <p class="text-sm font-medium text-blue-800">Función del sistema</p>
                    <p class="text-xs text-blue-600">Este tipo de usuario es requerida por el sistema y no puede ser eliminada.</p>
                </div>
            </div>
            {{-- Campo oculto para mantener el valor --}}
            <input type="hidden" name="is_system" value="1">
        </div>
    @endif

    {{-- BOTONES DE ACCIÓN --}}
    <div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
        <button type="button"
            onclick="FlyonUI.modal.close('{{ isset($usertype->id) ? 'editModal' : 'createModal' }}')"
            class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
            <i class="fa-solid fa-xmark mr-1"></i> Cancelar
        </button>

        <button type="submit"
            class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
            <i class="fa-solid fa-save"></i> {{ $buttonText }}
        </button>
    </div>
</div>