<turbo-frame id="modal-frame">
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999]">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-7xl overflow-hidden mx-4 sm:mx-6 my-10">

        {{-- HEADER --}}
        <div class="flex justify-between items-center px-6 py-4 border-b bg-slate-50">
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-calendar-pen text-amber-600"></i> Edicion Masiva de Programaciones
            </h3>
            <button type="button" onclick="closeMassiveModal()">
                <i class="fa-solid fa-xmark text-xl text-slate-500 hover:text-slate-700"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="p-6 space-y-6 overflow-y-auto max-h-[85vh]">

            @php $changeType = request('change_type', 'driver'); @endphp
            {{-- FORM PRINCIPAL --}}
            <form id="massiveFilter" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha de Inicio *</label>
                        <input id="f_start" type="date" name="start_date"
                               class="border rounded-lg p-2 w-full"
                               value="{{ request('start_date', now()->toDateString()) }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha de Fin *</label>
                        <input id="f_end" type="date" name="end_date"
                               class="border rounded-lg p-2 w-full"
                               value="{{ request('end_date', now()->addWeek()->toDateString()) }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Zonas (Opcional)</label>
                        <select id="f_zone" name="zone_id" class="border rounded-lg p-2 w-full">
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Dejar vacio para aplicar a todas las zonas</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo de Cambio *</label>
                        <select id="f_change_type" name="change_type" class="border rounded-lg p-2 w-full">
                            <option value="driver" {{ $changeType === 'driver' ? 'selected' : '' }}>Cambio de Conductor</option>
                            <option value="occupant" {{ $changeType === 'occupant' ? 'selected' : '' }}>Cambio de Ayudante</option>
                            <option value="turn" {{ $changeType === 'turn' ? 'selected' : '' }}>Cambio de Turno</option>
                            <option value="vehicle" {{ $changeType === 'vehicle' ? 'selected' : '' }}>Cambio de Vehiculo</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label id="label_from" class="block text-sm font-semibold text-slate-700 mb-1">Elemento a reemplazar *</label>
                        <select id="f_from" name="from_id" data-selected="{{ request('from_id') }}"
                                class="border rounded-lg p-2 w-full"></select>
                    </div>

                    <div>
                        <label id="label_to" class="block text-sm font-semibold text-slate-700 mb-1">Nuevo valor *</label>
                        <select id="f_to" name="to_id" data-selected="{{ request('to_id') }}"
                                class="border rounded-lg p-2 w-full"></select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Motivo del cambio masivo *</label>
                    <textarea id="f_reason" name="reason" rows="3"
                              class="w-full border rounded-lg p-3"
                              placeholder="Describe el motivo del cambio">{{ request('reason') }}</textarea>
                </div>
            </form>

            {{-- SIN RESULTADOS --}}
            @if(!$programaciones->count())
                <div class="text-center py-10 text-slate-500" id="serverEmpty">
                    <i class="fa-solid fa-circle-info text-3xl mb-2"></i><br>
                    Usa los filtros para listar programaciones.
                </div>
            @endif

            {{-- BOTON GLOBAL --}}
            @if($programaciones->count())
            <div class="flex justify-end">
                <button id="btnSaveMassive"
                        class="bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700 flex items-center gap-2">
                    <i class="fa-solid fa-check-double"></i> Aplicar Cambios a Todas
                </button>
            </div>
            @endif

            {{-- LISTADO DE PROGRAMACIONES --}}
            <div id="massiveResults" class="space-y-4"></div>
        </div>
    </div>
</div>

{{-- SCRIPT GLOBAL --}}
<script>
const FETCH_URL = "{{ route('schedulings.fetch-massive') }}";
const MASSIVE_UPDATE_URL = "{{ route('schedulings.update-massive') }}";

const TURNOS = @json($schedules);
const VEHICULOS = @json($vehicles);
const EMPLEADOS = @json($users);

// Neutralizar modal individual
window.setupSchedulingModal = function(){};
window.closeMassiveModal = function() {
    const frame = document.getElementById('modal-frame');
    if (frame) frame.innerHTML = "";
};

document.addEventListener("turbo:frame-load", () => {
    initMassiveEditor();
});

