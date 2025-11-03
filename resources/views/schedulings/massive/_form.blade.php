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
                <div class="flex items-center gap-3">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-calendar-check absolute left-3 top-2.5 text-slate-400"></i>
                        <input type="date" name="end_date"
                            value="{{ old('end_date', date('Y-m-d', strtotime('+7 days'))) }}"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('end_date') border-red-500 @enderror"
                            min="{{ date('Y-m-d') }}">
                    </div>

                    <div class="shrink-0">
                        <button type="button" id="validate_availability"
                                class="px-4 py-2 rounded-lg border border-emerald-300 text-emerald-700 bg-white hover:bg-emerald-50 transition flex items-center gap-2">
                            <i class="fa-solid fa-calendar-check text-emerald-600"></i>
                            Validar Disponibilidad
                        </button>
                    </div>
                </div>

                @error('end_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

    {{-- (Se eliminaron Estado, Notas y Días de la semana para ajustarse al diseño) --}}
        
        {{-- ===========================
             📋 Grupos existentes (lista debajo de las fechas)
        ============================ --}}
        <div class="mt-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Grupos registrados</label>

            @if($groups->isEmpty())
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-600">No hay grupos registrados aún.</div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($groups as $group)
                        <div class="border border-slate-200 rounded-lg p-4 bg-white shadow-sm">
                            <div class="flex items-start justify-between">
                                <h4 class="text-sm font-semibold uppercase text-slate-700">{{ $group->name }}</h4>
                                <button type="button" class="text-red-600 bg-red-50 rounded px-2 py-1 text-xs">Eliminar</button>
                            </div>

                            <div class="mt-3 text-xs text-slate-600 space-y-1">
                                <div><strong>Zona:</strong> {{ optional($group->zone)->name ?? '-' }}</div>
                                <div><strong>Horario:</strong> {{ optional($group->schedule)->name ?? '-' }}</div>
                                <div><strong>Miembros:</strong> {{ $group->employees->count() ?? ($group->employees_count ?? '-') }}</div>
                            </div>

                            <div class="mt-3">
                                <div class="flex items-center justify-between">
                                    <button type="button" data-group-id="{{ $group->id }}" class="select-group-btn px-3 py-1 bg-emerald-600 text-white rounded text-sm">
                                        Seleccionar
                                    </button>
                                    <div class="group-status text-sm text-slate-600 ml-3"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            {{-- Contenedor para mostrar resultados de validación masiva --}}
            <div id="massive_validation_results" class="mt-4"></div>
        </div>
    </fieldset>

    {{-- (Se eliminó el bloque de Asignaciones; la selección y asignaciones se realizan dentro de cada tarjeta de grupo en la sección superior) --}}
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

    // --- Selección de grupos y validación previa (AJAX) ---
    const selectedGroupIds = new Set();

    document.querySelectorAll('.select-group-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const groupId = this.getAttribute('data-group-id');
            const card = this.closest('.border');

            if (selectedGroupIds.has(groupId)) {
                selectedGroupIds.delete(groupId);
                this.textContent = 'Seleccionar';
                card.classList.remove('ring-2', 'ring-emerald-300');
            } else {
                selectedGroupIds.add(groupId);
                this.textContent = 'Seleccionado';
                card.classList.add('ring-2', 'ring-emerald-300');
            }
        });
    });

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        const input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    function renderValidationResults(groups) {
        // Clear previous statuses
        document.querySelectorAll('.group-status').forEach(el => { el.textContent = ''; el.className = 'group-status text-sm text-slate-600 ml-3'; });

        const container = document.getElementById('massive_validation_results');
        container.innerHTML = '';

        const list = document.createElement('div');
        list.className = 'space-y-3';

        groups.forEach(g => {
            // Update card status
            const btn = document.querySelector(`.select-group-btn[data-group-id="${g.group_id}"]`);
            const statusEl = btn ? btn.closest('.border').querySelector('.group-status') : null;

            const cardStatus = document.createElement('span');
            if (g.ok) {
                cardStatus.textContent = 'OK';
                cardStatus.className = 'text-green-600';
            } else {
                cardStatus.textContent = 'Problemas';
                cardStatus.className = 'text-red-600';
            }
            if (statusEl) { statusEl.innerHTML = ''; statusEl.appendChild(cardStatus); }

            // Build result entry
            const entry = document.createElement('div');
            entry.className = 'p-3 border border-slate-200 rounded-lg bg-white';

            const title = document.createElement('div');
            title.className = 'flex items-center justify-between';
            title.innerHTML = `<strong class="text-sm">${g.group_name}</strong>`;
            const state = document.createElement('div');
            state.className = g.ok ? 'text-sm text-green-600' : 'text-sm text-red-600';
            state.textContent = g.ok ? 'OK' : 'Problemas';
            title.appendChild(state);

            entry.appendChild(title);

            if (!g.ok && g.inconsistencies && g.inconsistencies.length) {
                const ul = document.createElement('ul');
                ul.className = 'mt-2 list-disc list-inside text-sm text-red-600 space-y-1';
                g.inconsistencies.forEach(msg => {
                    const li = document.createElement('li');
                    li.textContent = msg;
                    ul.appendChild(li);
                });
                entry.appendChild(ul);
            } else {
                const p = document.createElement('div');
                p.className = 'mt-2 text-sm text-slate-600';
                p.textContent = g.ok ? 'No se encontraron inconsistencias.' : '';
                entry.appendChild(p);
            }

            list.appendChild(entry);
        });

        container.appendChild(list);
        container.scrollIntoView({ behavior: 'smooth' });
    }

    document.getElementById('validate_availability').addEventListener('click', function(e) {
        e.preventDefault();

        const btn = this;
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Validando...';

        const start_date = document.querySelector('[name="start_date"]').value;
        const end_date = document.querySelector('[name="end_date"]').value;
        const scheduleInput = document.querySelector('[name="schedule_id"]');
        const schedule_id = scheduleInput ? scheduleInput.value : null;
        const vehicleInput = document.querySelector('[name="vehicle_id"]');
        const vehicle_id = vehicleInput ? vehicleInput.value : null;

        const payload = { start_date, end_date, schedule_id };

        // Determine groups to validate: none selected -> all_groups, one selected -> group_id, multiple -> all_groups
        if (selectedGroupIds.size === 1) {
            payload.group_id = Array.from(selectedGroupIds)[0];
        } else {
            payload.all_groups = 1;
        }

        if (vehicle_id) payload.vehicle_id = vehicle_id;

        const token = getCsrfToken();

    fetch("{{ route('schedulings.validate-massive') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
        })
        .then(resp => resp.json())
        .then(data => {
            if (!data || !data.groups) {
                alert('Respuesta inválida del servidor.');
                return;
            }

            renderValidationResults(data.groups);
        })
        .catch(err => {
            console.error(err);
            alert('Error al validar disponibilidad. Revisa la consola.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });
})();
</script>
