<div class="space-y-6">
    @php $modalId = isset($vacation->id) ? 'editModal' : 'createModal'; @endphp

    {{-- USUARIO --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Usuario <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <i class="fa-solid fa-user absolute left-3 top-2.5 text-slate-400"></i>
            <select id="vacation_user_select" name="user_id" required
                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">-- Seleccionar usuario --</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}" data-max-days="{{ $user->max_days ?? '' }}" {{ (old('user_id', $vacation->user_id ?? '') == $user->id) ? 'selected' : '' }}>
                    {{ $user->firstname }} {{ $user->lastname }}{{ isset($user->dni) ? ' - ' . $user->dni : '' }}
                </option>
                @endforeach
            </select>
            @error('user_id')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- AÑO --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Año <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <i class="fa-solid fa-calendar-alt absolute left-3 top-2.5 text-slate-400"></i>
            <input type="number"
                name="year"
                value="{{ old('year', $vacation->year ?? now()->year) }}"
                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                placeholder="Ej. 2025..."
                required>
        </div>
        @error('year')
        <p class="text-red-500 text-xs mt-1">
            <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
        </p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- FECHA INICIO --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha inicio <span class="text-red-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-clock absolute left-3 top-2.5 text-slate-400"></i>
                <input type="date"
                    name="start_date"
                    value="{{ old('start_date', isset($vacation->start_date) ? \Carbon\Carbon::parse($vacation->start_date)->format('Y-m-d') : '') }}"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    required>
            </div>
            @error('start_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- DÍAS SOLICITADOS --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Días solicitados <span class="text-red-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-calendar-check absolute left-3 top-2.5 text-slate-400"></i>
                <input type="number"
                    id="vacation_days_requested"
                    name="days_requested"
                    min="1"
                    value="{{ old('days_requested', isset($vacation->days_programmed) ? $vacation->days_programmed : '') }}"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    required>
            </div>
            @error('days_requested')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- DÍAS MÁXIMOS --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Días máximo disponibles <span class="text-red-500">*</span></label>
        <div class="relative">
            <i class="fa-solid fa-calendar-days absolute left-3 top-2.5 text-slate-400"></i>
            <input type="number"
                id="vacation_max_days"
                name="max_days"
                value="{{ old('max_days', $vacation->max_days ?? '') }}"
                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                readonly>
        </div>
        @error('max_days')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- FECHA FIN (calculada) --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha fin</label>
        <div class="relative">
            <i class="fa-solid fa-clock-rotate-left absolute left-3 top-2.5 text-slate-400"></i>
            <input type="date"
                id="vacation_end_date"
                name="end_date"
                value="{{ old('end_date', isset($vacation->end_date) ? \Carbon\Carbon::parse($vacation->end_date)->format('Y-m-d') : '') }}"
                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                readonly>
        </div>
        @error('end_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- MOTIVO --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Motivo
        </label>
        <div class="relative">
            <i class="fa-solid fa-ellipsis-vertical absolute left-3 top-2.5 text-slate-400"></i>
            <textarea name="reason" rows="3"
                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                placeholder="Opcional">{{ old('reason', $vacation->reason ?? '') }}</textarea>
        </div>
        @error('reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- ESTADO --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
        <div class="relative">
            <i class="fa-solid fa-toggle-on absolute left-3 top-2.5 text-slate-400"></i>
            <select name="status" class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="pendiente" {{ (old('status', $vacation->status ?? 'pendiente') == 'pendiente') ? 'selected' : '' }}>Pendiente</option>
                <option value="aprobada" {{ (old('status', $vacation->status ?? '') == 'aprobada') ? 'selected' : '' }}>Aprobada</option>
                <option value="rechazada" {{ (old('status', $vacation->status ?? '') == 'rechazada') ? 'selected' : '' }}>Rechazada</option>
                <option value="cancelada" {{ (old('status', $vacation->status ?? '') == 'cancelada') ? 'selected' : '' }}>Cancelada</option>
                <option value="completada" {{ (old('status', $vacation->status ?? '') == 'completada') ? 'selected' : '' }}>Completada</option>
            </select>
        </div>
        @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- BOTONES --}}
    <div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
        <button type="button"
            onclick="FlyonUI.modal.close('{{ $modalId }}')"
            class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
            <i class="fa-solid fa-xmark mr-1"></i> Cancelar
        </button>

        <button type="submit"
            class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
            <i class="fa-solid fa-save"></i> {{ $buttonText ?? (isset($vacation->id) ? 'Actualizar' : 'Registrar') }}
        </button>
    </div>
</div>
<script>
    (function() {
        function applyMaxDays(container) {
            var select = container.querySelector('select[name="user_id"]');
            var maxInput = container.querySelector('input[name="max_days"]');
            var startInput = container.querySelector('input[name="start_date"]');
            var daysRequested = container.querySelector('input[name="days_requested"]');
            var endInput = container.querySelector('input[name="end_date"]');
            if (!select || !maxInput) return;

            function setFromSelected() {
                var opt = select.options[select.selectedIndex];
                var md = null;
                if (opt) {
                    md = (opt.dataset && opt.dataset.maxDays) ? opt.dataset.maxDays : opt.getAttribute('data-max-days');
                }
                var parsed = null;
                if (md !== null && md !== undefined && md !== '') {
                    parsed = parseInt(md, 10);
                }

                if (!isNaN(parsed) && parsed !== null) {
                    maxInput.value = parsed;
                    // set max attribute on daysRequested if present
                    if (daysRequested) daysRequested.max = parsed;
                    // clamp current value if necessary
                    if (daysRequested) {
                        var cur = parseInt(daysRequested.value, 10);
                        if (!isNaN(cur) && cur > parsed) daysRequested.value = parsed;
                    }
                    // clear placeholder
                    maxInput.placeholder = '';
                } else {
                    // no data from contract: clear value and set placeholder
                    maxInput.value = '';
                    maxInput.placeholder = 'N/D';
                    if (daysRequested) daysRequested.removeAttribute('max');
                }

                // try recalc end date
                recalcEnd();
            }

            function formatDate(d) {
                var yyyy = d.getFullYear();
                var mm = String(d.getMonth() + 1).padStart(2, '0');
                var dd = String(d.getDate()).padStart(2, '0');
                return yyyy + '-' + mm + '-' + dd;
            }

            // Parse a YYYY-MM-DD string into a local Date object (avoids timezone shifts from Date("YYYY-MM-DD")).
            function parseYMD(ymd) {
                if (!ymd) return null;
                var parts = ymd.split('-');
                if (parts.length !== 3) return null;
                var y = parseInt(parts[0], 10);
                var m = parseInt(parts[1], 10) - 1;
                var d = parseInt(parts[2], 10);
                if (isNaN(y) || isNaN(m) || isNaN(d)) return null;
                return new Date(y, m, d);
            }

            function recalcEnd() {
                if (!startInput || !daysRequested || !endInput) return;
                var sd = startInput.value;
                var dr = parseInt(daysRequested.value, 10);
                if (!sd || isNaN(dr) || dr < 1) {
                    // clear end
                    endInput.value = '';
                    return;
                }
                try {
                    var d = parseYMD(sd);
                    if (!d) {
                        endInput.value = '';
                        return;
                    }
                    // add (dr - 1) days
                    d.setDate(d.getDate() + (dr - 1));
                    endInput.value = formatDate(d);
                } catch (e) {
                    endInput.value = '';
                }
            }

            select.addEventListener('change', setFromSelected);
            if (startInput) startInput.addEventListener('change', recalcEnd);
            if (daysRequested) daysRequested.addEventListener('input', function() {
                // ensure within max
                var mx = parseInt(daysRequested.max, 10);
                var val = parseInt(daysRequested.value, 10);
                if (mx && !isNaN(val) && val > mx) daysRequested.value = mx;
                recalcEnd();
            });

            // initialize
            setFromSelected();
        }

        // Try to apply to the modal container if present
        var modal = document.getElementById('{{ $modalId }}');
        if (modal) {
            applyMaxDays(modal);
        } else {
            // fallback to document (works for inline rendering)
            applyMaxDays(document);
        }

        // Re-apply when turbo frame loads modal content
        document.addEventListener('turbo:frame-load', function(e) {
            if (e.target && e.target.id === 'modal-frame') applyMaxDays(e.target);
        });
    })();
</script>