function initMassiveEditor() {
    const filterForm = document.querySelector("#massiveFilter");
    const massiveResults = document.querySelector("#massiveResults");
    const changeTypeSelect = document.querySelector("#f_change_type");
    const fromSelect = document.querySelector("#f_from");
    const toSelect = document.querySelector("#f_to");
    const labelFrom = document.querySelector("#label_from");
    const labelTo = document.querySelector("#label_to");
    const saveBtn = document.querySelector("#btnSaveMassive");
    const pageLoader = document.getElementById("page-loader");

    let lastFetchData = null;

    hydrateChangeSelectors();

    changeTypeSelect?.addEventListener("change", () => {
        hydrateChangeSelectors();
        triggerFetch();
    });

    ["#f_start", "#f_end", "#f_zone"].forEach(id => {
        document.querySelector(id)?.addEventListener("change", triggerFetch);
    });

    fromSelect?.addEventListener("change", () => {
        if (!changeTypeSelect) return;
        const type = changeTypeSelect.value;
        if (type === "driver" || type === "occupant") {
            const isDriver = type === "driver";
            const selectedFrom = fromSelect.value;
            toSelect.innerHTML = buildUserOptions(toSelect.value, isDriver, [], selectedFrom);
        }
    });

    if (filterForm) {
        filterForm.onsubmit = e => {
            e.preventDefault();
        };
    }

    saveBtn?.addEventListener("click", () => applyMassive());

    bindMassiveEvents();
    triggerFetch();

    function hydrateChangeSelectors() {
        const type = changeTypeSelect?.value || "driver";
        let selectedFrom = fromSelect?.dataset.selected || "";
        let selectedTo = toSelect?.dataset.selected || "";
        const config = getChangeConfig(type, selectedFrom, selectedTo, lastFetchData);

        if (labelFrom) labelFrom.textContent = config.fromLabel;
        if (labelTo) labelTo.textContent = config.toLabel;
        if (fromSelect) {
            fromSelect.innerHTML = config.fromOptions;
            // Autoseleccionar si hay uno solo
            const opts = fromSelect.querySelectorAll("option[value]");
            if (!selectedFrom && opts.length === 2) { // incluye opción vacía
                selectedFrom = opts[1].value;
            }
            if (selectedFrom) fromSelect.value = selectedFrom;
        }
        if (toSelect) {
            if (type === "driver" || type === "occupant") {
                const isDriver = type === "driver";
                const currentFrom = fromSelect.value;
                toSelect.innerHTML = buildUserOptions(selectedTo, isDriver, [], currentFrom);
            } else {
                toSelect.innerHTML = config.toOptions;
            }
            const opts = toSelect.querySelectorAll("option[value]");
            if (!selectedTo && opts.length === 2) {
                selectedTo = opts[1].value;
            }
            if (selectedTo && selectedTo !== fromSelect.value) {
                toSelect.value = selectedTo;
            } else {
                toSelect.value = "";
            }
        }
    }

    function getChangeConfig(type, selectedFrom, selectedTo, data) {
        if (type === "turn") {
            return {
                fromLabel: "Turno actual (opcional)",
                toLabel: "Nuevo turno",
                fromOptions: buildTurnOptions(selectedFrom, true),
                toOptions: buildTurnOptions(selectedTo)
            };
        }

        if (type === "vehicle") {
            return {
                fromLabel: "Vehiculo actual (opcional)",
                toLabel: "Nuevo vehiculo",
                fromOptions: buildVehicleOptions(selectedFrom, true),
                toOptions: buildVehicleOptions(selectedTo)
            };
        }

        const isDriver = type === "driver";
        const availableFrom = collectAvailableFrom(type, data);

        return {
            fromLabel: isDriver ? "Conductor a reemplazar" : "Ayudante a reemplazar",
            toLabel: isDriver ? "Nuevo conductor" : "Nuevo ayudante",
            fromOptions: buildUserOptions(selectedFrom, isDriver, availableFrom),
            toOptions: buildUserOptions(selectedTo, isDriver, [], selectedFrom)
        };
    }

    function buildTurnOptions(selected = "", allowEmpty = false) {
        let options = allowEmpty ? `<option value=\"\">Todos</option>` : "";
        options += TURNOS.map(t => `
            <option value="${t.id}" ${t.id == selected ? 'selected' : ''}>
                ${t.name}
            </option>
        `).join("");
        return options;
    }

    function buildVehicleOptions(selected = "", allowEmpty = false) {
        let options = allowEmpty ? `<option value=\"\">Todos</option>` : `<option value=\"\">Seleccione...</option>`;
        options += VEHICULOS.map(v => `
            <option value="${v.id}" ${v.id == selected ? 'selected' : ''}>
                ${v.plate} - ${v.name}
            </option>
        `).join("");
        return options;
    }

    function buildUserOptions(selected = "", isDriver = true, availableOnly = [], excludeId = null) {
        let base = EMPLEADOS.filter(u => {
            const driverRole = (u.usertype_id ?? u.usertype) == 1;
            return isDriver ? driverRole : !driverRole;
        });

        if (excludeId) {
            base = base.filter(u => String(u.id) !== String(excludeId));
        }

        const list = availableOnly.length ? base.filter(u => availableOnly.includes(String(u.id))) : base;

        let options = `<option value=\"\">Seleccione...</option>`;
        options += list.map(u => `
            <option value="${u.id}" ${u.id == selected ? 'selected' : ''}>
                ${u.firstname} ${u.lastname} ${u.document_number ? '- ' + u.document_number : ''}
            </option>
        `).join("");

        return options;
    }

    function collectAvailableFrom(type, data) {
        if (!data || !data.groups) return [];

        const ids = new Set();
        const wantDriver = type === "driver";

        data.groups.forEach(group => {
            (group.schedulings || []).forEach(sch => {
                (sch.assigned || []).forEach(a => {
                    const isDriver = (a.usertype ?? a.usertype_id) == 1;
                    if (wantDriver ? isDriver : !isDriver) {
                        ids.add(String(a.user_id));
                    }
                });
            });
        });

        return Array.from(ids);
    }

    function triggerFetch() {
        const start = document.querySelector("#f_start")?.value;
        const end = document.querySelector("#f_end")?.value;
        if (!start || !end) return;
        fetchMassive();
    }

    function fetchMassive() {
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const emptyNotice = document.querySelector("#serverEmpty");
        if (emptyNotice) emptyNotice.remove();

        const payload = {
            start_date: document.querySelector("#f_start")?.value,
            end_date: document.querySelector("#f_end")?.value,
            zone_id: document.querySelector("#f_zone")?.value,
        };

        massiveResults.innerHTML = loader();

        fetch(FETCH_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": token },
            body: JSON.stringify(payload)
        })
            .then(r => r.json())
            .then(data => {
                lastFetchData = data;
                hydrateChangeSelectors();
                renderMassive(data);
            })
            .catch(() => massiveResults.innerHTML = errorView());
    }

    // RENDERIZAR LAS PROGRAMACIONES MASIVAS
    function renderMassive(data) {

        if (!data.success || !data.groups.length) {
            massiveResults.innerHTML = emptyView();
            return;
        }

        let html = `
        <button onclick="applyMassive()" 
                class="bg-emerald-600 text-white px-4 py-2 mb-4 rounded-lg hover:bg-emerald-700 flex items-center gap-2">
            <i class="fa-solid fa-check-double"></i> Aplicar cambios
        </button>
        <div class="space-y-3">
        `;

        data.groups.forEach(group => {
            group.schedulings.forEach(item => {
                const assigned = item.assigned || [];
                const driver = (assigned.find(a => (a.usertype ?? a.usertype_id) == 1) || {}).name || 'Sin conductor';
                const helpers = assigned.filter(a => (a.usertype ?? a.usertype_id) != 1)
                    .map(a => a.name).filter(Boolean).join(', ') || 'Sin ocupantes';

                html += `
                <div class="massive-card border rounded-xl p-4 bg-white shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-slate-800">${item.date}</p>
                            <p class="text-xs text-slate-500">${group.group_name}</p>
                            <p class="text-xs text-slate-500">${group.zone || ''}</p>
                        </div>
                        <span class="text-xs text-slate-500">${group.vehicle || 'Sin vehiculo'}</span>
                    </div>

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-700">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-clock text-emerald-600"></i>
                            <span>${item.schedule_name || 'Sin turno'}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-truck text-slate-600"></i>
                            <span>${item.vehicle_label || 'Sin vehiculo'}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-id-card text-slate-600"></i>
                            <span>${driver}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-people-group text-slate-600"></i>
                            <span>${helpers}</span>
                        </div>
                    </div>
                </div>
                `;
            });
        });

        html += "</div>";
        massiveResults.innerHTML = html;
    }

    function bindMassiveEvents() {
        // sin acciones por tarjeta
    }

    // APLICAR CAMBIOS MASIVOS
    window.applyMassive = async function () {

        const token = document.querySelector('meta[name="csrf-token"]').content;
        const changeType = document.querySelector("#f_change_type")?.value || "driver";
        const fromValue = document.querySelector("#f_from")?.value || "";
        const toValue = document.querySelector("#f_to")?.value || "";
        const reason = (document.querySelector("#f_reason")?.value || "").trim();

        if (!reason) {
            Swal.fire("Atencion", "Indica el motivo del cambio masivo.", "warning");
            return;
        }

        if (["driver", "occupant"].includes(changeType) && !fromValue) {
            Swal.fire("Atencion", "Selecciona a quien reemplazar.", "warning");
            return;
        }

        if (!toValue) {
            Swal.fire("Atencion", "Selecciona el nuevo valor a aplicar.", "warning");
            return;
        }

        if (!lastFetchData || !lastFetchData.groups?.length) {
            Swal.fire("Atencion", "No hay programaciones para aplicar cambios.", "warning");
            return;
        }

        const meta = { changeType, fromValue, toValue, reason };
        let updates = [];

        lastFetchData.groups.forEach(group => {
            (group.schedulings || []).forEach(item => {
                const upd = applyChangeToData(item, meta);
                if (upd) updates.push(upd);
            });
        });

        if (!updates.length) {
            Swal.fire("Atencion", "No se encontraron programaciones que cumplan las condiciones seleccionadas.", "warning");
            return;
        }

        const showLoader = () => pageLoader?.classList.remove("hidden");
        const hideLoader = () => pageLoader?.classList.add("hidden");
        showLoader();
        saveBtn?.setAttribute("disabled", "disabled");

        try {
            const response = await fetch(MASSIVE_UPDATE_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": token },
                body: JSON.stringify({ updates })
            });

            const data = await response.json();

            if (!response.ok || data.success === false) {
                hideLoader();
                saveBtn?.removeAttribute("disabled");
                Swal.fire("Error", data.message || "No se pudieron aplicar los cambios masivos.", "error");
                return;
            }

            closeMassiveModal();
            Turbo.visit(window.location.href, { action: "replace" });
        } catch (err) {
            hideLoader();
            saveBtn?.removeAttribute("disabled");
            Swal.fire("Error", "Ocurrio un problema al aplicar los cambios masivos.", "error");
        }
    };


    function applyChangeToData(item, meta) {
        const assigned = Array.isArray(item.assigned) ? JSON.parse(JSON.stringify(item.assigned)) : [];
        let changes = [];

        if (meta.changeType === "turn") {
            if (meta.fromValue && String(item.schedule_id) !== String(meta.fromValue)) return null;

            const previous = scheduleNameById(item.schedule_id) || "Turno actual";
            item.schedule_id = meta.toValue;
            changes.push({
                tipo: "Cambio de Turno",
                anterior: previous,
                nuevo: scheduleNameById(meta.toValue) || "",
                notas: meta.reason
            });
        }

        if (meta.changeType === "vehicle") {
            if (meta.fromValue && String(item.vehicle_id) !== String(meta.fromValue)) return null;

            const previous = vehicleNameById(item.vehicle_id) || "Vehiculo actual";
            item.vehicle_id = meta.toValue;
            changes.push({
                tipo: "Cambio de Vehiculo",
                anterior: previous,
                nuevo: vehicleNameById(meta.toValue) || "",
                notas: meta.reason
            });
        }

        if (meta.changeType === "driver" || meta.changeType === "occupant") {
            const isDriver = meta.changeType === "driver";

            const match = assigned.find(row => {
                const driverRole = (row.usertype ?? row.usertype_id) == 1;
                return String(row.user_id) === String(meta.fromValue) && (isDriver ? driverRole : !driverRole);
            });

            if (!match) return null;

            const previous = userNameById(match.user_id) || "Actual";
            match.user_id = meta.toValue;

            changes.push({
                tipo: isDriver ? "Cambio de Conductor" : "Cambio de Ocupante",
                anterior: previous,
                nuevo: userNameById(meta.toValue) || "",
                notas: meta.reason
            });
        }

        if (!changes.length) return null;

        return {
            id: item.id,
            schedule_id: item.schedule_id,
            vehicle_id: item.vehicle_id,
            notes: item.notes || null,
            changes,
            assigned_json: assigned
        };
    }

    // VISTAS AUXILIARES
    function loader() {
        return `<div class="text-center py-4 text-slate-500">
            <i class='fa-solid fa-spinner fa-spin'></i> Cargando...
        </div>`;
    }

    function emptyView() {
        return `<div class="p-4 bg-amber-50 text-amber-700 border rounded-lg">
            No se encontraron programaciones en este rango.
        </div>`;
    }

    function errorView() {
        return `<div class="p-4 bg-red-50 text-red-700 border rounded-lg">
            Error al cargar informacion.
        </div>`;
    }

    function userNameById(id) {
        const user = EMPLEADOS.find(u => String(u.id) === String(id));
        return user ? `${user.firstname} ${user.lastname}` : "";
    }

    function scheduleNameById(id) {
        const item = TURNOS.find(t => String(t.id) === String(id));
        return item ? item.name : "";
    }

    function vehicleNameById(id) {
        const item = VEHICULOS.find(v => String(v.id) === String(id));
        return item ? `${item.plate} - ${item.name}` : "";
    }

}

</script>

</turbo-frame>
