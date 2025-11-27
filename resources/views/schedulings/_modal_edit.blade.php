<turbo-frame id="modal-frame">
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9998]">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl overflow-hidden mx-4 sm:mx-6 my-10">
        
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b bg-slate-50">
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-calendar text-emerald-600"></i> Editar Programación
            </h3>
            <button onclick="Turbo.visit(window.location.href)">
                <i class="fa-solid fa-xmark text-xl text-slate-500 hover:text-slate-700"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 bg-white overflow-y-auto max-h-[85vh]">
            <form id="editSchedulingForm"
                action="{{ route('schedulings.update', $scheduling->id) }}"
                method="POST"
                data-turbo-frame="modal-frame"
                class="space-y-6">
                @csrf
                @method('PUT')

                <!-- hidden -->
                <input type="hidden" name="group_id" value="{{ $scheduling->group_id }}">
                <input type="hidden" name="zone_id" value="{{ $scheduling->zone_id }}">
                <input type="hidden" name="date" value="{{ $scheduling->date }}">
                <input type="hidden" name="status" value="{{ $scheduling->status }}">
                <input type="hidden" name="notes" id="add_notes">

             <input type="hidden" id="assigned_json" name="assigned_json"
                    value='{{ $assigned->map(fn($d)=>[
                            "detail_id"=>$d->id,
                            "user_id"=>$d->user_id,
                            "usertype"=>$d->usertype_id,
                        ])->toJson() }}'>

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
                            <input type="text" value="{{ $scheduling->schedule->name }}" readonly
                                   class="w-full bg-slate-100 border-slate-200 rounded-lg py-2 px-3">
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-600">Nuevo Turno</label>
                            <select name="schedule_id" id="newSchedule"
                                class="w-full border-slate-300 rounded-lg py-2 px-3">
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}"
                                        {{ $schedule->id == $scheduling->schedule_id ? 'selected':'' }}>
                                        {{ $schedule->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="button"
                                class="add-change-btn bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700"
                                data-tipo="Turno">
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
                            <label class="text-sm font-medium text-slate-600">Vehículo Actual</label>
                            <input type="text" readonly
                                  value="{{ $scheduling->vehicle->plate ?? 'Sin asignar' }}"
                                  class="w-full bg-slate-100 border-slate-200 rounded-lg py-2 px-3">
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-600">Nuevo Vehículo</label>
                            <select name="vehicle_id" id="newVehicle"
                                class="w-full border-slate-300 rounded-lg py-2 px-3">
                                <option value="">Seleccione un vehículo</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}"
                                        {{ $vehicle->id == $scheduling->vehicle_id ? 'selected':'' }}>
                                        {{ $vehicle->plate }} - {{ $vehicle->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="button"
                                class="add-change-btn bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700"
                                data-tipo="Vehículo">
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
        <i class="fa-solid fa-user-switch text-blue-600"></i> Reemplazo de Personal Asignado
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

                @foreach($assigned as $detail)
                @php
                    $requiredUsertype = $detail->usertype_id; 
                @endphp

                <tr>
                    <td class="border px-4 py-2">
                        {{ $detail->user->firstname }} {{ $detail->user->lastname }}
                    </td>

                    <td class="border px-4 py-2">
                        {{ $detail->role_name }}
                    </td>

                    <td class="border px-4 py-2">
                        <select class="newReplaceSelect w-full border-slate-300 rounded-lg py-1 px-2"
                            data-detail="{{ $detail->id }}">

                            <option value="">Seleccione...</option>

                            @foreach($allEmployees->where('usertype_id', $requiredUsertype) as $emp)
                                @if($emp->id !== $detail->user_id)
                                    <option value="{{ $emp->id }}">
                                        {{ $emp->firstname }} {{ $emp->lastname }}
                                    </option>
                                @endif
                            @endforeach

                        </select>
                    </td>

                    <td class="border px-4 py-2 text-center">
                        <button type="button"
                            class="replace-btn bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700"
                            data-detail="{{ $detail->id }}"
                            data-current="{{ $detail->user_id }}"
                            data-current-name="{{ $detail->user->firstname }} {{ $detail->user->lastname }}">

                            <i class="fa-solid fa-right-left"></i> Reemplazar
                        </button>
                    </td>
                </tr>

                @endforeach

            </tbody>
        </table>
    </div>
</div>


                <!-- ============================= -->
                <!-- 🔹 TABLA DE CAMBIOS REGISTRADOS -->
                <!-- ============================= -->
                <div class="border border-slate-200 rounded-xl p-5 bg-white">
                    <h4 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-list text-emerald-600"></i> Cambios Registrados
                    </h4>

                    <div class="overflow-x-auto">
                        <table id="changesTable"
                               class="min-w-full border border-slate-200 text-sm text-slate-700">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="border px-4 py-2">Tipo</th>
                                    <th class="border px-4 py-2">Anterior</th>
                                    <th class="border px-4 py-2">Nuevo</th>
                                    <th class="border px-4 py-2">Notas</th>
                                    <th class="border px-4 py-2">Motivo</th>
                                    <th class="border px-4 py-2 text-center">Acci??n</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- ============================= -->
                <!-- BOTONES -->
                <!-- ============================= -->
                <div class="flex justify-end gap-3 pt-5 border-t border-slate-200">
                    <button type="button" onclick="Turbo.visit(window.location.href)"
                        class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">
                        <i class="fa-solid fa-xmark mr-1"></i> Cancelar
                    </button>

                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Guardar Cambios
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


<!-- ===================================== -->
<!-- 🔥 SCRIPT -->
<!-- ===================================== -->
<script>
document.addEventListener("turbo:frame-load", setupSchedulingModal);

function setupSchedulingModal() {

    const tbody = document.querySelector("#changesTable tbody");
    const notesInput = document.querySelector("#add_notes");
    const assignedInput = document.querySelector("#assigned_json");

    let cambios = [];
    let assigned = JSON.parse(assignedInput.value); 
    // [{detail_id, user_id, usertype}, ...]

    const motives = @json($motives->map(fn($m)=>['id'=>$m->id,'name'=>$m->name]));
    const motivesMap = Object.fromEntries(motives.map(m => [m.id, m.name]));

    const askNoteWithMotive = async () => {
        const options = motives.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
        const { value: formValues } = await Swal.fire({
            title: "Notas del cambio",
            html: `
                <div class="space-y-4 text-left text-slate-700">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Motivo</label>
                        <select id="swal-motive"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white text-slate-700">
                            <option value="">-- Selecciona un motivo --</option>
                            ${options}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Detalle</label>
                        <textarea id="swal-note"
                                  rows="3"
                                  class="w-full rounded-lg border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-slate-50 text-slate-700 placeholder-slate-400"
                                  placeholder="Ej. Ajuste por solicitud"></textarea>
                    </div>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: "Agregar",
            cancelButtonText: "Cancelar",
            buttonsStyling: false,
            customClass: {
                popup: 'rounded-2xl shadow-2xl border border-slate-200',
                confirmButton: 'px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition font-semibold',
                cancelButton: 'px-4 py-2 rounded-lg bg-slate-200 text-slate-700 hover:bg-slate-300 transition font-semibold'
            },
            preConfirm: () => {
                const motiveId = document.getElementById('swal-motive').value || null;
                const notas = document.getElementById('swal-note').value || '';
                return { notas, motive_id: motiveId ? parseInt(motiveId, 10) : null };
            }
        });
        return formValues;
    };

    /* ------------------------------ */
    /*    BOTONES TURNO / VEHÍCULO    */
    /* ------------------------------ */
    document.querySelectorAll(".add-change-btn").forEach(btn => {
        btn.onclick = async () => {

            const tipo = btn.dataset.tipo;
            let anterior = "", nuevo = "";

            if (tipo === "Turno") {
                anterior = "{{ $scheduling->schedule->name }}";
                nuevo = document.querySelector("#newSchedule")?.selectedOptions[0]?.text;
            }

            if (tipo === "Vehículo") {
                anterior = "{{ $scheduling->vehicle->plate ?? 'Sin vehículo' }}";
                nuevo = document.querySelector("#newVehicle")?.selectedOptions[0]?.text;
            }

            if (!nuevo || nuevo === anterior) {
                Swal.fire("Atención","Seleccione otro valor.","warning");
                return;
            }

            const res = await askNoteWithMotive();

            if (res !== undefined) {
                cambios.push({
                    tipo,
                    anterior,
                    nuevo,
                    notas: res?.notas ?? '',
                    motive_id: res?.motive_id ?? null,
                    motive_name: res?.motive_id ? motivesMap[res.motive_id] : '-'
                });
                renderCambios();
            }
        };
    });


    /* ------------------------------ */
    /*       REEMPLAZO PERSONAL       */
    /* ------------------------------ */
    document.querySelectorAll(".replace-btn").forEach(btn => {
        btn.onclick = async () => {

            let detailId = parseInt(btn.dataset.detail);
            let currentId = parseInt(btn.dataset.current);
            let currentName = btn.dataset["currentName"];

            let select = btn.closest("tr").querySelector(".newReplaceSelect");
            let newId = parseInt(select.value);
            let newName = select.selectedOptions[0]?.text || "";

            if (!newId || newId === currentId) {
                Swal.fire("Atención", "Seleccione un empleado válido", "warning");
                return;
            }

            const res = await askNoteWithMotive();
            if (res === undefined) return;

            // 🔥 ACTUALIZAR JSON assigned
            assigned = assigned.map(d =>
                d.detail_id === detailId
                    ? { ...d, user_id: newId }
                    : d
            );

            assignedInput.value = JSON.stringify(assigned);

            // 🔥 Registrar cambio en historial temporal del modal
            cambios.push({
                tipo: "Reemplazo Personal",
                anterior: currentName,
                nuevo: newName,
                notas: res?.notas ?? '',
                motive_id: res?.motive_id ?? null,
                motive_name: res?.motive_id ? motivesMap[res.motive_id] : '-'
            });

            renderCambios();
        };
    });


    /* ------------------------------ */
    /*     TABLA CAMBIOS REGISTRADOS */
    /* ------------------------------ */
    function renderCambios() {
        tbody.innerHTML = "";
        cambios.forEach((c, idx) => {
            tbody.insertAdjacentHTML("beforeend", `
                <tr>
                    <td class="border px-4 py-2">${c.tipo}</td>
                    <td class="border px-4 py-2">${c.anterior}</td>
                    <td class="border px-4 py-2">${c.nuevo}</td>
                    <td class="border px-4 py-2">${c.notas ?? ''}</td>
                    <td class="border px-4 py-2">${c.motive_name ?? '-'}</td>
                    <td class="border px-4 py-2 text-center">
                        <button data-index="${idx}" class="delete-change text-red-600 hover:text-red-800">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        notesInput.value = JSON.stringify(cambios);
    }

    /* ------------------------------ */
    /*   ELIMINAR UN CAMBIO           */
    /* ------------------------------ */
    tbody.addEventListener("click", e => {
        let btn = e.target.closest(".delete-change");
        if (!btn) return;

        cambios.splice(btn.dataset.index, 1);
        renderCambios();
    });
}
</script>

</turbo-frame>








