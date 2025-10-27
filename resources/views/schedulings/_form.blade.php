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

<div class="space-y-6">
    {{-- ===========================
         📅 DATOS DE LA PROGRAMACIÓN
    ============================ --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50/60 hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-calendar text-emerald-600"></i> Datos de la programación
        </legend>

        {{-- Fecha y Estado en una fila --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Fecha <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i class="fa-solid fa-calendar absolute left-3 top-2.5 text-slate-400"></i>
                    <input type="date" name="date"
                        value="{{ old('date', isset($scheduling) ? $scheduling->date->format('Y-m-d') : '') }}"
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('date') border-red-500 @enderror"
                        min="{{ date('Y-m-d') }}">
                </div>
                @error('date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Estado <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i class="fa-solid fa-circle-check absolute left-3 top-2.5 text-slate-400"></i>
                    <select name="status"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('status') border-red-500 @enderror">
                        <option value="">Seleccionar estado...</option>
                        <option value="0" {{ old('status', $scheduling->status ?? '') == '0' ? 'selected' : '' }}>Pendiente</option>
                        <option value="1" {{ old('status', $scheduling->status ?? '') == '1' ? 'selected' : '' }}>En Proceso</option>
                        <option value="2" {{ old('status', $scheduling->status ?? '') == '2' ? 'selected' : '' }}>Completado</option>
                        <option value="3" {{ old('status', $scheduling->status ?? '') == '3' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Notas --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Notas</label>
            <textarea name="notes" rows="3"
                class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                placeholder="Notas adicionales sobre la programación...">{{ old('notes', $scheduling->notes ?? '') }}</textarea>
        </div>
    </fieldset>

    {{-- ===========================
         👥 ASIGNACIONES
    ============================ --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-white hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-users text-emerald-600"></i> Asignaciones
        </legend>

        {{-- Grupo y Horario en una fila --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Grupo de Empleados <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i class="fa-solid fa-users absolute left-3 top-2.5 text-slate-400"></i>
                    <select name="group_id"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('group_id') border-red-500 @enderror">
                        <option value="">Seleccionar grupo...</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}"
                                {{ old('group_id', $scheduling->group_id ?? '') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('group_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Horario <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i class="fa-solid fa-clock absolute left-3 top-2.5 text-slate-400"></i>
                    <select name="schedule_id"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('schedule_id') border-red-500 @enderror">
                        <option value="">Seleccionar horario...</option>
                        @foreach($schedules as $schedule)
                            <option value="{{ $schedule->id }}"
                                {{ old('schedule_id', $scheduling->schedule_id ?? '') == $schedule->id ? 'selected' : '' }}>
                                {{ $schedule->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('schedule_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Vehículo y Zona en una fila --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Vehículo</label>
                <div class="relative">
                    <i class="fa-solid fa-truck absolute left-3 top-2.5 text-slate-400"></i>
                    <select name="vehicle_id"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('vehicle_id') border-red-500 @enderror">
                        <option value="">Seleccionar vehículo...</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}"
                                {{ old('vehicle_id', $scheduling->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->name }} ({{ $vehicle->plate }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('vehicle_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Zona</label>
                <div class="relative">
                    <i class="fa-solid fa-map-marker-alt absolute left-3 top-2.5 text-slate-400"></i>
                    <select name="zone_id"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('zone_id') border-red-500 @enderror">
                        <option value="">Seleccionar zona...</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}"
                                {{ old('zone_id', $scheduling->zone_id ?? '') == $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('zone_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </fieldset>
</div>

{{-- BOTONES --}}
@php
    $modalId = isset($scheduling) && isset($scheduling->id) ? 'editModal' : 'createModal';
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

{{-- Scripts para validaciones --}}
<script>
(function() {
    'use strict';
    
    // Validación del formulario antes de enviar
    document.querySelector('form').addEventListener('submit', function(e) {
        const requiredFields = [
            { name: 'date', label: 'Fecha' },
            { name: 'status', label: 'Estado' },
            { name: 'group_id', label: 'Grupo de empleados' },
            { name: 'schedule_id', label: 'Horario' }
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
    });

    // Validación de fecha en tiempo real
    document.querySelector('input[name="date"]').addEventListener('change', function(e) {
        const selectedDate = new Date(e.target.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            e.target.setCustomValidity('La fecha no puede ser anterior a hoy');
            e.target.classList.add('border-red-500');
            e.target.classList.remove('border-slate-300');
        } else {
            e.target.setCustomValidity('');
            e.target.classList.remove('border-red-500');
            e.target.classList.add('border-slate-300');
        }
    });

    // Validación en tiempo real para campos obligatorios
    const requiredFields = ['date', 'status', 'group_id', 'schedule_id'];

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
})();
</script>
