<turbo-frame id="modal-frame">
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999] overflow-y-auto">

<div class="bg-white rounded-2xl shadow-2xl w-full max-w-7xl overflow-hidden my-10">

    <!-- ===================================== -->
    <!-- HEADER -->
    <!-- ===================================== -->
    <div class="flex justify-between items-center px-6 py-4 border-b bg-slate-50">
        <h3 class="font-bold text-xl text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-calendar-pen text-amber-600"></i> Edición Masiva de Programaciones
        </h3>
        <button onclick="Turbo.visit(window.location.href)">
            <i class="fa-solid fa-xmark text-xl text-slate-500 hover:text-slate-700"></i>
        </button>
    </div>


    <!-- ===================================== -->
    <!-- FILTRO -->
    <!-- ===================================== -->
    <div class="p-6">
        <form id="massiveFilter" class="grid grid-cols-1 md:grid-cols-5 gap-4">

            @csrf

            <div>
                <label class="text-sm font-semibold text-slate-700">Turno</label>
                <select id="f_schedule" class="border rounded-lg p-2 w-full">
                    <option value="">Todos</option>
                    @foreach($schedules as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Desde</label>
                <input id="f_start" type="date"
                    class="border rounded-lg p-2 w-full"
                    value="{{ now()->toDateString() }}">
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Hasta</label>
                <input id="f_end" type="date"
                    class="border rounded-lg p-2 w-full"
                    value="{{ now()->addWeek()->toDateString() }}">
            </div>

            <div class="flex items-end">
                <button class="bg-amber-600 text-white rounded-lg p-2 w-full hover:bg-amber-700 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-search"></i> Buscar
                </button>
            </div>

        </form>
    </div>


    <!-- ===================================== -->
    <!-- RESULTADOS -->
    <!-- ===================================== -->
    <div id="massiveResults" class="p-6 space-y-6"></div>


    <!-- ===================================== -->
    <!-- BOTÓN GUARDAR CAMBIOS -->
    <!-- ===================================== -->
    <div id="massiveSaveContainer" class="hidden p-6 border-t bg-slate-50">
        <button onclick="saveMassiveChanges()"
            class="bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700 flex items-center gap-2 text-lg mx-auto">
            <i class="fa-solid fa-floppy-disk"></i> Guardar Todos los Cambios
        </button>
    </div>

</div>
</div>

<script>
document.addEventListener("turbo:frame-load", () => {
    document.querySelector("#massiveFilter").onsubmit = e => {
        e.preventDefault();
        loadMassive();
    };
});


// ===========================================================
// 🔥 CARGAR PROGRAMACIONES DEL RANGO DE FECHAS
// ===========================================================
function loadMassive() {

    const token = document.querySelector('meta[name="csrf-token"]').content;
    const box = document.querySelector("#massiveResults");
    const saveBox = document.querySelector("#massiveSaveContainer");

    saveBox.classList.add("hidden");
    box.innerHTML = `
        <div class='text-center py-10 text-slate-500'>
            <i class='fa-solid fa-spinner fa-spin text-3xl'></i>
            <p class='mt-2'>Cargando...</p>
        </div>
    `;

    fetch("{{ route('schedulings.fetch-massive') }}", {
        method: "POST",
        headers: {"Content-Type":"application/json", "X-CSRF-TOKEN": token},
        body: JSON.stringify({
            schedule_id: document.querySelector('#f_schedule').value,
            start_date: document.querySelector('#f_start').value,
            end_date: document.querySelector('#f_end').value
        })
    })
    .then(r => r.json())
    .then(data => renderMassive(data))
    .catch(err => {
        box.innerHTML = `<div class="p-4 bg-red-50 text-red-700 rounded-lg">
            Error al cargar datos.
        </div>`;
    });
}


// ===========================================================
// 🔥 RENDERIZAR TODA LA ESTRUCTURA EXACTA DEL EDITAR INDIVIDUAL
// ===========================================================
function renderMassive(data){

    const box = document.querySelector("#massiveResults");
    const saveBox = document.querySelector("#massiveSaveContainer");

    if(!data.success || data.schedulings.length === 0){
        box.innerHTML = `<div class="p-4 text-amber-700 bg-amber-50 rounded-lg">No hay programaciones.</div>`;
        return;
    }

    saveBox.classList.remove("hidden");

    let html = "";

    data.schedulings.forEach(item => {

        const s = item.scheduling;
        const assigned = item.assigned;
        const employees = item.allEmployees;
        const schedules = @json($schedules);
        const vehicles = @json($vehicles);

        html += `

        <!-- ===================================================== -->
        <!-- 🔥 BLOQUE COMPLETO DE UNA PROGRAMACIÓN -->
        <!-- ===================================================== -->
        <div class="border border-slate-300 rounded-xl p-6 bg-white shadow-sm space-y-6"
             data-id="${s.id}">

            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-calendar-day text-emerald-600"></i>
                Programación #${s.id} — ${s.date}
            </h3>

            <!-- HIDDEN JSON DE PERSONAL -->
            <input type="hidden"
                   class="assigned_json"
                   value='${JSON.stringify(assigned)}'>


            <!-- ============================= -->
            <!-- 🔹 CAMBIO DE TURNO -->
            <!-- ============================= -->
            <div class="border border-slate-200 rounded-xl p-5 bg-slate-50/60">
                <h4 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-clock text-emerald-600"></i> Cambio de Turno
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600">Turno Actual</label>
                        <input type="text" readonly value="${s.schedule_name}"
                               class="w-full bg-slate-100 border-slate-200 rounded-lg py-2 px-3">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-600">Nuevo Turno</label>
                        <select class="newSchedule w-full border-slate-300 rounded-lg py-2 px-3">
                            ${schedules.map(sc =>
                                `<option value="${sc.id}" ${sc.id==s.schedule_id?'selected':''}>${sc.name}</option>`
                            ).join('')}
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button class="add-change-btn bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700"
                                data-type="Turno">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>


            <!-- ============================= -->
            <!-- 🔹 CAMBIO DE VEHÍCULO -->
            <!-- ============================= -->
            <div class="border border-slate-200 rounded-xl p-5 bg-white">
                <h4 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-truck text-emerald-600"></i> Cambio de Vehículo
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600">Actual</label>
                        <input type="text" readonly
                               value="${s.vehicle_plate ?? 'Sin asignar'}"
                               class="w-full bg-slate-100 border-slate-200 rounded-lg py-2 px-3">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-600">Nuevo Vehículo</label>
                        <select class="newVehicle w-full border-slate-300 rounded-lg py-2 px-3">
                            <option value="">Seleccione...</option>
                            ${vehicles.map(v =>
                                `<option value="${v.id}" ${v.id==s.vehicle_id?'selected':''}>
                                    ${v.plate} - ${v.name}
                                 </option>`
                            ).join('')}
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button class="add-change-btn bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700"
                                data-type="Vehículo">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>


            <!-- ============================= -->
            <!-- 🔹 REEMPLAZO DE PERSONAL -->
            <!-- ============================= -->
            <div class="border border-slate-200 rounded-xl p-5 bg-white">
                <h4 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-user-switch text-blue-600"></i> Reemplazo de Personal
                </h4>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-slate-200 text-sm text-slate-700">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="border px-4 py-2">Actual</th>
                                <th class="border px-4 py-2">Rol</th>
                                <th class="border px-4 py-2">Reemplazar Por</th>
                                <th class="border px-4 py-2 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>

                            ${assigned.map(d => `
                                <tr>
                                    <td class="border px-4 py-2">${d.user_name}</td>
                                    <td class="border px-4 py-2">${d.role_name}</td>
                                    <td class="border px-4 py-2">
                                        <select class="replaceSelect w-full border-slate-300 rounded-lg py-1 px-2"
                                                data-detail="${d.detail_id}">
                                            <option value="">Seleccione...</option>
                                            
                                            ${employees
                                                .filter(e => e.usertype_id == d.usertype)
                                                .filter(e => e.id != d.user_id)
                                                .map(e =>
                                                    `<option value="${e.id}">${e.firstname} ${e.lastname}</option>`
                                                ).join('')}
                                        </select>
                                    </td>
                                    <td class="border px-4 py-2 text-center">
                                        <button class="replace-btn bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700"
                                            data-detail="${d.detail_id}" 
                                            data-current="${d.user_id}" 
                                            data-current-name="${d.user_name}">
                                            <i class="fa-solid fa-right-left"></i> Reemplazar
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}

                        </tbody>
                    </table>
                </div>
            </div>


            <!-- ============================= -->
            <!-- 🔹 TABLA DE CAMBIOS -->
            <!-- ============================= -->
            <div class="border border-slate-200 rounded-xl p-5 bg-white">
                <h4 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-list text-emerald-600"></i> Cambios Registrados
                </h4>

                <table class="changeTable min-w-full border border-slate-200 text-sm text-slate-700">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="border px-4 py-2">Tipo</th>
                            <th class="border px-4 py-2">Anterior</th>
                            <th class="border px-4 py-2">Nuevo</th>
                            <th class="border px-4 py-2">Notas</th>
                            <th class="border px-4 py-2 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>

        </div>

        `;
    });

    box.innerHTML = html;

    setupMassiveJS();
}



// ===========================================================
// 🔥 JS PARA CADA BLOQUE DE PROGRAMACIÓN
// ===========================================================
function setupMassiveJS(){

    document.querySelectorAll(".add-change-btn").forEach(btn => {
        btn.onclick = async function(){

            const card = btn.closest("[data-id]");
            const schedulingId = card.dataset.id;

            let tipo = btn.dataset.type;
            let anterior = "";
            let nuevo = "";

            if(tipo === "Turno"){
                anterior = card.querySelector("input[readonly]").value;
                nuevo = card.querySelector(".newSchedule").selectedOptions[0].text;
            }

            if(tipo === "Vehículo"){
                anterior = card.querySelector("input[readonly]").value;
                nuevo = card.querySelector(".newVehicle").selectedOptions[0].text;
            }

            if(!nuevo || nuevo===anterior){
                Swal.fire("Atención", "Seleccione un valor diferente.", "warning");
                return;
            }

            const { value: notas } = await Swal.fire({
                title:"Notas",
                input:"text",
                showCancelButton:true
            });

            if(notas === undefined) return;

            const table = card.querySelector(".changeTable tbody");
            table.insertAdjacentHTML("beforeend", `
                <tr>
                    <td class="border px-4 py-2">${tipo}</td>
                    <td class="border px-4 py-2">${anterior}</td>
                    <td class="border px-4 py-2">${nuevo}</td>
                    <td class="border px-4 py-2">${notas}</td>
                    <td class="border px-4 py-2 text-center">
                        <button class="del-change text-red-600"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
            `);
        };
    });


    // 🔥 REEMPLAZO DE PERSONAL
    document.querySelectorAll(".replace-btn").forEach(btn => {
        btn.onclick = async function(){

            const card = btn.closest("[data-id]");
            const assignedInput = card.querySelector(".assigned_json");

            let assigned = JSON.parse(assignedInput.value);

            let detailId = parseInt(btn.dataset.detail);
            let currentId = parseInt(btn.dataset.current);
            let currentName = btn.dataset.currentName;

            let select = btn.closest("tr").querySelector(".replaceSelect");
            let newId = parseInt(select.value);
            let newName = select.selectedOptions[0]?.text;

            if(!newId){
                Swal.fire("Atención","Seleccione un empleado válido","warning");
                return;
            }

            const { value: notas } = await Swal.fire({
                title:"Notas",
                input:"text",
                showCancelButton:true
            });

            if(notas === undefined) return;

            // actualizar json
            assigned = assigned.map(d =>
                d.detail_id === detailId
                    ? { ...d, user_id:newId }
                    : d
            );

            assignedInput.value = JSON.stringify(assigned);

            // añadir al historial
            const table = card.querySelector(".changeTable tbody");
            table.insertAdjacentHTML("beforeend", `
                <tr>
                    <td class="border px-4 py-2">Reemplazo Personal</td>
                    <td class="border px-4 py-2">${currentName}</td>
                    <td class="border px-4 py-2">${newName}</td>
                    <td class="border px-4 py-2">${notas}</td>
                    <td class="border px-4 py-2 text-center">
                        <button class="del-change text-red-600">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        };
    });


    // 🔥 BORRAR CAMBIO
    document.querySelectorAll(".changeTable tbody").forEach(tbody => {
        tbody.onclick = function(e){
            if(e.target.closest(".del-change")){
                e.target.closest("tr").remove();
            }
        };
    });
}



