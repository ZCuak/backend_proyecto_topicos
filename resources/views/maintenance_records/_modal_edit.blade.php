<turbo-frame id="modal-frame">
    <div id="flyonui-modal-container">
        <!-- Overlay -->
        <div class="flyonui-overlay fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9998]"
            onclick="FlyonUI.modal.close('editModal')">
        </div>

        <!-- Modal -->
        <div id="editModal" class="flyonui-modal fixed inset-0 flex items-center justify-center z-[9999]" data-flyonui
            role="dialog" aria-modal="true">

            <div
                class="flyonui-dialog relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden 
                        animate-[flyonui-fade-in_0.3s_ease-out] mx-4 my-10">

                <!-- Header -->
                <div
                    class="flyonui-header flex justify-between items-center px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-yellow-600"></i>
                        Editar Día de Mantenimiento
                    </h3>
                    <button type="button" onclick="FlyonUI.modal.close('editModal')"
                        class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="flyonui-body p-6 bg-white overflow-y-auto max-h-[75vh]">
                    {{-- Info del registro --}}
                    <div class="bg-blue-50 rounded-lg p-4 mb-6 border border-blue-100">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-slate-600">Fecha:</span>
                                <span class="font-medium text-slate-800 ml-2">
                                    {{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-600">Día:</span>
                                <span class="font-medium text-slate-800 ml-2">
                                    {{ ucfirst(strtolower($record->schedule->day)) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-600">Vehículo:</span>
                                <span class="font-medium text-slate-800 ml-2">
                                    {{ $record->schedule->vehicle->plate }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-600">Tipo:</span>
                                <span class="font-medium text-slate-800 ml-2">
                                    {{ $record->schedule->type }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('maintenance-records.update', $record->id) }}" method="POST"
                        enctype="multipart/form-data" data-turbo-frame="modal-frame" class="space-y-6">
                        @csrf
                        @method('PUT')

                        {{-- Observación --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Observación <span class="text-red-500">*</span>
                            </label>
                            <textarea name="description" rows="4"
                                class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="Ej. Todo conforme, No se realizó, etc." required maxlength="1000">{{ old('description', $record->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Imagen actual --}}
                        @if ($record->image_path)
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    Imagen Actual
                                </label>
                                <div class="relative inline-block">
                                    <img src="{{ asset('storage/' . $record->image_path) }}" alt="Imagen actual"
                                        class="w-48 h-48 object-cover rounded-lg border border-slate-200">
                                    <div class="absolute top-2 right-2">
                                        <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded">
                                            <i class="fa-solid fa-check"></i> Actual
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Nueva Imagen --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ $record->image_path ? 'Cambiar Imagen (opcional)' : 'Subir Imagen (opcional)' }}
                            </label>
                            <input type="file" name="image_path" id="image_path"
                                accept="image/jpeg,image/png,image/jpg,image/gif"
                                class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                            <p class="text-xs text-slate-500 mt-1">
                                Formatos: JPG, PNG, GIF. Máximo 2MB.
                            </p>
                            @error('image_path')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Estado: Completado o No --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                ¿Se realizó el mantenimiento? <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="completed" value="1"
                                        {{ old('completed', $record->completed) == 1 ? 'checked' : '' }}
                                        class="text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-slate-700">
                                        <i class="fa-solid fa-check-circle text-emerald-600"></i> Sí, realizado
                                    </span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="completed" value="0"
                                        {{ old('completed', $record->completed) == 0 ? 'checked' : '' }}
                                        class="text-red-600 focus:ring-red-500">
                                    <span class="text-slate-700">
                                        <i class="fa-solid fa-times-circle text-red-600"></i> No realizado
                                    </span>
                                </label>
                            </div>
                            @error('completed')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- BOTONES --}}
                        <div class="flex justify-end gap-3 pt-5 border-t border-slate-200">
                            <button type="button" onclick="FlyonUI.modal.close('editModal')"
                                class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                <i class="fa-solid fa-xmark mr-1"></i> Cancelar
                            </button>
                            <button type="submit"
                                class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
                                <i class="fa-solid fa-save"></i> Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</turbo-frame>
