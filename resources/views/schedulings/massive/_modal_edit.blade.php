<turbo-frame id="modal-frame">
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999]">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-7xl overflow-hidden mx-4 sm:mx-6 my-10">

        {{-- HEADER --}}
        <div class="flex justify-between items-center px-6 py-4 border-b bg-slate-50">
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-calendar-pen text-amber-600"></i> Edición Masiva de Programaciones
            </h3>
            <button onclick="Turbo.visit(window.location.href)">
                <i class="fa-solid fa-xmark text-xl text-slate-500 hover:text-slate-700"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="p-6 space-y-6 overflow-y-auto max-h-[85vh]">

            {{-- FILTRO SUPERIOR --}}
            <form id="massiveFilter" class="grid grid-cols-1 md:grid-cols-4 gap-4"
                  action="{{ route('schedulings.edit-massive') }}" method="GET">

               <select id="f_schedule" name="schedule_id" class="border rounded-lg p-2">

                    <option value="">Turno</option>
                    @foreach($schedules as $s)
                        <option value="{{ $s->id }}" {{ request('schedule_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>

                <input id="f_start" type="date" name="start_date"
                       class="border rounded-lg p-2"
                       value="{{ request('start_date', now()->toDateString()) }}">

                <input id="f_end" type="date" name="end_date"
                       class="border rounded-lg p-2"
                       value="{{ request('end_date', now()->addWeek()->toDateString()) }}">

                <button class="bg-amber-600 text-white rounded-lg p-2 hover:bg-amber-700 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-search"></i> Buscar
                </button>
            </form>

            {{-- SIN RESULTADOS --}}
            @if(!$programaciones->count())
                <div class="text-center py-10 text-slate-500">
                    <i class="fa-solid fa-circle-info text-3xl mb-2"></i><br>
                    No se encontraron programaciones en el rango indicado.
                </div>
            @endif

            {{-- BOTÓN GLOBAL --}}
            @if($programaciones->count())
            <div class="flex justify-end">
                <button id="btnSaveMassive"
                        class="bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700 flex items-center gap-2">
                    <i class="fa-solid fa-check-double"></i> Aplicar Cambios a Todas
                </button>
            </div>
            @endif

            {{-- LISTADO DE PROGRAMACIONES --}}
            <div id="massiveResults" class="space-y-6"></div>


                @foreach($programaciones as $program)
                @php
                    $assigned = \App\Models\SchedulingDetail::with(['user'])
                        ->where('scheduling_id', $program->id)
                        ->orderBy('position_order')
                        ->get();

                    $allEmployees = $users;
                @endphp

                <div class="border rounded-xl shadow p-5 bg-white relative" data-id="{{ $program->id }}">

                    {{-- TÍTULO --}}
                    <div class="flex justify-between mb-4">
                        <h3 class="font-semibold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-calendar"></i>
                            Programación #{{ $program->id }} — {{ $program->date }}
                        </h3>
                        <span class="text-slate-500">{{ $program->group->name }}</span>
                    </div>

                    {{-- HIDDEN JSON PARA ESTA PROGRAMACIÓN --}}
                    @php
                    $assignedJson = $assigned->map(function($d){
                        return [
                            "detail_id" => $d->id,
                            "user_id"   => $d->user_id,
                            "usertype"  => $d->usertype_id,
                        ];
                    })->toJson();
                    @endphp

                    <input type="hidden" class="assigned_json"
                           value='{{ $assignedJson }}'>

                    <!-- ====================================== -->
                    <!-- 🔹 CAMBIO DE TURNO -->
                    <!-- ====================================== -->
                    <div class="border rounded-lg p-4 bg-slate-50 mb-4">
                        <h4 class="font-semibold text-slate-700 mb-3">
                            <i class="fa-solid fa-clock text-emerald-600"></i> Cambio de Turno
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <div>
                                <label class="text-sm text-slate-600">Turno Actual</label>
                                <input type="text" readonly
                                       value="{{ $program->schedule->name }}"
                                       class="w-full bg-slate-100 border rounded-lg py-2 px-3">
                            </div>

                            <div>
                                <label class="text-sm text-slate-600">Nuevo Turno</label>
                                <select class="mass_newSchedule border rounded-lg py-2 px-3 w-full">
                                    @foreach($schedules as $s)
                                        <option value="{{ $s->id }}"
                                            {{ $s->id == $program->schedule_id ? 'selected':'' }}>
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button type="button"
                                    class="mass-add-change-btn bg-emerald-600 text-white px-4 py-2 rounded-lg"
                                    data-tipo="Turno">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- ====================================== -->
                    <!-- 🔹 CAMBIO DE VEHÍCULO -->
                    <!-- ====================================== -->
                    <div class="border rounded-lg p-4 bg-white mb-4">
                        <h4 class="font-semibold text-slate-700 mb-3">
                            <i class="fa-solid fa-truck text-emerald-600"></i> Cambio de Vehículo
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <div>
                                <label class="text-sm text-slate-600">Vehículo Actual</label>
                                <input type="text" readonly
                                       value="{{ $program->vehicle->plate ?? 'Sin asignar' }}"
                                       class="w-full bg-slate-100 border rounded-lg py-2 px-3">
                            </div>

                            <div>
                                <label class="text-sm text-slate-600">Nuevo Vehículo</label>
                                <select class="mass_newVehicle border rounded-lg py-2 px-3 w-full">
                                    <option value="">Seleccione...</option>
                                    @foreach($vehicles as $v)
                                        <option value="{{ $v->id }}"
                                            {{ $v->id == $program->vehicle_id ? 'selected':'' }}>
                                            {{ $v->plate }} - {{ $v->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-end">
                                <button type="button"
                                        class="mass-add-change-btn bg-emerald-600 text-white px-4 py-2 rounded-lg"
                                        data-tipo="Vehículo">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- ====================================== -->
                    <!-- 🔹 REEMPLAZO DE PERSONAL -->
                    <!-- ====================================== -->
                    <div class="border rounded-lg p-4 bg-white mb-4">
                        <h4 class="font-semibold text-slate-700 mb-3">
                            <i class="fa-solid fa-user-switch text-blue-600"></i> Reemplazo de Personal
                        </h4>

                        <table class="min-w-full border text-sm text-slate-700">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="border px-3 py-2">Actual</th>
                                    <th class="border px-3 py-2">Rol</th>
                                    <th class="border px-3 py-2">Reemplazar Por</th>
                                    <th class="border px-3 py-2 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>

                            @foreach($assigned as $d)
                                @php
                                    $role = $d->usertype_id;
                                @endphp

                                <tr>
                                    <td class="border px-3 py-2">
                                        {{ $d->user->firstname }} {{ $d->user->lastname }}
                                    </td>

                                    <td class="border px-3 py-2">
                                        {{ $d->role_name }}
                                    </td>

                                    <td class="border px-3 py-2">
                                        <select class="mass_replaceSelect border rounded-lg px-2 py-1 w-full"
                                                data-detail="{{ $d->id }}">
                                            <option value="">Seleccione...</option>

                                            @foreach($allEmployees->where('usertype_id', $role) as $emp)
                                                @if($emp->id !== $d->user_id)
                                                    <option value="{{ $emp->id }}">
                                                        {{ $emp->firstname }} {{ $emp->lastname }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="border px-3 py-2 text-center">
                                        <button type="button"
                                                class="mass-replace-btn bg-blue-600 text-white px-3 py-1 rounded-lg"
                                                data-detail="{{ $d->id }}"
                                                data-current="{{ $d->user_id }}"
                                                data-current-name="{{ $d->user->firstname }} {{ $d->user->lastname }}">
                                            <i class="fa-solid fa-right-left"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>

                    <!-- ====================================== -->
                    <!-- 🔹 TABLA CAMBIOS POR ESTA PROGRAMACIÓN -->
                    <!-- ====================================== -->
                    <div class="border rounded-lg p-4 bg-white">
                        <h4 class="font-semibold mb-3 flex items-center gap-2 text-slate-700">
                            <i class="fa-solid fa-list text-emerald-600"></i> Cambios Registrados
                        </h4>

                        <table class="mass_changesTable min-w-full border text-sm text-slate-700">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="border px-3 py-2">Tipo</th>
                                    <th class="border px-3 py-2">Anterior</th>
                                    <th class="border px-3 py-2">Nuevo</th>
                                    <th class="border px-3 py-2">Notas</th>
                                    <th class="border px-3 py-2 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT GLOBAL --}}
<script>
const FETCH_URL = "{{ route('schedulings.fetch-massive') }}";
const MASSIVE_UPDATE_URL = "{{ route('schedulings.update-massive') }}";

// 🔥 Turnos disponibles desde Laravel
const TURNOS = @json($schedules);

// 🔥 Vehículos disponibles desde Laravel
const VEHICULOS = @json($vehicles);

// Neutralizar modal individual
window.setupSchedulingModal = function(){};


document.addEventListener("turbo:frame-load", () => {
    initMassiveEditor();
});


function initMassiveEditor() {

    const filterForm = document.querySelector("#massiveFilter");
    const massiveResults = document.querySelector("#massiveResults");

    if (!filterForm) return;

    filterForm.onsubmit = e => {
        e.preventDefault();
        fetchMassive();
    };

    function fetchMassive() {
        const token = document.querySelector('meta[name="csrf-token"]').content;

        const payload = {
            schedule_id: document.querySelector("#f_schedule").value,
            start_date: document.querySelector("#f_start").value,
            end_date: document.querySelector("#f_end").value
        };

        massiveResults.innerHTML = loader();

        fetch(FETCH_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": token },
            body: JSON.stringify(payload)
        })
            .then(r => r.json())
            .then(data => renderMassive(data))
            .catch(() => massiveResults.innerHTML = errorView());
    }


    // ================================================================
    // 🔹 RENDERIZAR LAS PROGRAMACIONES MASIVAS
    // ================================================================
    function renderMassive(data) {

        if (!data.success || !data.groups.length) {
            massiveResults.innerHTML = emptyView();
            return;
        }

        let html = `
        <button onclick="applyMassive()" 
                class="bg-emerald-600 text-white px-4 py-2 mb-4 rounded-lg hover:bg-emerald-700">
            <i class="fa-solid fa-check-double"></i> Aplicar cambios
        </button>
        <div class="space-y-4">
        `;

        data.groups.forEach(group => {

            group.schedulings.forEach(item => {

                const assigned = [];
                const config = group;

                html += `
                <div class="massive-card border rounded-xl p-4 bg-white shadow-sm"
                     data-id="${item.id}" data-group="${group.group_id}">

                    <input type="hidden" class="assigned_json" value='${JSON.stringify(assigned)}'>
                    <input type="hidden" class="changes_json" value='[]'>

                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-slate-800">${item.date}</p>
                            <p class="text-xs text-slate-500">${group.group_name}</p>
                            <p class="text-xs text-slate-500">${group.zone || ''}</p>
                        </div>

                        <input type="checkbox" class="massive-select h-5 w-5 mt-1">
                    </div>


                    <!-- ================================ -->
                    <!-- CAMBIO DE TURNO -->
                    <!-- ================================ -->
                    <div class="mt-4 border-t pt-3">
                        <label class="text-sm text-slate-600">Turno</label>
                        <select class="m_turno w-full border-slate-300 rounded-lg py-1 px-2">
                            ${turnoOptions(item.schedule_id)}
                        </select>

                        <button class="add-change-turno mt-2 text-emerald-600 flex items-center gap-1 text-sm">
                            <i class="fa-solid fa-plus"></i> Registrar cambio
                        </button>
                    </div>

                    <!-- ================================ -->
                    <!-- CAMBIO DE VEHÍCULO -->
                    <!-- ================================ -->
                    <div class="mt-4 border-t pt-3">
                        <label class="text-sm text-slate-600">Vehículo</label>
                        <select class="m_vehicle w-full border-slate-300 rounded-lg py-1 px-2">
                            ${vehicleOptions(item.vehicle)}
                        </select>

                        <button class="add-change-vehicle mt-2 text-emerald-600 flex items-center gap-1 text-sm">
                            <i class="fa-solid fa-plus"></i> Registrar cambio
                        </button>
                    </div>

                    <!-- ================================ -->
                    <!-- NOTAS -->
                    <!-- ================================ -->
                    <div class="mt-4 border-t pt-3">
                        <label class="text-sm text-slate-600">Notas</label>
                        <input type="text" class="m_notes w-full border-slate-300 rounded-lg py-1 px-2" value="${item.notes || ''}">
                    </div>


                    <!-- ================================ -->
                    <!-- HISTORIAL TEMPORAL -->
                    <!-- ================================ -->
                    <div class="mt-4 border-t pt-3">
                        <p class="text-sm font-semibold text-slate-700 mb-1">Cambios Registrados</p>

                        <table class="w-full text-sm border border-slate-300 changes_table">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="border px-2 py-1">Tipo</th>
                                    <th class="border px-2 py-1">Anterior</th>
                                    <th class="border px-2 py-1">Nuevo</th>
                                    <th class="border px-2 py-1">Notas</th>
                                    <th class="border px-2 py-1">Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
                `;
            });

        });

        html += "</div>";
        massiveResults.innerHTML = html;

        bindMassiveEvents();
    }


    // ================================================================
    // 🔹 ENLAZAR EVENTOS A LOS ELEMENTOS DINÁMICOS
    // ================================================================
    function bindMassiveEvents() {

        document.querySelectorAll(".add-change-turno").forEach(btn => {
            btn.onclick = () => registerChange(btn, "Turno");
        });

        document.querySelectorAll(".add-change-vehicle").forEach(btn => {
            btn.onclick = () => registerChange(btn, "Vehículo");
        });
    }


    // ================================================================
    // 🔹 REGISTRAR CAMBIO DE TURNO / VEHÍCULO
    // ================================================================
    async function registerChange(btn, tipo) {

        const card = btn.closest(".massive-card");
        const changesInput = card.querySelector(".changes_json");
        const table = card.querySelector(".changes_table tbody");

        let anterior = "", nuevo = "";

        if (tipo === "Turno") {
            anterior = "Turno anterior";
            nuevo = card.querySelector(".m_turno").selectedOptions[0].text;
        }

        if (tipo === "Vehículo") {
            anterior = "Vehículo anterior";
            nuevo = card.querySelector(".m_vehicle").selectedOptions[0].text;
        }

        const { value: notas } = await Swal.fire({
            title: "Notas del cambio",
            input: "text",
            showCancelButton: true,
            confirmButtonText: "Agregar"
        });

        if (notas === undefined) return;

        let changes = JSON.parse(changesInput.value);
        changes.push({ tipo, anterior, nuevo, notas });

        changesInput.value = JSON.stringify(changes);

        renderChangesTable(changes, table);
    }


    // ================================================================
    // 🔹 RENDER TABLE
    // ================================================================
    function renderChangesTable(changes, tbody) {
        tbody.innerHTML = "";

        changes.forEach((c, idx) => {
            tbody.insertAdjacentHTML("beforeend", `
                <tr>
                    <td class="border px-2 py-1">${c.tipo}</td>
                    <td class="border px-2 py-1">${c.anterior}</td>
                    <td class="border px-2 py-1">${c.nuevo}</td>
                    <td class="border px-2 py-1">${c.notas}</td>
                    <td class="border px-2 py-1 text-center">
                        <button class="delete-change text-red-600" data-index="${idx}">X</button>
                    </td>
                </tr>
            `);
        });
    }


    // ================================================================
    // 🔹 APLICAR CAMBIOS MASIVOS
    // ================================================================
    window.applyMassive = function () {

        const token = document.querySelector('meta[name="csrf-token"]').content;

        let selected = [...document.querySelectorAll(".massive-select:checked")].map(chk => {
            let card = chk.closest(".massive-card");

            return {
                id: card.dataset.id,
                schedule_id: card.querySelector(".m_turno").value,
                vehicle_id: card.querySelector(".m_vehicle").value || null,
                notes: card.querySelector(".m_notes").value || null,
                changes: JSON.parse(card.querySelector(".changes_json").value),
                assigned_json: JSON.parse(card.querySelector(".assigned_json").value)
            };
        });

        if (!selected.length) {
            Swal.fire("Atención", "Seleccione registros para aplicar cambios", "warning");
            return;
        }

        fetch(MASSIVE_UPDATE_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": token },
            body: JSON.stringify({ updates: selected })
        })
            .then(r => r.json())
            .then(d => {
                Swal.fire("Éxito", "Cambios aplicados correctamente", "success");
                fetchMassive();
            });
    };



    // ================================================================
    // 🔹 VISTAS AUXILIARES
    // ================================================================
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
            Error al cargar información.
        </div>`;
    }


    // ================================================================
    // 🔹 OPCIONES PARA TURNOS Y VEHÍCULOS
    // ================================================================
    function turnoOptions(selected) {
        return TURNOS.map(t => `
            <option value="${t.id}" ${t.id == selected ? 'selected':''}>
                ${t.name}
            </option>
        `).join("");
    }

    function vehicleOptions(selectedName) {
        return VEHICULOS.map(v => `
            <option value="${v.id}">
                ${v.plate} - ${v.name}
            </option>
        `).join("");
    }

}

</script>

</turbo-frame>
