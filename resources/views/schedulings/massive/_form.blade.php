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
         📅 RANGO DE FECHAS
    ============================ --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50/60 hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-calendar text-emerald-600"></i> Rango de Fechas
        </legend>

        {{-- Fechas de inicio y fin --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de Inicio <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i class="fa-solid fa-calendar-day absolute left-3 top-2.5 text-slate-400"></i>
                    <input type="date" name="start_date"
                        value="{{ old('start_date', date('Y-m-d')) }}"
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('start_date') border-red-500 @enderror"
                        min="{{ date('Y-m-d') }}">
                </div>
                @error('start_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de Fin <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i class="fa-solid fa-calendar-check absolute left-3 top-2.5 text-slate-400"></i>
                    <input type="date" name="end_date"
                        value="{{ old('end_date', date('Y-m-d', strtotime('+7 days'))) }}"
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('end_date') border-red-500 @enderror"
                        min="{{ date('Y-m-d') }}">
                </div>
                @error('end_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Estado --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
            <div class="relative">
                <i class="fa-solid fa-circle-check absolute left-3 top-2.5 text-slate-400"></i>
                <select name="status"
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('status') border-red-500 @enderror">
                    <option value="">Seleccionar estado...</option>
                    <option value="0" {{ old('status', '0') == '0' ? 'selected' : '' }}>Pendiente</option>
                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>En Proceso</option>
                    <option value="2" {{ old('status') == '2' ? 'selected' : '' }}>Completado</option>
                    <option value="3" {{ old('status') == '3' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            @error('status')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Notas --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Notas</label>
            <textarea name="notes" rows="3"
                class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                placeholder="Notas adicionales para todas las programaciones...">{{ old('notes') }}</textarea>
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
                                {{ old('group_id') == $group->id ? 'selected' : '' }}>
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
                                {{ old('schedule_id') == $schedule->id ? 'selected' : '' }}>
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
                                {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
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
                                {{ old('zone_id') == $zone->id ? 'selected' : '' }}>
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
<div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
    <button type="button"
        onclick="FlyonUI.modal.close('massiveModal')"
        class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark mr-1"></i> Cancelar
    </button>
    <button type="submit"
        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
        <i class="fa-solid fa-calendar-plus"></i> {{ $buttonText }}
    </button>
</div>

{{-- Scripts para validaciones --}}
<script>
(function() {
    'use strict';
    
    // Validación del formulario antes de enviar
    document.querySelector('form').addEventListener('submit', function(e) {
        const requiredFields = [
            { name: 'start_date', label: 'Fecha de inicio' },
            { name: 'end_date', label: 'Fecha de fin' },
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
        
        // Validar que la fecha de fin sea posterior a la fecha de inicio
        const startDate = document.querySelector('[name="start_date"]').value;
        const endDate = document.querySelector('[name="end_date"]').value;
        
        if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
            hasErrors = true;
            errorMessages.push('La fecha de fin debe ser posterior a la fecha de inicio');
            
            const endDateElement = document.querySelector('[name="end_date"]');
            endDateElement.classList.add('border-red-500');
            endDateElement.classList.remove('border-slate-300');
        }
        
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

    // Validación de fechas en tiempo real
    document.querySelector('input[name="start_date"]').addEventListener('change', function(e) {
        const endDateInput = document.querySelector('input[name="end_date"]');
        const startDate = new Date(e.target.value);
        const endDate = new Date(endDateInput.value);
        
        if (endDate < startDate) {
            endDateInput.setCustomValidity('La fecha de fin debe ser posterior a la fecha de inicio');
            endDateInput.classList.add('border-red-500');
            endDateInput.classList.remove('border-slate-300');
        } else {
            endDateInput.setCustomValidity('');
            endDateInput.classList.remove('border-red-500');
            endDateInput.classList.add('border-slate-300');
        }
    });

    document.querySelector('input[name="end_date"]').addEventListener('change', function(e) {
        const startDateInput = document.querySelector('input[name="start_date"]');
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(e.target.value);
        
        if (endDate < startDate) {
            e.target.setCustomValidity('La fecha de fin debe ser posterior a la fecha de inicio');
            e.target.classList.add('border-red-500');
            e.target.classList.remove('border-slate-300');
        } else {
            e.target.setCustomValidity('');
            e.target.classList.remove('border-red-500');
            e.target.classList.add('border-slate-300');
        }
    });

    // Validación en tiempo real para campos obligatorios
    const requiredFields = ['start_date', 'end_date', 'group_id', 'schedule_id'];

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