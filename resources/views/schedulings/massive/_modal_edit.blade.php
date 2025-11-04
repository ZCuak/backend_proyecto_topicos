<turbo-frame id="modal-frame">
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl overflow-hidden">
        
        {{-- Header --}}
        <div class="flex justify-between items-center px-6 py-4 border-b bg-slate-50">
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-calendar-pen text-amber-600"></i> Edición Masiva
            </h3>
            <button onclick="Turbo.visit(window.location.href)">
                <i class="fa-solid fa-xmark text-xl text-slate-500 hover:text-slate-700"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-5">

            {{-- Filtro --}}
            <form id="massiveFilter" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf

                <select id="f_schedule" class="border rounded-lg p-2">
                    <option value="">Turno</option>
                    @foreach($schedules as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>

                <input id="f_start" type="date" class="border rounded-lg p-2" value="{{ now()->toDateString() }}">
                <input id="f_end" type="date" class="border rounded-lg p-2" value="{{ now()->addWeek()->toDateString() }}">

                <button class="bg-amber-600 text-white rounded-lg p-2 hover:bg-amber-700 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-search"></i> Buscar
                </button>
            </form>

            {{-- Resultados --}}
            <div id="massiveResults" class="space-y-3"></div>
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

function loadMassive(){
    const token = document.querySelector('meta[name="csrf-token"]').content;

    const payload = {
        schedule_id: document.querySelector('#f_schedule').value,
        start_date: document.querySelector('#f_start').value,
        end_date: document.querySelector('#f_end').value
    };

    let box = document.querySelector("#massiveResults");
    box.innerHTML = `<div class='text-center py-4 text-slate-500'><i class='fa-solid fa-spinner fa-spin'></i> Cargando...</div>`

    fetch("{{ route('schedulings.fetch-massive') }}", {
        method: "POST",
        headers: {"Content-Type":"application/json", "X-CSRF-TOKEN":token},
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => renderMassive(data))
    .catch(() => box.innerHTML = errorView());
}

function renderMassive(data){
    const box = document.querySelector("#massiveResults");

    if(!data.success || !data.groups.length){
        box.innerHTML = emptyView();
        return;
    }

    let html = `
        <button onclick="saveMassive()" class="bg-emerald-600 text-white px-4 py-2 mb-3 rounded-lg hover:bg-emerald-700">
            <i class="fa-solid fa-check-double"></i> Aplicar cambios
        </button>
        <div class="space-y-2">
    `;

    data.groups.forEach(g => {
        g.schedulings.forEach(s => {
            html += `
            <div class="flex flex-col md:flex-row justify-between items-center border rounded-lg p-3 bg-white gap-3"
                 data-id="${s.id}">
                 
                <div>
                    <p class="font-semibold">${s.date}</p>
                    <p class="text-xs text-slate-500">${g.group_name}</p>
                </div>

                <div class="flex flex-wrap gap-2">

                    <select class="m-field vehicle border rounded px-2 py-1 text-sm">
                        <option value="">Vehículo</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>

                    <select class="m-field status border rounded px-2 py-1 text-sm">
                        <option value="">Estado</option>
                        <option value="0">Pendiente</option>
                        <option value="2">Completado</option>
                        <option value="3">Cancelado</option>
                    </select>

                    <input placeholder="Notas" class="m-field notes border rounded px-2 py-1 text-sm" value="${s.notes ?? ''}">
                </div>

                <input type="checkbox" class="m-check h-5 w-5">
            </div>`;
        });
    });

    html += "</div>";
    box.innerHTML = html;
}

function saveMassive(){
    const token = document.querySelector('meta[name="csrf-token"]').content;

    let rows = [...document.querySelectorAll(".m-check:checked")].map(c => {
        let row = c.closest("[data-id]");
        return {
            id: row.dataset.id,
            vehicle_id: row.querySelector(".vehicle").value || null,
            status: row.querySelector(".status").value || null,
            notes: row.querySelector(".notes").value || null
        };
    });

    if(!rows.length) return alert("Seleccione registros");

    fetch("{{ route('schedulings.update-massive') }}", {
        method:"POST",
        headers:{"Content-Type":"application/json","X-CSRF-TOKEN":token},
        body: JSON.stringify({ updates:rows })
    })
    .then(r=>r.json())
    .then(d=>{
        alert("✅ Guardado correctamente");
        loadMassive();
    });
}

function emptyView(){ return `<div class='p-4 bg-amber-50 text-amber-700 rounded-lg border'>No hay programaciones.</div>`; }
function errorView(){ return `<div class='p-4 bg-red-50 text-red-700 rounded-lg border'>Error al cargar.</div>`; }
</script>
</turbo-frame>
