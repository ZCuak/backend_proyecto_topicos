{{-- Errores de validación --}}
@if($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
        <h4 class="font-semibold mb-2">Revisa los campos</h4>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li class="text-sm">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $employeesList = collect($employees ?? []);
    $driversList = $employeesList->where('usertype_id', 1);
    $helpersList = $employeesList->where('usertype_id', 2);
@endphp

<fieldset class="border border-slate-200 rounded-2xl p-6 bg-white space-y-6 hover:shadow-md transition">
    <legend class="px-3 text-sm font-semibold text-slate-700 flex items-center gap-2">
        <i class="fa-solid fa-calendar-plus text-emerald-600"></i> Programación de actividades
    </legend>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- FECHA INICIO -->
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">
                Fecha inicio <span class="text-red-500">*</span>
            </label>

            <input type="date" name="date"
                value="{{ old('date', isset($scheduling) ? $scheduling->date->format('Y-m-d') : date('Y-m-d')) }}"
                class="w-full py-2 px-3 rounded-lg border border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                min="{{ date('Y-m-d') }}">
        </div>

        <!-- FECHA FIN -->
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Fecha fin</label>

            <input type="date" name="end_date"
                value="{{ old('end_date', isset($scheduling) ? $scheduling->end_date?->format('Y-m-d') : date('Y-m-d')) }}"
                class="w-full py-2 px-3 rounded-lg border border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                min="{{ date('Y-m-d') }}">

            <p class="text-xs text-slate-500">Si defines fin, aplica el rango según los días.</p>
        </div>

    </div>


    {{-- 1. GRUPO (FILTRO PRINCIPAL) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Grupo de Empleados <span
                    class="text-red-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-users absolute left-3 top-2.5 text-slate-400"></i>
                <select name="group_id" data-endpoint="{{ url('groups') }}"
                    onchange="window.handleGroupChange && window.handleGroupChange(event)"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 @error('group_id') border-red-500 @enderror">
                    <option value="">Seleccionar grupo...</option>
                    @foreach($groups as $group)
                        @php
                            $groupDays = is_array($group->days) ? $group->days : json_decode($group->days, true);
                            $config = ($group->configgroups ?? collect())->sortBy('id')->values();
                            $driverId = $config[0]->user_id ?? null;
                            $helper1Id = $config[1]->user_id ?? null;
                            $helper2Id = $config[2]->user_id ?? null;
                        @endphp
                        <option value="{{ $group->id }}" data-schedule="{{ $group->schedule_id }}"
                            data-vehicle="{{ $group->vehicle_id }}" data-zone="{{ $group->zone_id }}"
                            data-driver="{{ $driverId }}" data-helper1="{{ $helper1Id }}" data-helper2="{{ $helper2Id }}"
                            data-days='@json($groupDays ?? [])' {{ old('group_id', $scheduling->group_id ?? '') == $group->id ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <p class="text-xs text-slate-500">El grupo define días, vehículo, zona y horario.</p>
            @error('group_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    {{-- Horario --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Horario <span class="text-red-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-clock absolute left-3 top-2.5 text-slate-400"></i>
                <select name="schedule_id" disabled
                    class="w-full pl-10 pr-3 py-2 rounded-lg border border-slate-300 bg-slate-100 cursor-not-allowed">
                    <option value="">Seleccionar horario...</option>
                    @foreach($schedules as $schedule)
                        <option value="{{ $schedule->id }}" {{ old('schedule_id', $scheduling->schedule_id ?? '') == $schedule->id ? 'selected' : '' }}>
                            {{ $schedule->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

    </div>

    {{-- 2. ASIGNACIONES DEPENDIENTES DEL GRUPO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    

        {{-- Vehículo --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Vehículo</label>
            <div class="relative">
                <i class="fa-solid fa-truck absolute left-3 top-2.5 text-slate-400"></i>
                <select name="vehicle_id" disabled
                    class="w-full pl-10 pr-3 py-2 rounded-lg border border-slate-300 bg-slate-100 cursor-not-allowed">
                    <option value="">Seleccionar vehículo...</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $scheduling->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->name }} ({{ $vehicle->plate }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Zona --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Zona</label>
            <div class="relative">
                <i class="fa-solid fa-map-marker-alt absolute left-3 top-2.5 text-slate-400"></i>
                <select name="zone_id" disabled
                    class="w-full pl-10 pr-3 py-2 rounded-lg border border-slate-300 bg-slate-100 cursor-not-allowed">
                    <option value="">Seleccionar zona...</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" {{ old('zone_id', $scheduling->zone_id ?? '') == $zone->id ? 'selected' : '' }}>
                            {{ $zone->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

    </div>


    {{-- 3. DÍAS DE LA SEMANA --}}
    <div class="mt-4">
        <label class="block text-sm font-medium text-slate-700 mb-1">Días de la semana</label>

        @php
            $days = [
                'lunes' => 'Lun',
                'martes' => 'Mar',
                'miercoles' => 'Mié',
                'jueves' => 'Jue',
                'viernes' => 'Vie',
                'sabado' => 'Sáb',
                'domingo' => 'Dom',
            ];

            $selectedDays = old('days', isset($scheduling) ? $scheduling->days_array : []);
        @endphp

        <div class="flex gap-3 flex-wrap">
            @foreach($days as $key => $label)
                <label
                    class="inline-flex items-center space-x-2 cursor-pointer px-3 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm">
                    <input type="checkbox" name="days[]" value="{{ $key }}"
                        class="h-5 w-5 rounded border border-slate-300 text-emerald-600 focus:ring-2 focus:ring-emerald-500"
                        {{ in_array($key, $selectedDays) ? 'checked' : '' }}>
                    <span class="text-slate-700 text-sm">{{ $label }}</span>
                </label>
            @endforeach
        </div>

        <p class="text-xs text-slate-500 mt-2">
            Si no eliges días, se aplicará a todos dentro del rango.
        </p>
    </div>

    {{-- 4. PERSONAL DEL GRUPO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Conductor</label>
            <div class="relative">
                <i class="fa-solid fa-id-card absolute left-3 top-2.5 text-slate-400"></i>
                <select name="driver_id"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Seleccionar conductor...</option>
                    @foreach($driversList as $employee)
                        <option value="{{ $employee->id }}" {{ old('driver_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->firstname }} {{ $employee->lastname }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Ayudante 1</label>
            <div class="relative">
                <i class="fa-solid fa-user-group absolute left-3 top-2.5 text-slate-400"></i>
                <select name="helper1_id"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Seleccionar ayudante...</option>
                    @foreach($helpersList as $employee)
                        <option value="{{ $employee->id }}" {{ old('helper1_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->firstname }} {{ $employee->lastname }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Ayudante 2</label>
            <div class="relative">
                <i class="fa-solid fa-user absolute left-3 top-2.5 text-slate-400"></i>
                <select name="helper2_id"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Seleccionar ayudante...</option>
                    @foreach($helpersList as $employee)
                        <option value="{{ $employee->id }}" {{ old('helper2_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->firstname }} {{ $employee->lastname }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>


    {{-- 5. NOTAS --}}
    <div class="mt-4">
        <label class="block text-sm font-medium text-slate-700 mb-1">Notas</label>
        <textarea name="notes" rows="3"
            class="w-full py-3 px-4 rounded-lg border border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
            placeholder="Notas adicionales...">{{ old('notes', $scheduling->notes ?? '') }}</textarea>
    </div>
</fieldset>

{{-- BOTONES --}}
@php
    $modalId = isset($scheduling) && isset($scheduling->id) ? 'editModal' : 'createModal';
@endphp

<div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
    <button type="button" onclick="FlyonUI.modal.close('{{ $modalId }}')"
        class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark mr-1"></i> Cancelar
    </button>

    <button type="submit"
        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
        <i class="fa-solid fa-save"></i> {{ $buttonText }}
    </button>
</div>

<script>
    (function initSchedulingForm() {
        'use strict';

        const currentScript = document.currentScript;
        const form = currentScript?.parentElement?.querySelector('form') || document.querySelector('form[action*="schedulings"]');
        if (!form || form.dataset.enhanced === 'true') return;
        form.dataset.enhanced = 'true';

        const groupSelect = form.querySelector('[name="group_id"]');
        const scheduleSelect = form.querySelector('[name="schedule_id"]');
        const vehicleSelect = form.querySelector('[name="vehicle_id"]');
        const zoneSelect = form.querySelector('[name="zone_id"]');
        const driverSelect = form.querySelector('[name="driver_id"]');
        const helper1Select = form.querySelector('[name="helper1_id"]');
        const helper2Select = form.querySelector('[name="helper2_id"]');
        const dayCheckboxes = Array.from(form.querySelectorAll('input[name="days[]"]'));
        const startDate = form.querySelector('input[name="date"]');
        const endDate = form.querySelector('input[name="end_date"]');

        const disableChosen = () => {
            const chosen = new Set([
                driverSelect?.value || '',
                helper1Select?.value || '',
                helper2Select?.value || '',
            ].filter(Boolean));

            [driverSelect, helper1Select, helper2Select].forEach(select => {
                if (!select) return;
                Array.from(select.options).forEach(opt => {
                    if (!opt.value) return;
                    opt.disabled = false;
                    if (chosen.has(opt.value) && opt.value !== select.value) {
                        opt.disabled = true;
                    }
                });
            });
        };

        const setSelectValue = (selectEl, value) => {
            if (!selectEl || value === null || value === undefined || value === '') return;
            const strVal = String(value);
            const option = selectEl.querySelector(`option[value="${strVal}"]`);
            if (option) {
                selectEl.value = strVal;
                selectEl.dispatchEvent(new Event('change'));
            }
        };

        const clearAndSetDays = (daysArray) => {
            if (!dayCheckboxes.length) return;
            dayCheckboxes.forEach(cb => cb.checked = false);
            daysArray.forEach(day => {
                const target = dayCheckboxes.find(cb => cb.value === day);
                if (target) target.checked = true;
            });
        };

        const renderReadOnlyDays = (daysArray) => {
            const badges = document.querySelectorAll('#selectedDaysDisplay [data-day]');
            if (!badges.length) return;
            badges.forEach(badge => {
                const icon = badge.querySelector('i');
                if (icon) icon.remove();
                const day = badge.dataset.day;
                if (Array.isArray(daysArray) && daysArray.includes(day)) {
                    const i = document.createElement('i');
                    i.className = 'fa-solid fa-check text-emerald-600 text-[10px]';
                    badge.appendChild(i);
                }
            });
        };

        const fetchGroupData = async (groupId) => {
            if (!groupId) return null;
            try {
                const base = groupSelect?.dataset?.endpoint || '/groups';
                const url = `${base.replace(/\/$/, '')}/${groupId}`;
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) return null;
                const json = await response.json();
                return json?.data ?? null;
            } catch (err) {
                console.error('No se pudo obtener datos del grupo', err);
                return null;
            }
        };

        window.handleGroupChange = async (e) => {
            const option = e.target.selectedOptions[0];
            const groupId = option?.value;
            if (!groupId) return;

            const scheduleId = option.getAttribute('data-schedule');
            const vehicleId = option.getAttribute('data-vehicle');
            const zoneId = option.getAttribute('data-zone');
            const driverId = option.getAttribute('data-driver');
            const helper1Id = option.getAttribute('data-helper1');
            const helper2Id = option.getAttribute('data-helper2');
            let days = [];
            try {
                days = JSON.parse(option.getAttribute('data-days') || '[]');
            } catch (err) {
                days = [];
            }

            setSelectValue(scheduleSelect, scheduleId);
            setSelectValue(vehicleSelect, vehicleId);
            setSelectValue(zoneSelect, zoneId);
            setSelectValue(driverSelect, driverId);
            setSelectValue(helper1Select, helper1Id);
            setSelectValue(helper2Select, helper2Id);
            disableChosen();
            if (days.length) {
                clearAndSetDays(days);
                renderReadOnlyDays(days);
            }

            const data = await fetchGroupData(groupId);
            if (data) {
                setSelectValue(scheduleSelect, data.schedule_id);
                setSelectValue(vehicleSelect, data.vehicle_id);
                setSelectValue(zoneSelect, data.zone_id);
                setSelectValue(driverSelect, data.driver_id);
                setSelectValue(helper1Select, data.helper1_id);
                setSelectValue(helper2Select, data.helper2_id);
                disableChosen();
                if (Array.isArray(data.days) && data.days.length) {
                    clearAndSetDays(data.days);
                    renderReadOnlyDays(data.days);
                }
            }
        };

        if (groupSelect) {
            groupSelect.addEventListener('change', window.handleGroupChange);
            if (groupSelect.value) {
                groupSelect.dispatchEvent(new Event('change'));
            }
        }

        [driverSelect, helper1Select, helper2Select].forEach(select => {
            if (!select) return;
            select.addEventListener('change', disableChosen);
        });
        disableChosen();

        form.addEventListener('submit', function (e) {
            const requiredFields = [
                { name: 'date', label: 'Fecha inicio' },
                { name: 'group_id', label: 'Grupo de empleados' },
                { name: 'schedule_id', label: 'Horario' }
            ];

            let hasErrors = false;
            let errorMessages = [];

            document.querySelectorAll('.field-error').forEach(el => el.remove());
            document.querySelectorAll('.border-red-500').forEach(el => {
                el.classList.remove('border-red-500');
                el.classList.add('border-slate-300');
            });

            requiredFields.forEach(field => {
                const element = document.querySelector(`[name="${field.name}"]`);
                if (element) {
                    const value = element.value.trim();

                    if (!value || value === '') {
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

            if (startDate && endDate && startDate.value && endDate.value && endDate.value < startDate.value) {
                hasErrors = true;
                errorMessages.push('La fecha fin debe ser mayor o igual a la fecha inicio');
                endDate.classList.add('border-red-500');
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

                const formEl = document.querySelector('form');
                formEl.insertBefore(generalError, formEl.firstChild);
                generalError.scrollIntoView({ behavior: 'smooth' });

                return false;
            }
        });

        if (startDate) {
            startDate.addEventListener('change', function (e) {
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
        }

        const requiredFields = ['date', 'group_id', 'schedule_id'];

        requiredFields.forEach(fieldName => {
            const element = document.querySelector(`[name="${fieldName}"]`);
            if (element) {
                element.addEventListener('input', function (e) {
                    const value = e.target.value.trim();
                    const existingError = e.target.parentNode.querySelector('.field-error');
                    if (existingError) {
                        existingError.remove();
                    }

                    if (value && value !== '') {
                        e.target.classList.remove('border-red-500');
                        e.target.classList.add('border-slate-300');
                        e.target.setCustomValidity('');
                    } else {
                        e.target.classList.add('border-red-500');
                        e.target.classList.remove('border-slate-300');
                        e.target.setCustomValidity('Este campo es obligatorio');
                    }
                });

                element.addEventListener('change', function (e) {
                    const value = e.target.value.trim();
                    const existingError = e.target.parentNode.querySelector('.field-error');
                    if (existingError) {
                        existingError.remove();
                    }

                    if (value && value !== '') {
                        e.target.classList.remove('border-red-500');
                        e.target.classList.add('border-slate-300');
                        e.target.setCustomValidity('');
                    } else {
                        e.target.classList.add('border-red-500');
                        e.target.classList.remove('border-slate-300');
                        e.target.setCustomValidity('Este campo es obligatorio');
                    }
                });
            }
        });
    })();
</script>