// ===========================================================
// 🔥 GUARDAR CAMBIOS MASIVOS
// ===========================================================
function saveMassiveChanges(){

    let payload = [];

    document.querySelectorAll("[data-id]").forEach(card => {

        const schedulingId = parseInt(card.dataset.id);

        const schedule_id = parseInt(card.querySelector(".newSchedule").value);
        const vehicle_id = card.querySelector(".newVehicle").value || null;
        const assigned_json = card.querySelector(".assigned_json").value;

        // cambios del historial
        let changes = [];
        card.querySelectorAll(".changeTable tbody tr").forEach(tr => {
            let tds = tr.querySelectorAll("td");
            changes.push({
                tipo: tds[0].innerText,
                anterior: tds[1].innerText,
                nuevo: tds[2].innerText,
                notas: tds[3].innerText
            });
        });

        payload.push({
            id: schedulingId,
            schedule_id,
            vehicle_id,
            assigned_json,
            changes
        });
    });

    const token = document.querySelector('meta[name="csrf-token"]').content;

    fetch("{{ route('schedulings.update-massive') }}", {
        method:"POST",
        headers:{
            "Content-Type":"application/json",
            "X-CSRF-TOKEN": token
        },
        body: JSON.stringify({ updates: payload })
    })
    .then(r=>r.json())
    .then(res=>{
        Swal.fire("Éxito", "Cambios guardados correctamente", "success");
    })
    .catch(err=>{
        Swal.fire("Error","Ocurrió un problema","error")
    });
}

</script>
</turbo-frame>
