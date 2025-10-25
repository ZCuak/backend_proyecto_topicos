<div class="space-y-6">

    {{-- CAMPO: NOMBRE (requerido) --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Nombre del Tipo de Vehículo <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <i class="fa-solid fa-truck absolute left-3 top-2.5 text-slate-400"></i>
            <input type="text" 
                   name="name"
                   value="{{ old('name', $vehicletype->name ?? '') }}"
                   class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                   placeholder="Ej. Camión Compactador, Camión Volquete, Camioneta..."
                   required>
        </div>
        @error('name')
            <p class="text-red-500 text-xs mt-1">
                <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
            </p>
        @enderror
    </div>

    {{-- CAMPO: DESCRIPCIÓN (opcional)--}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Descripción
        </label>
        <div class="relative">
            <i class="fa-solid fa-align-left absolute left-3 top-2.5 text-slate-400"></i>
            <textarea name="description" 
                      rows="3"
                      class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                      placeholder="Describe las características y uso de este tipo de vehículo...">{{ old('description', $vehicletype->description ?? '') }}</textarea>
        </div>
        @error('description')
            <p class="text-red-500 text-xs mt-1">
                <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
            </p>
        @enderror
    </div>

    {{-- INFORMACIÓN ADICIONAL (si está editando) --}}
    @if(isset($vehicletype->id))
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg">
            <div class="flex items-start gap-2">
                <i class="fa-solid fa-info-circle text-slate-600 mt-0.5"></i>
                <div class="text-sm text-slate-600">
                    <p class="font-medium">Información del registro:</p>
                    <ul class="mt-1 space-y-1 text-xs">
                        <li><strong>Creado:</strong> {{ $vehicletype->created_at->format('d/m/Y H:i') }}</li>
                        <li><strong>Última modificación:</strong> {{ $vehicletype->updated_at->format('d/m/Y H:i') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- BOTONES DE ACCIÓN --}}
    <div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
        <button type="button"
            onclick="FlyonUI.modal.close('{{ isset($vehicletype->id) ? 'editModal' : 'createModal' }}')"
            class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
            <i class="fa-solid fa-xmark mr-1"></i> Cancelar
        </button>

        <button type="submit"
            class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
            <i class="fa-solid fa-save"></i> {{ $buttonText }}
        </button>
    </div>
</div>