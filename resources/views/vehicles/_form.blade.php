{{-- Mostrar errores de validación --}}
@if($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
        <h4 class="font-semibold mb-2">Errores de validación:</h4>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li class="text-sm">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
    {{-- ===========================
         📝 FORMULARIO PRINCIPAL
    ============================ --}}
    <div class="xl:col-span-3 space-y-6">
        {{-- ===========================
             🚛 DATOS DEL VEHÍCULO
        ============================ --}}
        <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50/60 hover:shadow-sm transition">
            <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
                <i class="fa-solid fa-truck text-emerald-600"></i> Datos del vehículo
            </legend>

            {{-- Nombre del vehículo --}}
            <div class="mt-3">
                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del vehículo <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i class="fa-solid fa-tag absolute left-3 top-2.5 text-slate-400"></i>
                    <input type="text" name="name" maxlength="150"
                        value="{{ old('name', $vehicle->name ?? '') }}"
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('name') border-red-500 @enderror"
                        placeholder="Ej. Camión Recolección 01">
                </div>
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Código, Placa, Año y Estado en una fila --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Código <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-hashtag absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="text" name="code" maxlength="50"
                            value="{{ old('code', $vehicle->code ?? '') }}"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('code') border-red-500 @enderror"
                            placeholder="Ej. VEH-001">
                    </div>
                    @error('code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Placa <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-id-card absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="text" name="plate" id="plateInput"
                            value="{{ old('plate', $vehicle->plate ?? '') }}"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 uppercase @error('plate') border-red-500 @enderror"
                            placeholder="Ej. ABC123, AB-1234, ABC-123"
                            title="Formatos válidos: XXXXXX (ej: ABC123), XX-XXXX (ej: AB-1234), XXX-XXX (ej: ABC-123)">
                    </div>
                    @error('plate')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Año <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-calendar absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="number" name="year" min="1900" max="{{ date('Y') }}" maxlength="4"
                            value="{{ old('year', $vehicle->year ?? '') }}"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="2023"
                            title="Año entre 1900 y {{ date('Y') }} (4 dígitos)"
                            oninput="this.value = this.value.slice(0, 4)">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Estado <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-circle-check absolute left-3 top-2.5 text-slate-400"></i>
                        <select name="status"
                                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Seleccionar estado...</option>
                            <option value="DISPONIBLE" {{ old('status', $vehicle->status ?? '') == 'DISPONIBLE' ? 'selected' : '' }}>Disponible</option>
                            <option value="OCUPADO" {{ old('status', $vehicle->status ?? '') == 'OCUPADO' ? 'selected' : '' }}>Ocupado</option>
                            <option value="MANTENIMIENTO" {{ old('status', $vehicle->status ?? '') == 'MANTENIMIENTO' ? 'selected' : '' }}>Mantenimiento</option>
                            <option value="INACTIVO" {{ old('status', $vehicle->status ?? '') == 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Descripción --}}
            <div class="mt-3">
                <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                <textarea name="description" rows="3"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Descripción del vehículo...">{{ old('description', $vehicle->description ?? '') }}</textarea>
            </div>
        </fieldset>

        {{-- ===========================
             🔧 ESPECIFICACIONES TÉCNICAS
        ============================ --}}
        <fieldset class="border border-slate-200 rounded-xl p-5 bg-white hover:shadow-sm transition">
            <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
                <i class="fa-solid fa-cogs text-emerald-600"></i> Especificaciones técnicas
            </legend>

            {{-- Marca, Modelo, Tipo y Color en una fila --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Marca <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-industry absolute left-3 top-2.5 text-slate-400"></i>
                        <select name="brand_id" id="brand_id"
                                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Seleccionar marca...</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}"
                                    {{ old('brand_id', $vehicle->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Modelo <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-car absolute left-3 top-2.5 text-slate-400"></i>
                        <select name="model_id" id="model_id"
                                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Seleccionar modelo...</option>
                            @if(isset($vehicle) && $vehicle->model_id)
                                @foreach($models as $model)
                                    <option value="{{ $model->id }}"
                                        {{ old('model_id', $vehicle->model_id) == $model->id ? 'selected' : '' }}>
                                        {{ $model->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-truck-pickup absolute left-3 top-2.5 text-slate-400"></i>
                        <select name="type_id"
                                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Seleccionar tipo...</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('type_id', $vehicle->type_id ?? '') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Color <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-palette absolute left-3 top-2.5 text-slate-400"></i>
                        <select name="color_id"
                                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('color_id') border-red-500 @enderror">
                            <option value="">Seleccionar color...</option>
                            @foreach($colors as $color)
                                <option value="{{ $color->id }}"
                                    {{ old('color_id', $vehicle->color_id ?? '') == $color->id ? 'selected' : '' }}>
                                    {{ $color->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('color_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Capacidades en una fila --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Capacidad de Pasajeros</label>
                    <div class="relative">
                        <i class="fa-solid fa-users absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="number" name="occupant_capacity" min="0"
                            value="{{ old('occupant_capacity', $vehicle->occupant_capacity ?? '') }}"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Ej. 2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Capacidad de Carga (kg)</label>
                    <div class="relative">
                        <i class="fa-solid fa-weight-hanging absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="number" name="load_capacity" min="0"
                            value="{{ old('load_capacity', $vehicle->load_capacity ?? '') }}"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Ej. 5000">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Capacidad de Compactación (m³)</label>
                    <div class="relative">
                        <i class="fa-solid fa-cube absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="number" name="compaction_capacity" min="0"
                            value="{{ old('compaction_capacity', $vehicle->compaction_capacity ?? '') }}"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Ej. 15">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Capacidad de Combustible (L)</label>
                    <div class="relative">
                        <i class="fa-solid fa-gas-pump absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="number" name="fuel_capacity" min="0"
                            value="{{ old('fuel_capacity', $vehicle->fuel_capacity ?? '') }}"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Ej. 200">
                    </div>
                </div>
            </div>
        </fieldset>
    </div>

    {{-- ===========================
         📸 PANEL DE IMÁGENES
    ============================ --}}
    <div class="xl:col-span-1">
        <fieldset class="border border-slate-200 rounded-xl p-5 bg-white hover:shadow-sm transition sticky top-4">
            <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
                <i class="fa-solid fa-images text-emerald-600"></i> Imágenes del vehículo
            </legend>

            {{-- Área de carga --}}
            <div class="mt-4">
                <div class="border-2 border-dashed border-slate-300 rounded-lg p-4 text-center hover:border-emerald-400 transition-colors cursor-pointer"
                     id="dropZone">
                    <div class="space-y-2">
                        <i class="fa-solid fa-cloud-upload-alt text-3xl text-slate-400"></i>
                        <div>
                            <p class="text-sm text-slate-600">
                                <span class="font-medium">Haz clic para seleccionar</span> o arrastra imágenes aquí
                            </p>
                            <p class="text-xs text-slate-500 mt-1">PNG, JPG, JPEG hasta 5MB cada una</p>
                        </div>
                    </div>
                    <input type="file" name="images[]" id="imageInput" multiple accept="image/*" 
                           class="hidden">
                    
                    {{-- Input oculto para imágenes temporales --}}
                    <input type="file" name="images[]" id="tempImageInput" multiple accept="image/*" 
                           class="hidden">
                </div>

                {{-- Galería de imágenes --}}
                <div id="imageGallery" class="mt-4 grid grid-cols-2 gap-3">
                    @if(isset($vehicle) && $vehicle->images->count() > 0)
                        @foreach($vehicle->images as $image)
                            <div class="relative group" data-image-id="{{ $image->id }}">
                                <div class="aspect-square rounded-lg overflow-hidden bg-slate-100">
                                    <img src="{{ $image->url }}" alt="Imagen del vehículo" 
                                         class="w-full h-full object-cover">
                                </div>
                                
                                {{-- Indicador de imagen de perfil fuera de la imagen --}}
                                @if($image->is_profile)
                                    <div class="mt-1 flex justify-center">
                                        <span class="bg-yellow-500 text-white rounded-full px-2 py-1 text-xs flex items-center gap-1">
                                            <i class="fa-solid fa-star"></i>
                                            <span>Perfil</span>
                                        </span>
                                    </div>
                                @endif
                                
                                {{-- Botones de acción fuera de la imagen --}}
                                <div class="mt-2 flex gap-1">
                                    <button type="button" 
                                            onclick="setAsProfile({{ $image->id }})"
                                            class="profile-btn flex-1 px-2 py-1 text-white rounded-md text-xs transition flex items-center justify-center {{ $image->is_profile ? 'bg-yellow-600' : 'bg-emerald-600 hover:bg-emerald-700' }}"
                                            data-image-id="{{ $image->id }}"
                                            data-is-profile="{{ $image->is_profile ? 'true' : 'false' }}">
                                        <i class="fa-solid fa-star"></i>
                                    </button>
                                    
                                    <button type="button" onclick="deleteImage({{ $image->id }})"
                                            class="flex-1 px-2 py-1 bg-red-600 text-white rounded-md text-xs hover:bg-red-700 transition flex items-center justify-center">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Mensaje cuando no hay imágenes --}}
                <div id="noImagesMessage" class="text-center py-6 text-slate-500 {{ isset($vehicle) && $vehicle->images->count() > 0 ? 'hidden' : '' }}">
                    <i class="fa-solid fa-images text-3xl mb-2 text-slate-300"></i>
                    <p class="text-sm">No hay imágenes cargadas</p>
                    <p class="text-xs text-slate-400 mt-1">Selecciona imágenes para comenzar</p>
                </div>
            </div>
        </fieldset>
    </div>
</div>

{{-- BOTONES --}}
@php
    $modalId = isset($vehicle) && isset($vehicle->id) ? 'editModal' : 'createModal';
@endphp
<div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
    <button type="button"
        onclick="FlyonUI.modal.close('{{ $modalId }}')"
        class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark mr-1"></i> Cancelar
    </button>
    <button type="submit"
        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
        <i class="fa-solid fa-save"></i> {{ $buttonText }}
    </button>
</div>

{{-- Scripts para filtro de modelos por marca y validaciones --}}
<script>
(function() {
    'use strict';
    
    // Validación del formulario antes de enviar
    document.querySelector('form').addEventListener('submit', function(e) {
        console.log('=== FORM SUBMIT DEBUG ===');
        console.log('Form action:', this.action);
        console.log('Form method:', this.method);
        
        // Debuggear datos del formulario
        const formData = new FormData(this);
        console.log('Form data entries:');
        for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
                console.log(`${key}: File - ${value.name} (${value.size} bytes)`);
            } else {
                console.log(`${key}: ${value}`);
            }
        }
        
        // Debuggear imágenes específicamente
        const imageInput = document.getElementById('imageInput');
        if (imageInput && imageInput.files.length > 0) {
            console.log('Images in input:', imageInput.files.length);
            Array.from(imageInput.files).forEach((file, index) => {
                console.log(`Image ${index}: ${file.name} (${file.size} bytes)`);
            });
        } else {
            console.log('No images in input');
        }
        
        // Debuggear imágenes temporales
        if (typeof uploadedImages !== 'undefined') {
            console.log('Temporary images:', uploadedImages.length);
            uploadedImages.forEach((img, index) => {
                console.log(`Temp image ${index}:`, img);
            });
            
            // Asegurar que las imágenes temporales se agreguen al FormData
            uploadedImages.forEach((img, index) => {
                if (img.file) {
                    console.log(`Adding temp image ${index} to FormData:`, img.file.name);
                    formData.append('images[]', img.file);
                }
            });
        }
        
        // Verificar FormData final
        console.log('Final FormData entries:');
        for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
                console.log(`${key}: File - ${value.name} (${value.size} bytes)`);
            } else {
                console.log(`${key}: ${value}`);
            }
        }
        
        const requiredFields = [
            { name: 'name', label: 'Nombre del vehículo' },
            { name: 'code', label: 'Código' },
            { name: 'plate', label: 'Placa' },
            { name: 'year', label: 'Año' },
            { name: 'status', label: 'Estado' },
            { name: 'brand_id', label: 'Marca' },
            { name: 'model_id', label: 'Modelo' },
            { name: 'type_id', label: 'Tipo' },
            { name: 'color_id', label: 'Color' }
        ];
    
    let hasErrors = false;
    let errorMessages = [];
    
    // Limpiar errores anteriores
    document.querySelectorAll('.field-error').forEach(el => el.remove());
    document.querySelectorAll('.border-red-500').forEach(el => {
        el.classList.remove('border-red-500');
        el.classList.add('border-slate-300');
    });
    
    // Validar cada campo obligatorio
    requiredFields.forEach(field => {
        const element = document.querySelector(`[name="${field.name}"]`);
        if (element) {
            const value = element.value.trim();
            
            if (!value || value === '') {
                hasErrors = true;
                errorMessages.push(`${field.label} es obligatorio`);
                
                // Marcar campo con error
                element.classList.add('border-red-500');
                element.classList.remove('border-slate-300');
                
                // Agregar mensaje de error
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error text-red-500 text-xs mt-1';
                errorDiv.textContent = `${field.label} es obligatorio`;
                element.parentNode.appendChild(errorDiv);
            }
        }
    });
    
        // Si hay errores, prevenir envío
        if (hasErrors) {
            e.preventDefault();
            
            // Mostrar mensaje general
            const generalError = document.createElement('div');
            generalError.className = 'mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg';
            generalError.innerHTML = `
                <h4 class="font-semibold mb-2">Por favor completa todos los campos obligatorios:</h4>
                <ul class="list-disc list-inside space-y-1">
                    ${errorMessages.map(msg => `<li class="text-sm">${msg}</li>`).join('')}
                </ul>
            `;
            
            // Insertar mensaje al inicio del formulario
            const form = document.querySelector('form');
            form.insertBefore(generalError, form.firstChild);
            
            // Scroll al mensaje de error
            generalError.scrollIntoView({ behavior: 'smooth' });
            
            return false;
        }
        
        // Si no hay errores, interceptar el envío para agregar imágenes temporales
        e.preventDefault();
        
        console.log('=== FORM INTERCEPTOR DEBUG ===');
        console.log('Preventing default form submission');
        
        // Crear nuevo FormData con las imágenes temporales
        const finalFormData = new FormData();
        
        // Agregar todos los campos del formulario original
        for (let [key, value] of formData.entries()) {
            finalFormData.append(key, value);
        }
        
        // Agregar imágenes temporales si existen
        if (typeof uploadedImages !== 'undefined' && uploadedImages.length > 0) {
            console.log('Adding temporary images to final FormData');
            uploadedImages.forEach((img, index) => {
                if (img.file) {
                    console.log(`Adding temp image ${index} to final FormData:`, img.file.name, 'isProfile:', img.isProfile);
                    finalFormData.append('images[]', img.file);
                    
                    // Agregar información de perfil
                    if (img.isProfile) {
                        finalFormData.append('profile_image_index', index);
                        console.log(`Setting profile image index to: ${index}`);
                    }
                }
            });
        }
        
        // Las imágenes ya están en el FormData original desde el input oculto
        // Pero necesitamos agregar la información de perfil si no se agregó arriba
        if (typeof uploadedImages !== 'undefined' && uploadedImages.length > 0) {
            const profileImage = uploadedImages.find(img => img.isProfile);
            if (profileImage) {
                const profileIndex = uploadedImages.indexOf(profileImage);
                finalFormData.append('profile_image_index', profileIndex);
                console.log(`Setting profile image index from uploadedImages: ${profileIndex}`);
            } else {
                console.log('No profile image found in uploadedImages array');
            }
        } else {
            console.log('No uploadedImages array found');
        }
        
        // Verificar FormData final antes del envío
        console.log('Final FormData before sending:');
        for (let [key, value] of finalFormData.entries()) {
            if (value instanceof File) {
                console.log(`${key}: File - ${value.name} (${value.size} bytes)`);
            } else {
                console.log(`${key}: ${value}`);
            }
        }
        
        // Debuggear información de perfil
        console.log('=== PROFILE INFO DEBUG ===');
        if (typeof uploadedImages !== 'undefined') {
            uploadedImages.forEach((img, index) => {
                console.log(`Image ${index}:`, {
                    id: img.id,
                    fileName: img.fileName || img.file?.name,
                    isProfile: img.isProfile,
                    url: img.url
                });
            });
        }
        
        // Verificar que profile_image_index esté en el FormData
        console.log('=== PROFILE INDEX VERIFICATION ===');
        const profileIndexValue = finalFormData.get('profile_image_index');
        // Si no hay profile_image_index, agregarlo por defecto como 0
        if (profileIndexValue === null) {
            finalFormData.append('profile_image_index', '0');
            console.log('Added default profile_image_index: 0');
        }
        
        // Verificar el valor final
        const finalProfileIndex = finalFormData.get('profile_image_index');
        console.log('Final profile image index:', finalProfileIndex);
        
        // Enviar formulario con fetch
        console.log('Sending form with fetch to:', this.action);
        fetch(this.action, {
            method: this.method,
            body: finalFormData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Turbo-Frame': 'modal-frame'
            }
        })
        .then(response => {
            console.log('Response received:', response.status);
            console.log('Response headers:', response.headers);
            if (response.ok) {
                // Si es una respuesta JSON (Turbo), parsearla
                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // Si es HTML, recargar la página
                    console.log('HTML response, reloading page');
                    window.location.reload();
                }
            } else {
                throw new Error('Error en la respuesta del servidor: ' + response.status);
            }
        })
        .then(data => {
            console.log('Response data:', data);
            if (data && data.success) {
                console.log('Success:', data.message);
                // Cerrar modal y recargar página
                if (typeof FlyonUI !== 'undefined' && FlyonUI.modal) {
                    const modalId = this.action.includes('update') ? 'editModal' : 'createModal';
                    FlyonUI.modal.close(modalId);
                }
                window.location.reload();
            } else {
                console.error('Error response:', data);
                alert('Error: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Error al enviar el formulario: ' + error.message);
        });
});

document.getElementById('brand_id').addEventListener('change', function() {
    const brandId = this.value;
    const modelSelect = document.getElementById('model_id');
    
    // Limpiar opciones
    modelSelect.innerHTML = '<option value="">Seleccionar modelo...</option>';
    
    if (brandId) {
        fetch(`/vehicles/models/${brandId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(model => {
                        const option = document.createElement('option');
                        option.value = model.id;
                        option.textContent = model.name;
                        modelSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading models:', error));
    }
});

// Validación de placa en tiempo real con límite dinámico de caracteres
const plateInput = document.getElementById('plateInput') || document.querySelector('input[name="plate"]');
if (plateInput) {
    plateInput.addEventListener('input', function(e) {
        let plate = e.target.value.toUpperCase().trim();
        
        // Remover caracteres no permitidos (solo letras, números y guiones)
        plate = plate.replace(/[^A-Z0-9\-]/g, '');
        
        // Detectar formato y limitar caracteres
        let maxLength = 6; // Por defecto formato XXXXXX
        
        // Si tiene guión, determinar el formato
        if (plate.includes('-')) {
            const parts = plate.split('-');
            if (parts[0].length <= 2 && parts[1].length <= 4) {
                maxLength = 7; // Formato XX-XXXX (2 + guión + 4 = 7)
            } else if (parts[0].length <= 3 && parts[1].length <= 3) {
                maxLength = 7; // Formato XXX-XXX (3 + guión + 3 = 7)
            }
        } else {
            maxLength = 6; // Formato XXXXXX
        }
        
        // Limitar longitud según el formato detectado
        if (plate.length > maxLength) {
            plate = plate.slice(0, maxLength);
        }
        
        // Actualizar valor
        e.target.value = plate;
        
        // Validar formatos: XXXXXX, XX-XXXX, XXX-XXX
        const plateFormats = [
            /^[A-Z0-9]{6}$/,           // XXXXXX (6 caracteres sin guión)
            /^[A-Z0-9]{2}-[A-Z0-9]{4}$/, // XX-XXXX (2 caracteres, guión, 4 caracteres)
            /^[A-Z0-9]{3}-[A-Z0-9]{3}$/, // XXX-XXX (3 caracteres, guión, 3 caracteres)
        ];
        
        let isValid = false;
        if (plate) {
            for (let format of plateFormats) {
                if (format.test(plate)) {
                    isValid = true;
                    break;
                }
            }
        }
        
        // Validar formato
        if (plate && !isValid) {
            e.target.setCustomValidity('Formato inválido. Use: XXXXXX (ej: ABC123), XX-XXXX (ej: AB-1234) o XXX-XXX (ej: ABC-123)');
            e.target.classList.add('border-red-500');
            e.target.classList.remove('border-slate-300');
        } else {
            e.target.setCustomValidity('');
            e.target.classList.remove('border-red-500');
            e.target.classList.add('border-slate-300');
        }
    });
    
    // Prevenir pegar texto que exceda el límite
    plateInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text').toUpperCase().trim();
        let plate = pastedText.replace(/[^A-Z0-9\-]/g, '');
        
        // Detectar formato y limitar
        let maxLength = 6;
        if (plate.includes('-')) {
            const parts = plate.split('-');
            if (parts[0].length <= 2 && parts[1].length <= 4) {
                maxLength = 7;
            } else if (parts[0].length <= 3 && parts[1].length <= 3) {
                maxLength = 7;
            }
        }
        
        if (plate.length > maxLength) {
            plate = plate.slice(0, maxLength);
        }
        
        this.value = plate;
        this.dispatchEvent(new Event('input'));
    });
}

// Validación de año en tiempo real (solo 4 dígitos)
const yearInput = document.querySelector('input[name="year"]');
if (yearInput) {
    yearInput.addEventListener('input', function(e) {
        // Limitar a 4 dígitos y solo números
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value.length > 4) {
            value = value.slice(0, 4);
        }
        e.target.value = value;
        
        const year = parseInt(value);
        const currentYear = new Date().getFullYear();
        
        if (value && (year < 1900 || year > currentYear)) {
            e.target.setCustomValidity(`El año debe estar entre 1900 y ${currentYear}`);
            e.target.classList.add('border-red-500');
            e.target.classList.remove('border-slate-300');
        } else {
            e.target.setCustomValidity('');
            e.target.classList.remove('border-red-500');
            e.target.classList.add('border-slate-300');
        }
    });
    
    // Prevenir pegar texto que no sea numérico o exceda 4 dígitos
    yearInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
        if (pastedText.length > 4) {
            this.value = pastedText.slice(0, 4);
        } else {
            this.value = pastedText;
        }
        this.dispatchEvent(new Event('input'));
    });
}

// Validación de código único (opcional - se puede implementar con AJAX)
document.querySelector('input[name="code"]').addEventListener('blur', function(e) {
    const code = e.target.value;
    if (code) {
        // Aquí se podría implementar una validación AJAX para verificar si el código ya existe
        // Por ahora solo validamos que no esté vacío
        if (code.trim() === '') {
            e.target.setCustomValidity('El código es obligatorio');
            e.target.classList.add('border-red-500');
            e.target.classList.remove('border-slate-300');
        } else {
            e.target.setCustomValidity('');
            e.target.classList.remove('border-red-500');
            e.target.classList.add('border-slate-300');
        }
    }
});

// Validación en tiempo real para campos obligatorios
const requiredFields = ['name', 'code', 'plate', 'year', 'status', 'brand_id', 'model_id', 'type_id', 'color_id'];

requiredFields.forEach(fieldName => {
    const element = document.querySelector(`[name="${fieldName}"]`);
    if (element) {
        element.addEventListener('input', function(e) {
            const value = e.target.value.trim();
            
            // Remover mensajes de error anteriores
            const existingError = e.target.parentNode.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
            
            if (value && value !== '') {
                // Campo válido
                e.target.classList.remove('border-red-500');
                e.target.classList.add('border-slate-300');
                e.target.setCustomValidity('');
            } else {
                // Campo vacío
                e.target.classList.add('border-red-500');
                e.target.classList.remove('border-slate-300');
                e.target.setCustomValidity('Este campo es obligatorio');
            }
        });
        
        element.addEventListener('change', function(e) {
            const value = e.target.value.trim();
            
            // Remover mensajes de error anteriores
            const existingError = e.target.parentNode.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
            
            if (value && value !== '') {
                // Campo válido
                e.target.classList.remove('border-red-500');
                e.target.classList.add('border-slate-300');
                e.target.setCustomValidity('');
            } else {
                // Campo vacío
                e.target.classList.add('border-red-500');
                e.target.classList.remove('border-slate-300');
                e.target.setCustomValidity('Este campo es obligatorio');
            }
        });
    }
});

// ===========================
// 📸 MANEJO DE IMÁGENES
// ============================

// Variables para manejo de imágenes
let uploadedImages = [];
let imageCounter = 0;

// Funciones separadas para los event handlers
function handleDropZoneClick(e) {
    e.preventDefault();
    e.stopPropagation();
    console.log('Click en dropZone');
    
    // Crear un nuevo input file temporal
    const tempInput = document.createElement('input');
    tempInput.type = 'file';
    tempInput.multiple = true;
    tempInput.accept = 'image/*';
    
    tempInput.addEventListener('change', function(e) {
        console.log('Archivos seleccionados desde input temporal:', e.target.files.length);
        handleFiles(e.target.files);
    });
    
    // Simular click en el input temporal
    tempInput.click();
}

function handleImageInputChange(e) {
    console.log('Archivos seleccionados:', e.target.files.length);
    handleFiles(e.target.files);
}

// Función para inicializar imágenes (se ejecuta inmediatamente)
function initImageHandlers() {
    const dropZone = document.getElementById('dropZone');
    const imageInput = document.getElementById('imageInput');
    
    console.log('=== INIT IMAGE HANDLERS DEBUG ===');
    console.log('Buscando elementos:', { dropZone, imageInput });
    console.log('Document ready state:', document.readyState);
    
    if (dropZone && imageInput) {
        console.log('✅ Elementos encontrados, inicializando manejadores de imágenes...');
        
        // Remover listeners anteriores para evitar duplicados
        dropZone.removeEventListener('click', handleDropZoneClick);
        imageInput.removeEventListener('change', handleImageInputChange);
        
        // Click en el área de carga
        dropZone.addEventListener('click', handleDropZoneClick);
        
        // Selección de archivos
        imageInput.addEventListener('change', handleImageInputChange);

        // Drag and drop
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-emerald-400', 'bg-emerald-50');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-emerald-400', 'bg-emerald-50');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-emerald-400', 'bg-emerald-50');
            handleFiles(e.dataTransfer.files);
        });
        
        // Cargar imágenes existentes si estamos editando
        loadExistingImages();
        
        console.log('✅ Manejadores de imágenes inicializados correctamente');
    } else {
        console.log('❌ Elementos no encontrados:', { dropZone, imageInput });
        console.log('Document body:', document.body);
        console.log('Available elements with "dropZone" id:', document.querySelectorAll('[id*="dropZone"]'));
        console.log('Available elements with "imageInput" id:', document.querySelectorAll('[id*="imageInput"]'));
    }
}

// Cargar imágenes existentes del vehículo
function loadExistingImages() {
    // Verificar si hay imágenes existentes en el HTML (para edición)
    const existingImages = document.querySelectorAll('#imageGallery [data-image-id]');
    if (existingImages.length > 0) {
        console.log('Cargando imágenes existentes:', existingImages.length);
        
        // Las imágenes ya están en el HTML, solo necesitamos actualizar el mensaje
        updateNoImagesMessage();
        
        // Agregar las imágenes al array de uploadedImages para consistencia
        existingImages.forEach(img => {
            const imageId = img.getAttribute('data-image-id');
            const imgElement = img.querySelector('img');
            
            if (imgElement && imgElement.src) {
                const imageUrl = imgElement.src;
                const fileName = 'imagen.jpg'; // Nombre por defecto
                const isProfile = img.querySelector('.absolute.top-1.right-1') !== null;
                
                // Agregar al array si no existe
                if (!uploadedImages.find(existingImg => existingImg.id === imageId)) {
                    uploadedImages.push({
                        id: imageId,
                        url: imageUrl,
                        fileName: fileName,
                        isProfile: isProfile
                    });
                }
            }
        });
    }
}

// Función para intentar inicializar múltiples veces
function tryInitImageHandlers() {
    console.log('=== TRYING TO INIT IMAGE HANDLERS ===');
    initImageHandlers();
}

// Ejecutar inmediatamente
tryInitImageHandlers();

// También ejecutar cuando el DOM esté listo (por si acaso)
document.addEventListener('DOMContentLoaded', tryInitImageHandlers);

// Ejecutar cuando se abre el modal (para modales dinámicos)
document.addEventListener('turbo:frame-render', tryInitImageHandlers);
document.addEventListener('turbo:load', tryInitImageHandlers);

// Ejecutar después de delays progresivos para asegurar que los elementos estén disponibles
setTimeout(tryInitImageHandlers, 100);
setTimeout(tryInitImageHandlers, 500);
setTimeout(tryInitImageHandlers, 1000);

// También ejecutar cuando se hace clic en el botón de editar (si existe)
document.addEventListener('click', function(e) {
    if (e.target && e.target.closest('[data-turbo-frame="modal-frame"]')) {
        console.log('Modal button clicked, trying to init handlers...');
        setTimeout(tryInitImageHandlers, 200);
    }
});


// Manejar archivos seleccionados
function handleFiles(files) {
    console.log('=== HANDLE FILES DEBUG ===');
    console.log('Files received:', files.length);
    
    Array.from(files).forEach((file, index) => {
        console.log(`Processing file ${index}:`, {
            name: file.name,
            size: file.size,
            type: file.type
        });
        
        if (file.type.startsWith('image/')) {
            if (file.size > 5 * 1024 * 1024) { // 5MB
                console.log(`File ${file.name} is too large: ${file.size} bytes`);
                alert('El archivo ' + file.name + ' es demasiado grande. Máximo 5MB.');
                return;
            }
            console.log(`File ${file.name} is valid, creating preview...`);
            previewImage(file);
        } else {
            console.log(`File ${file.name} is not an image: ${file.type}`);
            alert('El archivo ' + file.name + ' no es una imagen válida.');
        }
    });
}

// Vista previa de imagen
function previewImage(file) {
    console.log('=== PREVIEW IMAGE DEBUG ===');
    console.log('Creating preview for file:', file.name);
    
    const reader = new FileReader();
    reader.onload = (e) => {
        const imageId = 'temp_' + (++imageCounter);
        const isProfile = uploadedImages.length === 0; // Primera imagen es perfil por defecto
        
        console.log('Image preview created:', {
            imageId: imageId,
            fileName: file.name,
            isProfile: isProfile,
            totalImages: uploadedImages.length + 1
        });
        
        uploadedImages.push({
            id: imageId,
            file: file,
            url: e.target.result,
            isProfile: isProfile
        });

        addImageToGallery(imageId, e.target.result, file.name, isProfile);
        updateNoImagesMessage();
        
        // Actualizar el input oculto con las imágenes temporales
        updateHiddenFileInput();
        
        console.log('Updated uploadedImages array:', uploadedImages);
    };
    reader.readAsDataURL(file);
}

// Agregar imagen a la galería
function addImageToGallery(imageId, imageUrl, fileName, isProfile = false) {
    const imageGallery = document.getElementById('imageGallery');
    const imageDiv = document.createElement('div');
    imageDiv.className = 'relative group';
    imageDiv.setAttribute('data-image-id', imageId);
    
    imageDiv.innerHTML = `
        <div class="aspect-square rounded-lg overflow-hidden bg-slate-100">
            <img src="${imageUrl}" alt="${fileName}" class="w-full h-full object-cover">
        </div>
        
        <div class="mt-1 flex justify-center" style="display: ${isProfile ? 'flex' : 'none'}">
            <span class="bg-yellow-500 text-white rounded-full px-2 py-1 text-xs flex items-center gap-1">
                <i class="fa-solid fa-star"></i>
                <span>Perfil</span>
            </span>
        </div>
        
        <div class="mt-2 flex gap-1">
            <button type="button" 
                    onclick="setAsProfile('${imageId}')" 
                    class="profile-btn flex-1 px-2 py-1 text-white rounded-md text-xs transition flex items-center justify-center ${isProfile ? 'bg-yellow-600' : 'bg-emerald-600 hover:bg-emerald-700'}"
                    data-image-id="${imageId}"
                    data-is-profile="${isProfile}">
                <i class="fa-solid fa-star"></i>
            </button>
            
            <button type="button" onclick="deleteImage('${imageId}')" class="flex-1 px-2 py-1 bg-red-600 text-white rounded-md text-xs hover:bg-red-700 transition flex items-center justify-center">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    `;
    
    imageGallery.appendChild(imageDiv);
}

// Establecer como imagen de perfil
function setAsProfile(imageId) {
    console.log('Estableciendo imagen de perfil:', imageId);
    
    // Si es una imagen existente (no temporal), hacer petición AJAX
    if (!imageId.toString().startsWith('temp_')) {
        setAsProfileExisting(imageId);
        return;
    }
    
    // Para imágenes temporales (nuevas)
    // 1. Remover perfil de todas las imágenes (ponerlas verdes)
    document.querySelectorAll('.profile-btn').forEach(btn => {
        btn.classList.remove('bg-yellow-600');
        btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
        btn.setAttribute('data-is-profile', 'false');
    });
    
    // 2. Ocultar indicadores de perfil de todas las imágenes EXCEPTO la seleccionada
    document.querySelectorAll('[data-image-id]').forEach(container => {
        const containerImageId = container.getAttribute('data-image-id');
        if (containerImageId !== imageId.toString()) {
            const profileIndicator = container.querySelector('.mt-1.flex.justify-center');
            if (profileIndicator) {
                profileIndicator.style.display = 'none';
            }
        }
    });
    
    // 3. Establecer nueva imagen de perfil (ponerla amarilla)
    const targetBtn = document.querySelector(`.profile-btn[data-image-id="${imageId}"]`);
    if (targetBtn) {
        console.log('Encontrado botón objetivo:', targetBtn);
        
        // Cambiar botón a amarillo
        targetBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
        targetBtn.classList.add('bg-yellow-600');
        targetBtn.setAttribute('data-is-profile', 'true');
        
        console.log('Botón cambiado a amarillo');
        
        // Mostrar indicador de perfil
        // El botón está dentro del contenedor, necesitamos subir al contenedor padre
        const imageContainer = targetBtn.parentElement.parentElement; // subir dos niveles: div de botones -> contenedor
        if (imageContainer && imageContainer.hasAttribute('data-image-id')) {
            console.log('Contenedor de imagen encontrado:', imageContainer);
            
            let profileIndicator = imageContainer.querySelector('.mt-1.flex.justify-center');
            if (!profileIndicator) {
                console.log('Creando nuevo indicador de perfil');
                profileIndicator = document.createElement('div');
                profileIndicator.className = 'mt-1 flex justify-center';
                profileIndicator.innerHTML = '<span class="bg-yellow-500 text-white rounded-full px-2 py-1 text-xs flex items-center gap-1"><i class="fa-solid fa-star"></i><span>Perfil</span></span>';
                
                // Insertar después de la imagen pero antes de los botones
                const imageDiv = imageContainer.querySelector('.aspect-square');
                if (imageDiv) {
                    imageDiv.insertAdjacentElement('afterend', profileIndicator);
                } else {
                    console.error('No se encontró el div de la imagen');
                }
            }
            profileIndicator.style.display = 'flex';
            console.log('Indicador de perfil mostrado');
        } else {
            console.error('No se encontró el contenedor de imagen');
        }
        
        // Actualizar en el array
        if (typeof uploadedImages !== 'undefined') {
            uploadedImages.forEach(img => {
                img.isProfile = (img.id === imageId);
            });
        }
        
        console.log('Imagen de perfil establecida:', imageId);
    } else {
        console.error('No se encontró el botón objetivo para imageId:', imageId);
    }
}

// Eliminar imagen
function deleteImage(imageId) {
    console.log('Eliminando imagen:', imageId);
    
    // Si es una imagen existente (no temporal), usar AJAX
    if (!imageId.toString().startsWith('temp_')) {
        deleteImageExisting(imageId);
        return;
    }
    
    // Para imágenes temporales (nuevas)
    if (confirm('¿Estás seguro de eliminar esta imagen?')) {
        // Remover del DOM
        const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
        if (imageElement) {
            imageElement.remove();
        }
        
        // Remover del array
        uploadedImages = uploadedImages.filter(img => img.id !== imageId);
        
        // Si era la imagen de perfil, establecer la primera como perfil
        if (uploadedImages.length > 0) {
            const firstImage = uploadedImages[0];
            firstImage.isProfile = true;
            setAsProfile(firstImage.id);
        }
        
        updateNoImagesMessage();
    }
}

// Actualizar mensaje de "no hay imágenes"
function updateNoImagesMessage() {
    const imageGallery = document.getElementById('imageGallery');
    const noImagesMessage = document.getElementById('noImagesMessage');
    
    if (imageGallery && noImagesMessage) {
        const hasImages = imageGallery.children.length > 0;
        noImagesMessage.style.display = hasImages ? 'none' : 'block';
    }
}

// Función para actualizar el input oculto con las imágenes temporales
function updateHiddenFileInput() {
    const tempInput = document.getElementById('tempImageInput');
    if (!tempInput) return;
    
    console.log('=== UPDATE HIDDEN FILE INPUT DEBUG ===');
    console.log('Updating hidden file input with', uploadedImages.length, 'images');
    
    // Crear un nuevo DataTransfer para manejar múltiples archivos
    const dataTransfer = new DataTransfer();
    
    uploadedImages.forEach((img, index) => {
        if (img.file) {
            console.log(`Adding file ${index} to DataTransfer:`, img.file.name);
            dataTransfer.items.add(img.file);
        }
    });
    
    // Asignar los archivos al input
    tempInput.files = dataTransfer.files;
    
    console.log('Hidden input now has', tempInput.files.length, 'files');
}

// Función para establecer imagen de perfil (para imágenes existentes)
function setAsProfileExisting(imageId) {
    console.log('=== SET AS PROFILE EXISTING DEBUG ===');
    console.log('Estableciendo imagen de perfil existente:', imageId);
    
    // Obtener el ID del vehículo desde el formulario o URL
    const vehicleId = getVehicleId();
    console.log('Vehicle ID obtained:', vehicleId);
    
    if (!vehicleId) {
        console.error('No se pudo obtener el ID del vehículo');
        alert('Error: No se pudo obtener el ID del vehículo');
        return;
    }
    
    const url = `/vehicles/${vehicleId}/images/${imageId}/profile`;
    console.log('Making request to:', url);
    
    // Hacer petición AJAX
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Actualizar la UI
            updateProfileUI(imageId);
            console.log('Imagen de perfil actualizada:', data.message);
        } else {
            console.error('Error al actualizar imagen de perfil:', data.message);
            alert('Error al actualizar imagen de perfil: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        alert('Error al actualizar imagen de perfil: ' + error.message);
    });
}

// Función para obtener el ID del vehículo
function getVehicleId() {
    console.log('=== GET VEHICLE ID DEBUG ===');
    
    // Intentar obtener desde el formulario (para edición)
    const form = document.querySelector('form');
    console.log('Form found:', form);
    if (form && form.action) {
        console.log('Form action:', form.action);
        const matches = form.action.match(/\/vehicles\/(\d+)/);
        if (matches) {
            console.log('Vehicle ID from form action:', matches[1]);
            return matches[1];
        }
    }
    
    // Intentar obtener desde la URL actual
    console.log('Current URL:', window.location.pathname);
    const urlMatches = window.location.pathname.match(/\/vehicles\/(\d+)/);
    if (urlMatches) {
        console.log('Vehicle ID from URL:', urlMatches[1]);
        return urlMatches[1];
    }
    
    // Intentar obtener desde un campo oculto en el formulario
    const vehicleIdInput = document.querySelector('input[name="vehicle_id"]');
    if (vehicleIdInput) {
        console.log('Vehicle ID from hidden input:', vehicleIdInput.value);
        return vehicleIdInput.value;
    }
    
    // Intentar obtener desde el atributo data del modal
    const modal = document.querySelector('[id*="Modal"]');
    if (modal) {
        const vehicleId = modal.getAttribute('data-vehicle-id');
        if (vehicleId) {
            console.log('Vehicle ID from modal data:', vehicleId);
            return vehicleId;
        }
    }
    
    // Intentar obtener desde el botón que abrió el modal
    const editButtons = document.querySelectorAll('[data-turbo-frame="modal-frame"]');
    editButtons.forEach(button => {
        const href = button.getAttribute('href');
        if (href) {
            const matches = href.match(/\/vehicles\/(\d+)\/edit/);
            if (matches) {
                console.log('Vehicle ID from edit button:', matches[1]);
                return matches[1];
            }
        }
    });
    
    console.log('No vehicle ID found');
    return null;
}

// Función para actualizar la UI después de establecer perfil
function updateProfileUI(profileImageId) {
    console.log('=== UPDATE PROFILE UI DEBUG ===');
    console.log('Updating UI for profile image ID:', profileImageId);
    
    // Remover perfil de todas las imágenes existentes
    document.querySelectorAll('[data-image-id]').forEach(img => {
        const containerImageId = img.getAttribute('data-image-id');
        
        // Solo procesar imágenes existentes (no temporales)
        if (!containerImageId.toString().startsWith('temp_')) {
            console.log('Processing existing image:', containerImageId);
            
            // Remover indicador de perfil
            const profileIndicator = img.querySelector('.mt-1.flex.justify-center');
            if (profileIndicator) {
                profileIndicator.style.display = 'none';
            }
            
            // Cambiar botón a verde (no perfil)
            const profileBtn = img.querySelector('.profile-btn');
            if (profileBtn) {
                profileBtn.classList.remove('bg-yellow-600');
                profileBtn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                profileBtn.setAttribute('data-is-profile', 'false');
            }
        }
    });
    
    // Establecer nueva imagen de perfil
    const targetImage = document.querySelector(`[data-image-id="${profileImageId}"]`);
    if (targetImage) {
        console.log('Found target image container');
        
        // Mostrar indicador de perfil
        let profileIndicator = targetImage.querySelector('.mt-1.flex.justify-center');
        if (!profileIndicator) {
            console.log('Creating new profile indicator');
            profileIndicator = document.createElement('div');
            profileIndicator.className = 'mt-1 flex justify-center';
            profileIndicator.innerHTML = '<span class="bg-yellow-500 text-white rounded-full px-2 py-1 text-xs flex items-center gap-1"><i class="fa-solid fa-star"></i><span>Perfil</span></span>';
            
            // Insertar después de la imagen pero antes de los botones
            const imageDiv = targetImage.querySelector('.aspect-square');
            if (imageDiv) {
                imageDiv.insertAdjacentElement('afterend', profileIndicator);
            }
        }
        profileIndicator.style.display = 'flex';
        
        // Cambiar botón a amarillo (perfil)
        const profileBtn = targetImage.querySelector('.profile-btn');
        if (profileBtn) {
            profileBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
            profileBtn.classList.add('bg-yellow-600');
            profileBtn.setAttribute('data-is-profile', 'true');
        }
        
        console.log('Profile UI updated successfully');
    } else {
        console.error('Target image container not found for ID:', profileImageId);
    }
}

// Función para eliminar imagen (para imágenes existentes)
function deleteImageExisting(imageId) {
    if (!confirm('¿Estás seguro de eliminar esta imagen?')) {
        return;
    }
    
    console.log('Eliminando imagen existente:', imageId);
    
    // Obtener el ID del vehículo
    const vehicleId = getVehicleId();
    if (!vehicleId) {
        console.error('No se pudo obtener el ID del vehículo');
        return;
    }
    
    // Hacer petición AJAX
    fetch(`/vehicles/${vehicleId}/images/${imageId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remover del DOM
            const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
            if (imageElement) {
                imageElement.remove();
            }
            updateNoImagesMessage();
            console.log('Imagen eliminada:', data.message);
        } else {
            console.error('Error al eliminar imagen:', data.message);
            alert('Error al eliminar imagen: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        alert('Error al eliminar imagen');
    });
}

// Exponer funciones globalmente para que puedan ser llamadas desde HTML
window.setAsProfile = setAsProfile;
window.deleteImage = deleteImage;
window.setAsProfileExisting = setAsProfileExisting;
window.deleteImageExisting = deleteImageExisting;

})(); // Cerrar función anónima auto-ejecutable
</script>
