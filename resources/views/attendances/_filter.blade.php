<div id="filtersModal"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden animate-[flyonui-fade-in_0.3s_ease-out]">

        {{-- Header --}}
        <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200 bg-emerald-50">
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-filter text-emerald-600"></i>
                Filtros Avanzados
            </h3>
            <button type="button" onclick="closeFiltersModal()" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
            <form method="GET" action="{{ route('attendances.index') }}" id="filtersForm">

                {{-- Mantener búsqueda activa --}}
                @if (request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif

                <div class="space-y-6">

                    {{-- SECCIÓN DE FECHAS --}}
                    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50">
                        <legend class="px-3 text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-days text-emerald-600"></i>
                            Rango de Fechas
                        </legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    <i class="fa-solid fa-calendar-day mr-1 text-emerald-600"></i>
                                    Fecha Inicial
                                </label>
                                <input type="date" name="start_date" id="start_date"
                                    value="{{ request('start_date') }}"
                                    class="w-full px-4 py-2.5 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    <i class="fa-solid fa-calendar-check mr-1 text-emerald-600"></i>
                                    Fecha Final <span class="text-slate-400 text-xs">(Opcional)</span>
                                </label>
                                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                                    class="w-full px-4 py-2.5 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-xs text-blue-700 flex items-start gap-2">
                                <i class="fa-solid fa-info-circle mt-0.5"></i>
                                <span>
                                    <strong>¿Cómo funciona?</strong><br>
                                    • Si ingresas <strong>ambas fechas</strong> → Se busca en todo el rango<br>
                                    • Si ingresas <strong>solo la inicial</strong> → Se busca solo ese día<br>
                                    • Si no ingresas nada → Se muestran las asistencias de <strong>hoy</strong>
                                </span>
                            </p>
                        </div>
                    </fieldset>

                    {{-- OTROS FILTROS --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fa-solid fa-arrows-left-right mr-1 text-emerald-600"></i>
                                Tipo de Marcación
                            </label>
                            <select name="type"
                                class="w-full px-4 py-2.5 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Todos los tipos</option>
                                <option value="ENTRADA" {{ request('type') == 'ENTRADA' ? 'selected' : '' }}>
                                    🟢 ENTRADA
                                </option>
                                <option value="SALIDA" {{ request('type') == 'SALIDA' ? 'selected' : '' }}>
                                    🔴 SALIDA
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i>
                                Estado
                            </label>
                            <select name="status"
                                class="w-full px-4 py-2.5 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Todos los estados</option>
                                <option value="PRESENTE" {{ request('status') == 'PRESENTE' ? 'selected' : '' }}>
                                    ✅ PRESENTE
                                </option>
                                <option value="TARDANZA" {{ request('status') == 'TARDANZA' ? 'selected' : '' }}>
                                    ⚠️ TARDANZA
                                </option>
                                <option value="AUSENTE" {{ request('status') == 'AUSENTE' ? 'selected' : '' }}>
                                    ❌ AUSENTE
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-200">
                    <button type="button" onclick="document.getElementById('filtersForm').reset()"
                        class="px-5 py-2.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                        <i class="fa-solid fa-rotate-left mr-1"></i>
                        Limpiar
                    </button>
                    <button type="button" onclick="closeFiltersModal()"
                        class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition">
                        <i class="fa-solid fa-xmark mr-1"></i>
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-md">
                        <i class="fa-solid fa-check mr-1"></i>
                        Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
    

    // Validación de fechas
    document.addEventListener('DOMContentLoaded', function() {
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');

        if (startDate && endDate) {
            startDate.addEventListener('change', function() {
                endDate.min = this.value;
            });

            endDate.addEventListener('change', function() {
                if (startDate.value && this.value < startDate.value) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fecha inválida',
                        text: 'La fecha final no puede ser anterior a la fecha inicial',
                        confirmButtonColor: '#d97706'
                    });
                    this.value = '';
                }
            });
        }
    });
</script>
