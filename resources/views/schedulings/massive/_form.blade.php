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
    {{-- Selector para filtrar por turno colocado arriba del rango de fechas --}}
    <div class="mb-3">
        <label class="block text-sm text-slate-600 mb-1">Seleccionar turno/horario <span class="text-red-500">*</span></label>
    <select id="filter_schedule_select_top" name="schedule_id" required class="w-full md:w-1/3 pl-3 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('schedule_id') border-red-500 @enderror">
            @foreach($schedules as $s)
                <option value="{{ $s->id }}" {{ old('schedule_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        @error('schedule_id')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
    {{-- ===========================
         📅 RANGO DE FECHAS
    ============================ --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50/60 hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-calendar text-emerald-600"></i> Rango de Fechas
        </legend>

        {{-- Fechas de inicio y fin con botón de validación --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3 items-end">
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

            <div>
                <button type="button" id="validate_availability"
                        class="w-full px-4 py-2 rounded-lg border border-emerald-300 text-emerald-700 bg-white hover:bg-emerald-50 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-calendar-check text-emerald-600"></i>
                    Validar Disponibilidad
                </button>
            </div>
        </div>

    {{-- (Se eliminaron Estado, Notas y Días de la semana para ajustarse al diseño) --}}
        
        {{-- ===========================
             📋 Grupos existentes (lista debajo de las fechas)
        ============================ --}}
        <div class="mt-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Grupos registrados</label>
            {{-- (El selector de turno está arriba del rango de fechas) --}}

            @if($groups->isEmpty())
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-600">No hay grupos registrados aún.</div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($groups as $group)
                        @php
                            $driverId = $group->driver?->id ?? '';
                            $helper1Id = $group->helper1?->id ?? '';
                            $helper2Id = $group->helper2?->id ?? '';
                            $vehicleId = optional($group->vehicle)->id ?? '';
                            $daysJson = json_encode($group->days_array ?: []);
                        @endphp
                        <div class="border border-slate-200 rounded-lg p-4 bg-white shadow-sm group-card" data-schedule-id="{{ optional($group->schedule)->id }}" data-group-id="{{ $group->id }}" data-driver-id="{{ $driverId }}" data-helper1-id="{{ $helper1Id }}" data-helper2-id="{{ $helper2Id }}" data-vehicle-id="{{ $vehicleId }}" data-days='@json($group->days_array ?? [])'>
                            <div class="flex items-start justify-between">
                                <h4 class="text-sm font-semibold uppercase text-slate-700">{{ $group->name }}</h4>
                                <button type="button" class="remove-group-btn text-red-600 bg-red-50 hover:bg-red-100 rounded px-2 py-1 text-xs transition" data-group-id="{{ $group->id }}">Eliminar</button>
                            </div>

                            <div class="mt-3 text-xs text-slate-600 space-y-1">
                                <div><strong>Zona:</strong> {{ optional($group->zone)->name ?? '-' }}</div>
                                <div><strong>Horario:</strong> {{ optional($group->schedule)->name ?? '-' }}</div>
                                <div><strong>Días:</strong>
                                    @php
                                        $daysArr = $group->days_array ?? (is_array($group->days) ? $group->days : (empty($group->days) ? [] : json_decode($group->days, true)));
                                        $daysMap = ['lunes'=>'Lun','martes'=>'Mar','miercoles'=>'Mié','jueves'=>'Jue','viernes'=>'Vie','sabado'=>'Sáb','domingo'=>'Dom'];
                                        $daysNames = array_map(fn($d) => $daysMap[$d] ?? $d, $daysArr ?: []);
                                    @endphp
                                    {{ $daysNames ? implode(', ', $daysNames) : '-' }}</div>
                                <div><strong>Vehículo:</strong> {{ optional($group->vehicle)->name ?? '-' }}</div>
                                <div><strong>Conductor:</strong> {{ optional($group->driver)->firstname ? optional($group->driver)->firstname . ' ' . optional($group->driver)->lastname : '-' }}</div>
                                <div><strong>Ayudantes:</strong>
                                    @php
                                        $h1 = optional($group->helper1);
                                        $h2 = optional($group->helper2);
                                        $helpers = [];
                                        if ($h1 && $h1->id) $helpers[] = $h1->firstname . ' ' . $h1->lastname;
                                        if ($h2 && $h2->id) $helpers[] = $h2->firstname . ' ' . $h2->lastname;
                                    @endphp
                                    {{ count($helpers) ? implode(', ', $helpers) : '-' }}</div>
                            </div>

                                <div class="mt-3">
                                <div class="flex items-center justify-between">
                                    <div class="group-status text-sm"></div>
                                    <button type="button" class="edit-group-btn text-xs px-2 py-1 bg-amber-50 text-amber-700 rounded" data-group-id="{{ $group->id }}">Editar</button>
                                </div>
                                {{-- Contenedor para mostrar inconsistencias específicas del grupo --}}
                                <div class="group-inconsistencies mt-2 hidden">
                                    <div class="bg-red-50 border border-red-200 rounded p-2 text-xs text-red-700">
                                        <div class="font-semibold mb-1">⚠️ Problemas encontrados:</div>
                                        <ul class="list-disc list-inside space-y-0.5 inconsistencies-list"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
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
    <button type="submit" id="submit_scheduling_btn" disabled
        class="px-4 py-2 rounded-lg bg-slate-300 text-slate-600 cursor-not-allowed transition flex items-center gap-2 pointer-events-none">
        <i class="fa-solid fa-calendar-plus"></i> {{ $buttonText }}
    </button>
</div>

{{-- Campo oculto para indicar programación para todos los grupos (filtrados por horario) --}}
<input type="hidden" name="all_groups" value="1">
<input type="hidden" name="filter_schedule" id="filter_schedule_hidden">

{{-- Scripts para validaciones --}}
<script>
(function() {
    'use strict';

    function parseResponseJson(resp){
        return resp.text().then(text => {
            try { return JSON.parse(text); }
            catch(e){ throw new Error('Respuesta inválida (no JSON): ' + (text ? text.substring(0,200) : '<vacío>')); }
        });
    }

    // Helper to get CSRF token
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        const input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    // Safe DOM helpers
    const formEl = document.querySelector('form');
    if (formEl) {
        formEl.addEventListener('submit', function(e) {
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
                    const value = (element.value || '').toString().trim();
                    if (!value) {
                        hasErrors = true;
                        errorMessages.push(`${field.label} es obligatorio`);
                        element.classList.add('border-red-500');
                        element.classList.remove('border-slate-300');
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'field-error text-red-500 text-xs mt-1';
                        errorDiv.textContent = `${field.label} es obligatorio`;
                        element.parentNode.appendChild(errorDiv);
                    }
                }
            });

            // Validar que la fecha de fin sea posterior a la fecha de inicio
            const startInput = document.querySelector('[name="start_date"]');
            const endInput = document.querySelector('[name="end_date"]');
            const startDate = startInput ? startInput.value : null;
            const endDate = endInput ? endInput.value : null;

            if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                hasErrors = true;
                errorMessages.push('La fecha de fin debe ser posterior a la fecha de inicio');
                if (endInput) { endInput.classList.add('border-red-500'); endInput.classList.remove('border-slate-300'); }
            }

            if (hasErrors) {
                e.preventDefault();
                const generalError = document.createElement('div');
                generalError.className = 'mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg';
                generalError.innerHTML = `
                    <h4 class="font-semibold mb-2">Por favor completa todos los campos obligatorios:</h4>
                    <ul class="list-disc list-inside space-y-1">
                        ${errorMessages.map(msg => `<li class="text-sm">${msg}</li>`).join('')}
                    </ul>
                `;
                formEl.insertBefore(generalError, formEl.firstChild);
                generalError.scrollIntoView({ behavior: 'smooth' });
                return false;
            }
        });
    }

    // Date inputs listeners (guarded)
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function(e) {
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

        endDateInput.addEventListener('change', function(e) {
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
    }

    // --- Filtrado de grupos por turno/horario ---
    const filterSelect = document.getElementById('filter_schedule_select_top');
    const filterScheduleHidden = document.getElementById('filter_schedule_hidden');
    
    function applyScheduleFilter() {
        const val = filterSelect ? filterSelect.value : '';
        const cards = document.querySelectorAll('[data-schedule-id]');
        let visibleCount = 0;
        
        // Actualizar campo oculto para envío del formulario
        if (filterScheduleHidden && val) {
            filterScheduleHidden.value = val;
        }
        
        cards.forEach(card => {
            // No mostrar grupos marcados como eliminados
            if (card.classList.contains('removed-from-list')) {
                card.style.display = 'none';
                return;
            }
            
            const cardSchedule = card.getAttribute('data-schedule-id') || '';
            // Si hay un horario seleccionado, mostrar solo los grupos de ese horario
            // Si no hay horario seleccionado, mostrar todos
            if (!val || val === '' || cardSchedule === val) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Mostrar mensaje si no hay grupos visibles
        const container = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.md\\:grid-cols-3');
        if (container) {
            let noGroupsMsg = container.querySelector('.no-groups-message');
            if (visibleCount === 0) {
                if (!noGroupsMsg) {
                    noGroupsMsg = document.createElement('div');
                    noGroupsMsg.className = 'no-groups-message col-span-full p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700';
                    noGroupsMsg.textContent = 'No hay grupos registrados para este horario. Seleccione otro horario o cree grupos primero.';
                    container.appendChild(noGroupsMsg);
                }
            } else {
                if (noGroupsMsg) noGroupsMsg.remove();
            }
        }
    }
    if (filterSelect) { 
        filterSelect.addEventListener('change', applyScheduleFilter); 
        applyScheduleFilter(); 
    }

    function renderValidationResults(groups) {
        // Limpiar estados previos de todos los grupos
        document.querySelectorAll('.group-status').forEach(el => {
            el.textContent = '';
            el.className = 'group-status text-sm';
        });
        document.querySelectorAll('.group-inconsistencies').forEach(el => {
            el.classList.add('hidden');
        });

        let allGroupsOk = true;
        let hasVisibleGroups = false;

        groups.forEach(g => {
            const card = document.querySelector(`[data-group-id="${g.group_id}"]`);
            if (!card) return;

            hasVisibleGroups = true;

            const statusEl = card.querySelector('.group-status');
            const inconsistenciesContainer = card.querySelector('.group-inconsistencies');
            const inconsistenciesList = card.querySelector('.inconsistencies-list');

            if (g.ok) {
                // Grupo sin problemas
                if (statusEl) {
                    statusEl.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium"><i class="fa-solid fa-circle-check"></i> Disponible</span>';
                }
                if (inconsistenciesContainer) {
                    inconsistenciesContainer.classList.add('hidden');
                }
            } else {
                // Grupo con problemas
                allGroupsOk = false;
                if (statusEl) {
                    statusEl.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-medium"><i class="fa-solid fa-circle-xmark"></i> No disponible</span>';
                }
                
                // Mostrar inconsistencias específicas del grupo
                if (inconsistenciesContainer && inconsistenciesList && g.inconsistencies && g.inconsistencies.length) {
                    inconsistenciesList.innerHTML = '';
                    g.inconsistencies.forEach(msg => {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        inconsistenciesList.appendChild(li);
                    });
                    inconsistenciesContainer.classList.remove('hidden');
                }
            }
        });

        // Habilitar o deshabilitar el botón de submit según los resultados
        const submitBtn = document.getElementById('submit_scheduling_btn');
        if (submitBtn) {
            if (hasVisibleGroups && allGroupsOk) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('bg-slate-300', 'text-slate-600', 'cursor-not-allowed', 'pointer-events-none');
                submitBtn.classList.add('bg-emerald-600', 'text-white', 'hover:bg-emerald-700', 'pointer-events-auto');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.remove('bg-emerald-600', 'text-white', 'hover:bg-emerald-700', 'pointer-events-auto');
                submitBtn.classList.add('bg-slate-300', 'text-slate-600', 'cursor-not-allowed', 'pointer-events-none');
            }
        }

        // Scroll al primer grupo con problemas si existe
        const firstProblem = document.querySelector('.group-inconsistencies:not(.hidden)');
        if (firstProblem) {
            firstProblem.closest('[data-group-id]').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Validate availability button
    const validateBtn = document.getElementById('validate_availability');
    if (validateBtn) {
        validateBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const btn = this; btn.disabled = true; const originalText = btn.innerHTML; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Validando...';

            const start_date = (document.querySelector('[name="start_date"]') || {}).value || '';
            const end_date = (document.querySelector('[name="end_date"]') || {}).value || '';
            const scheduleInput = document.querySelector('[name="schedule_id"]');
            const schedule_id = scheduleInput ? scheduleInput.value : null;

            // Obtener solo los IDs de las tarjetas principales de grupos visibles (no eliminados)
            // Usar .group-card para asegurar que solo seleccionamos las tarjetas principales
            const visibleCards = document.querySelectorAll('.group-card:not(.removed-from-list)');
            const visibleGroupIds = Array.from(visibleCards)
                .filter(card => card.style.display !== 'none')
                .map(card => card.getAttribute('data-group-id'))
                .filter((id, index, self) => id && self.indexOf(id) === index); // Eliminar duplicados y nulls

            console.log('IDs de grupos a validar:', visibleGroupIds);

            // Solo enviar group_ids, sin filter_schedule ni all_groups
            const payload = { 
                start_date, 
                end_date, 
                schedule_id, 
                group_ids: visibleGroupIds 
            };

            console.log('Payload a enviar:', payload);

            const token = getCsrfToken();

            fetch("{{ route('schedulings.validate-massive') }}", {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify(payload)
            }).then(resp => {
                // parse response safely
                if (!resp.ok) return parseResponseJson(resp).then(json => { throw new Error(json.message || JSON.stringify(json)); });
                return parseResponseJson(resp);
            }).then(data => {
                if (!data || !data.groups) { alert('Respuesta inválida del servidor.'); return; }
                console.log('Respuesta del servidor:', data);
                renderValidationResults(data.groups);
            }).catch(err => { console.error(err); alert('Error al validar disponibilidad: ' + (err.message || err)); })
            .finally(() => { btn.disabled = false; btn.innerHTML = originalText; });
        });
    }

})();
</script>

<!-- Modal para editar configuración del grupo -->
<div id="groupEditModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-lg w-full max-w-2xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Editar Grupo <span id="editModalGroupName" class="text-sm text-slate-600"></span></h3>
            <button type="button" id="editModalClose" class="text-slate-500">✕</button>
        </div>

        <form id="editGroupForm">
            <input type="hidden" name="group_id" id="edit_group_id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Conductor</label>
                    <select name="driver_id" id="edit_driver_id" class="w-full rounded border-slate-300 p-2"></select>
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Ayudante 1</label>
                    <select name="user1_id" id="edit_user1_id" class="w-full rounded border-slate-300 p-2"></select>
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Ayudante 2</label>
                    <select name="user2_id" id="edit_user2_id" class="w-full rounded border-slate-300 p-2"></select>
                </div>
                <div>
                    <label class="block text-sm text-slate-600 mb-1">Vehículo</label>
                    <select name="vehicle_id" id="edit_vehicle_id" class="w-full rounded border-slate-300 p-2">
                        <option value="">-- Ninguno --</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label class="block text-sm text-slate-600 mb-1">Días</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-sm" id="edit_days_container">
                    @php $days = ['lunes'=>'Lun','martes'=>'Mar','miercoles'=>'Mié','jueves'=>'Jue','viernes'=>'Vie','sabado'=>'Sáb','domingo'=>'Dom']; @endphp
                    @foreach($days as $key => $label)
                        <label class="inline-flex items-center gap-2"><input type="checkbox" name="days[]" value="{{ $key }}"> {{ $label }}</label>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" id="editCancel" class="px-3 py-2 rounded bg-slate-100 text-slate-700">Cancelar</button>
                <button type="submit" id="editSave" class="px-4 py-2 rounded bg-emerald-600 text-white">Guardar</button>
            </div>
        </form>
    </div>
</div>

    <!-- JSON blobs for users and vehicles to avoid inline JS injection -->
    <script type="application/json" id="massive-users-json">@json($users ?? [])</script>
    <script type="application/json" id="massive-vehicles-json">@json($vehicles ?? [])</script>

    <script>
    // Extras para manejo del modal y edición de grupo
    (function(){
        // Parse users/vehicles from JSON script tags
        const usersJsonEl = document.getElementById('massive-users-json');
        const vehiclesJsonEl = document.getElementById('massive-vehicles-json');
        const usersList = usersJsonEl ? JSON.parse(usersJsonEl.textContent || '[]') : [];
        const vehiclesList = vehiclesJsonEl ? JSON.parse(vehiclesJsonEl.textContent || '[]') : [];

        function parseResponseJson(resp){
            return resp.text().then(text => {
                try { return JSON.parse(text); }
                catch(e){ throw new Error('Respuesta inválida (no JSON): ' + (text ? text.substring(0,200) : '<vacío>')); }
            });
        }

    // --- Funcionalidad de eliminar grupos de la lista ---
    document.addEventListener('click', function(e){
        if (e.target && e.target.matches('.remove-group-btn')){
            e.preventDefault();
            const groupId = e.target.getAttribute('data-group-id');
            const card = document.querySelector(`[data-group-id="${groupId}"]`);
            
            if (card) {
                // Marcar como eliminado y ocultar
                card.classList.add('removed-from-list');
                card.style.display = 'none';
                
                // Deshabilitar botón de submit al eliminar un grupo
                const submitBtn = document.getElementById('submit_scheduling_btn');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.remove('bg-emerald-600', 'text-white', 'hover:bg-emerald-700', 'pointer-events-auto');
                    submitBtn.classList.add('bg-slate-300', 'text-slate-600', 'cursor-not-allowed', 'pointer-events-none');
                }
                
                // Re-aplicar filtro para actualizar contador de grupos visibles
                const filterSelect = document.getElementById('filter_schedule_select_top');
                if (filterSelect) {
                    const event = new Event('change');
                    filterSelect.dispatchEvent(event);
                }
            }
        }
    });

    function buildSelectOptions(selectEl, items, selectedId, labelField = 'firstname'){
        selectEl.innerHTML = '';
        const empty = document.createElement('option');
        empty.value = '';
        empty.textContent = '-- Ninguno --';
        selectEl.appendChild(empty);

        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            if (item.firstname && item.lastname) opt.textContent = item.firstname + ' ' + item.lastname;
            else opt.textContent = item.name || (item.firstname || '') + ' ' + (item.lastname || '');
            if (String(item.id) === String(selectedId)) opt.selected = true;
            selectEl.appendChild(opt);
        });
    }

    function openEditModalForGroup(groupId){
        const card = document.querySelector(`[data-group-id="${groupId}"]`);
        if (!card) return alert('No se encontró la tarjeta del grupo.');

        const driverId = card.getAttribute('data-driver-id') || '';
        const helper1Id = card.getAttribute('data-helper1-id') || '';
        const helper2Id = card.getAttribute('data-helper2-id') || '';
        const vehicleId = card.getAttribute('data-vehicle-id') || '';
        const days = (() => { try { return JSON.parse(card.getAttribute('data-days') || '[]'); } catch(e){ return []; } })();

        document.getElementById('edit_group_id').value = groupId;
        document.getElementById('editModalGroupName').textContent = ' - ' + (card.querySelector('h4') ? card.querySelector('h4').textContent.trim() : '');

        // Build selects
        buildSelectOptions(document.getElementById('edit_driver_id'), usersList, driverId);
        buildSelectOptions(document.getElementById('edit_user1_id'), usersList, helper1Id);
        buildSelectOptions(document.getElementById('edit_user2_id'), usersList, helper2Id);
        buildSelectOptions(document.getElementById('edit_vehicle_id'), vehiclesList, vehicleId, 'name');

        // Days checkboxes
        const daysContainer = document.getElementById('edit_days_container');
        daysContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = days.includes(cb.value));

        // Show modal
        const modal = document.getElementById('groupEditModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditModal(){
        const modal = document.getElementById('groupEditModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }


    // Attach events to dynamic edit buttons in cards
    document.addEventListener('click', function(e){
        if (e.target && e.target.matches('.edit-group-btn')){
            const id = e.target.getAttribute('data-group-id');
            openEditModalForGroup(id);
        }
    });

    const editModalClose = document.getElementById('editModalClose');
    if (editModalClose) editModalClose.addEventListener('click', closeEditModal);
    const editCancel = document.getElementById('editCancel');
    if (editCancel) editCancel.addEventListener('click', closeEditModal);

    const editForm = document.getElementById('editGroupForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e){
            e.preventDefault();
            const form = e.target;
            const payload = { group_id: form.group_id.value };
            payload.driver_id = form.driver_id.value || null;
            payload.user1_id = form.user1_id.value || null;
            payload.user2_id = form.user2_id.value || null;
            payload.vehicle_id = form.vehicle_id.value || null;
            payload.days = Array.from(form.querySelectorAll('input[name="days[]"]:checked')).map(i => i.value);

            const token = (function(){ const m = document.querySelector('meta[name="csrf-token"]'); return m ? m.getAttribute('content') : (document.querySelector('input[name="_token"]') ? document.querySelector('input[name="_token"]').value : ''); })();

            const saveBtn = document.getElementById('editSave');
            if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Guardando...'; }

            fetch("{{ route('schedulings.update-group') }}", {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify(payload)
            }).then(resp => {
                if (!resp.ok) return parseResponseJson(resp).then(json => { throw new Error(json.message || JSON.stringify(json)); });
                return parseResponseJson(resp);
            }).then(data => {
                if (!data || !data.success){ alert(data?.message || 'Error al guardar'); return; }

                const g = data.group;
                const card = document.querySelector(`[data-group-id="${payload.group_id}"]`);
                if (card) {
                    card.setAttribute('data-driver-id', payload.driver_id || '');
                    card.setAttribute('data-helper1-id', payload.user1_id || '');
                    card.setAttribute('data-helper2-id', payload.user2_id || '');
                    card.setAttribute('data-vehicle-id', payload.vehicle_id || '');
                    card.setAttribute('data-days', JSON.stringify(g.days || payload.days || []));

                    card.querySelectorAll('div').forEach(d => {
                        if (d.textContent && d.textContent.trim().startsWith('Conductor:')) {
                            d.innerHTML = '<strong>Conductor:</strong> ' + (g.driver ?? '-');
                        }
                        if (d.textContent && d.textContent.trim().startsWith('Ayudantes:')) {
                            const helpers = [g.helper1, g.helper2].filter(Boolean).join(', ');
                            d.innerHTML = '<strong>Ayudantes:</strong> ' + (helpers || '-');
                        }
                        if (d.textContent && d.textContent.trim().startsWith('Vehículo:')) {
                            d.innerHTML = '<strong>Vehículo:</strong> ' + (g.vehicle ?? '-');
                        }
                        if (d.textContent && d.textContent.trim().startsWith('Días:')) {
                            const map = { 'lunes':'Lun','martes':'Mar','miercoles':'Mié','jueves':'Jue','viernes':'Vie','sabado':'Sáb','domingo':'Dom' };
                            const daysArr = g.days && g.days.length ? g.days : (payload.days || []);
                            const names = (daysArr || []).map(x => map[x] || x);
                            d.innerHTML = '<strong>Días:</strong> ' + (names.length ? names.join(', ') : '-');
                        }
                    });
                }

                closeEditModal();
                revalidateGroup(payload.group_id);
            }).catch(err => { console.error(err); alert('Error al guardar: ' + (err.message || err)); })
            .finally(() => { if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Guardar'; } });
        });
    }

    function revalidateGroup(groupId){
        const start_date = document.querySelector('[name="start_date"]').value;
        const end_date = document.querySelector('[name="end_date"]').value;
        const scheduleInput = document.querySelector('[name="schedule_id"]');
        const schedule_id = scheduleInput ? scheduleInput.value : null;

        const token = (function(){ const m = document.querySelector('meta[name="csrf-token"]'); return m ? m.getAttribute('content') : (document.querySelector('input[name="_token"]') ? document.querySelector('input[name="_token"]').value : ''); })();

        const payload = { start_date, end_date, schedule_id, group_id: groupId };

        fetch("{{ route('schedulings.validate-massive') }}", {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify(payload)
        }).then(resp => {
            if (!resp.ok) return parseResponseJson(resp).then(json => { throw new Error(json.message || JSON.stringify(json)); });
            return parseResponseJson(resp);
        }).then(data => { if (data && data.groups) renderValidationResults(data.groups); })
        .catch(err => { console.error(err); });
    }
})();
</script>
