<div class="grid grid-cols-1 lg:grid-cols-1">

    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50/60 hover:shadow transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-palette text-emerald-600"></i> Datos del color
        </legend>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <!-- Nombre -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name"
                       value="{{ old('name', $color->name ?? '') }}"
                       class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                       placeholder="Ej. Verde bosque">
            </div>

            <!-- Selector de color -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Elegir color <span class="text-red-500">*</span>
                </label>

                <div class="flex items-center gap-3">
                    <!-- Selector nativo -->
                    <input type="color"
                        name="rgb_code"
                        id="color_picker"
                        value="{{ old('rgb_code', $color->rgb_code ?? '#ffffff') }}"
                        oninput="document.querySelector('input[name=rgb_code_text]').value=this.value"
                        class="h-10 w-12 rounded-md border border-slate-300 cursor-pointer focus:ring-emerald-500 focus:border-emerald-500">

                    <!-- Campo texto editable -->
                    <input type="text"
                        name="rgb_code_text"
                        value="{{ old('rgb_code', $color->rgb_code ?? '#ffffff') }}"
                        oninput="if(/^#([0-9A-Fa-f]{3}){1,2}$/.test(this.value)){document.getElementById('color_picker').value=this.value}"
                        class="flex-1 py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="#ffffff"
                        aria-label="Código hexadecimal (ej. #1abc9c)">
                </div>

                <!-- Paleta rápida (solo como ayuda visual, no restringe la elección) -->
                <div class="mt-3 flex flex-wrap gap-2">
                    @php
                        $swatches = [
                            '#000000','#ffffff','#f87171','#ef4444','#dc2626','#fb923c','#f59e0b',
                            '#84cc16','#10b981','#14b8a6','#0ea5e9','#3b82f6','#6366f1','#8b5cf6',
                            '#ec4899','#d946ef','#94a3b8','#64748b'
                        ];
                    @endphp
                    @foreach($swatches as $s)
                        <button type="button"
                                class="h-8 w-8 rounded-md border cursor-pointer hover:scale-110 transition"
                                style="background: {{ $s }}"
                                title="{{ $s }}"
                                onclick="
                                    document.getElementById('color_picker').value='{{ $s }}';
                                    document.querySelector('input[name=rgb_code_text]').value='{{ $s }}';
                                ">
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </fieldset>

    <!-- BOTONES -->
    <div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
        <button type="button"
            onclick="FlyonUI.modal.close('{{ isset($color->id) ? 'editModal' : 'createModal' }}')"
            class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
            <i class="fa-solid fa-xmark mr-1"></i> Cancelar
        </button>
        <button type="submit"
            class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
            <i class="fa-solid fa-save"></i> {{ $buttonText }}
        </button>
    </div>
</div>
