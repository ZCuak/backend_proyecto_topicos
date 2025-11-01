{{-- MODAL DE FILTROS AVANZADOS --}}
<div id="filtersModal"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden animate-[flyonui-fade-in_0.3s_ease-out]">

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
            <form method="GET" action="{{ route('history.index') }}" id="filtersForm">

                {{-- Mantener búsqueda activa --}}
                @if (request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif

                <div class="space-y-6">

                    {{-- Filtro por Módulo --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <i class="fa-solid fa-layer-group mr-1 text-emerald-600"></i>
                            Módulo
                        </label>
                        <select name="auditable_type"
                            class="w-full px-4 py-2.5 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Todos los módulos</option>
                            @foreach ($auditableTypes as $type)
                                <option value="{{ $type }}"
                                    {{ request('auditable_type') == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Rango de Fechas --}}
                    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50">
                        <legend class="px-3 text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-days text-emerald-600"></i>
                            Rango de Fechas
                        </legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Desde</label>
                                <input type="date" name="start_date" id="start_date"
                                    value="{{ request('start_date') }}"
                                    class="w-full px-4 py-2.5 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Hasta</label>
                                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                                    class="w-full px-4 py-2.5 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>
                    </fieldset>
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
    function openFiltersModal() {
        document.getElementById('filtersModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeFiltersModal() {
        document.getElementById('filtersModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeFiltersModal();
    });

    // Cerrar al hacer clic fuera
    document.getElementById('filtersModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeFiltersModal();
    });
</script>
