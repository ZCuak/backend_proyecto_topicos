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

                <input type="hidden" name="group_id" value="{{ $scheduling->group_id }}">
                <input type="hidden" name="zone_id" value="{{ $scheduling->zone_id }}">
                <input type="hidden" name="date" value="{{ $scheduling->date }}">
                <input type="hidden" name="status" value="{{ $scheduling->status }}">
                <input type="hidden" name="notes" id="add_notes"> 

                <!-- 🔹 CAMBIO DE TURNO -->
                <div class="border border-slate-200 rounded-xl p-5 bg-slate-50/60">
                    <h4 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-clock text-emerald-600"></i> Cambio de Turno
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-600">Turno Actual</label>
                            <input type="text" value="{{ $scheduling->schedule->name ?? '' }}" readonly
                                class="w-full bg-slate-100 border-slate-200 rounded-lg py-2 px-3">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600">Nuevo Turno</label>
                            <select name="schedule_id" id="newSchedule" class="w-full border-slate-300 rounded-lg py-2 px-3">
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}" {{ $scheduling->schedule_id == $schedule->id ? 'selected' : '' }}>
                                        {{ $schedule->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="button"
                                class="add-change-btn flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                                data-tipo="Turno">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 🔹 CAMBIO DE VEHÍCULO -->
                <div class="border border-slate-200 rounded-xl p-5 bg-white">
                    <h4 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-truck text-emerald-600"></i> Cambio de Vehículo
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-600">Vehículo Actual</label>
                            <input type="text" value="{{ $scheduling->vehicle->plate ?? 'Sin asignar' }}" readonly
                                class="w-full bg-slate-100 border-slate-200 rounded-lg py-2 px-3">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600">Nuevo Vehículo</label>
                            <select name="vehicle_id" id="newVehicle" class="w-full border-slate-300 rounded-lg py-2 px-3">
                                <option value="">Seleccione un vehículo</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ $scheduling->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                        {{ $vehicle->plate }} - {{ $vehicle->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="button"
                                class="add-change-btn flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                                data-tipo="Vehículo">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 🔹 CAMBIO DE PERSONAL -->
                <div class="border border-slate-200 rounded-xl p-5 bg-slate-50/60">
                    <h4 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-users text-emerald-600"></i> Cambio de Personal
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-600">Personal Actual</label>
                            <select id="oldEmployee" class="w-full border-slate-300 rounded-lg py-2 px-3">
                                @foreach($scheduling->group->employees ?? [] as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->firstname }} {{ $emp->lastname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600">Nuevo Personal</label>
                            <select id="newEmployee" class="w-full border-slate-300 rounded-lg py-2 px-3">
                                @foreach($scheduling->group->employees ?? [] as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->firstname }} {{ $emp->lastname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="button"
                                class="add-change-btn flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                                data-tipo="Personal">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 🔹 CAMBIOS REGISTRADOS -->
                <div class="border border-slate-200 rounded-xl p-5 bg-white">
                    <h4 class="font-semibold text-slate-700 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-list text-emerald-600"></i> Cambios Registrados
                    </h4>
                    <div class="overflow-x-auto">
                        <table id="changesTable" class="min-w-full border border-slate-200 text-sm text-slate-700">
                            <thead class="bg-slate-100 text-slate-600">
                                <tr>
                                    <th class="px-4 py-2 border">Tipo</th>
                                    <th class="px-4 py-2 border">Anterior</th>
                                    <th class="px-4 py-2 border">Nuevo</th>
                                    <th class="px-4 py-2 border">Notas</th>
                                    <th class="px-4 py-2 border text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- BOTONES -->
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

<script>
document.addEventListener("turbo:frame-load", () => setupSchedulingModal());

function setupSchedulingModal() {
    const tbody = document.querySelector("#changesTable tbody");
    const inputHidden = document.querySelector("#add_notes");
    const buttons = document.querySelectorAll(".add-change-btn");
    let cambios = [];

    buttons.forEach(btn => {
        btn.onclick = async () => {
            const tipo = btn.dataset.tipo;
            let anterior = "", nuevo = "";

            if (tipo === "Turno") {
                anterior = "{{ $scheduling->schedule->name ?? 'Sin turno' }}";
                nuevo = document.querySelector("#newSchedule")?.selectedOptions[0]?.text || "";
            } else if (tipo === "Vehículo") {
                anterior = "{{ $scheduling->vehicle->plate ?? 'Sin vehículo' }}";
                nuevo = document.querySelector("#newVehicle")?.selectedOptions[0]?.text || "";
            } else if (tipo === "Personal") {
                anterior = document.querySelector("#oldEmployee")?.selectedOptions[0]?.text || "";
                nuevo = document.querySelector("#newEmployee")?.selectedOptions[0]?.text || "";
            }

            if (!nuevo || nuevo === anterior) {
                Swal.fire("Atención", "Selecciona un valor diferente antes de registrar el cambio.", "warning");
                return;
            }

            const { value: notas } = await Swal.fire({
                title: "Notas del cambio",
                input: "text",
                inputPlaceholder: "Motivo o comentario...",
                showCancelButton: true,
                confirmButtonText: "Agregar",
                confirmButtonColor: "#10b981"
            });

            if (notas !== undefined) {
                cambios.push({ tipo, anterior, nuevo, notas: notas || "" });
                renderCambios();
            }
        };
    });

    function renderCambios() {
        tbody.innerHTML = "";
        cambios.forEach((c, i) => {
            tbody.insertAdjacentHTML("beforeend", `
                <tr>
                    <td class="border px-4 py-2">${c.tipo}</td>
                    <td class="border px-4 py-2">${c.anterior}</td>
                    <td class="border px-4 py-2">${c.nuevo}</td>
                    <td class="border px-4 py-2">${c.notas}</td>
                    <td class="border px-4 py-2 text-center">
                        <button type="button" class="delete-change text-red-600 hover:text-red-800" data-index="${i}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
        inputHidden.value = JSON.stringify(cambios);
    }

    tbody.addEventListener("click", e => {
        const btn = e.target.closest(".delete-change");
        if (btn) {
            cambios.splice(parseInt(btn.dataset.index), 1);
            renderCambios();
        }
    });
}
</script>
</turbo-frame>
