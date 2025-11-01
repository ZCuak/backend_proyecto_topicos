{{-- 
    Este componente se incluye en cualquier vista de detalle para mostrar
    el historial de cambios de un registro específico.
    
    Variables requeridas:
    - $audits: Collection de cambios obtenida desde el controlador
    
    Ejemplo de uso en un controlador:
    
    public function show($id)
    {
        $attendance = Attendace::findOrFail($id);
        $audits = HistoryController::getHistory('ASISTENCIA DE PERSONAL', $id);
        return view('attendances.show', compact('attendance', 'audits'));
    }
    
    Ejemplo de uso en una vista:
    
    @include('audits._history_table', ['audits' => $audits])
--}}

<div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">

    {{-- ========================================
         HEADER
    ======================================== --}}
    <div
        class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                <i class="fa-solid fa-clock-rotate-left text-blue-600 text-lg"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800">Historial de Cambios</h3>
                <p class="text-sm text-slate-500">
                    Registro de todas las modificaciones realizadas
                </p>
            </div>
        </div>

        {{-- Badge con cantidad de cambios --}}
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                {{ $audits->count() }} {{ $audits->count() === 1 ? 'cambio' : 'cambios' }}
            </span>
        </div>
    </div>

    {{-- ========================================
         BODY CON TABLA
    ======================================== --}}
    <div class="p-6">
        @if ($audits->isEmpty())
            {{-- ========================================
                 ESTADO VACÍO
            ======================================== --}}
            <div class="text-center py-12 text-slate-400">
                <i class="fa-solid fa-history text-5xl mb-3 text-slate-300"></i>
                <p class="text-lg font-medium">No hay cambios registrados</p>
                <p class="text-sm">Este registro aún no ha sido modificado</p>
            </div>
        @else
            {{-- ========================================
                 TABLA DE CAMBIOS
            ======================================== --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider text-xs">
                                Fecha y Hora
                            </th>
                            <th
                                class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider text-xs">
                                Campo
                            </th>
                            <th
                                class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider text-xs">
                                Valor Anterior
                            </th>
                            <th
                                class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider text-xs">
                                Valor Nuevo
                            </th>
                            <th
                                class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider text-xs">
                                Usuario
                            </th>
                            <th
                                class="px-4 py-3 text-center font-semibold text-slate-600 uppercase tracking-wider text-xs">
                                Nota
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($audits as $audit)
                            <tr class="hover:bg-slate-50 transition">
                                {{-- ========================================
                                     FECHA Y HORA
                                ======================================== --}}
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-xs">
                                            {{ $audit->created_at->format('d/m/Y') }}
                                        </span>
                                        <span class="text-xs text-slate-400">
                                            {{ $audit->created_at->format('H:i:s') }}
                                        </span>
                                    </div>
                                </td>

                                {{-- ========================================
                                     CAMPO MODIFICADO
                                ======================================== --}}
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-medium">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        {{ $audit->campo_modificado }}
                                    </span>
                                </td>

                                {{-- ========================================
                                     VALOR ANTERIOR
                                ======================================== --}}
                                <td class="px-4 py-3">
                                    <div class="max-w-xs">
                                        @if ($audit->campo_modificado === 'registro_eliminado')
                                            <span class="text-slate-400 italic text-xs">
                                                (Registro completo)
                                            </span>
                                        @else
                                            <div class="bg-red-50 border border-red-200 rounded-md px-3 py-2">
                                                <p class="text-xs text-red-700 font-mono break-words"
                                                    title="{{ $audit->valor_anterior }}">
                                                    {{ Str::limit($audit->valor_anterior ?? '—', 50) }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- ========================================
                                     VALOR NUEVO
                                ======================================== --}}
                                <td class="px-4 py-3">
                                    <div class="max-w-xs">
                                        @if ($audit->valor_nuevo === 'ELIMINADO')
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                                <i class="fa-solid fa-trash"></i>
                                                ELIMINADO
                                            </span>
                                        @else
                                            <div class="bg-green-50 border border-green-200 rounded-md px-3 py-2">
                                                <p class="text-xs text-green-700 font-mono break-words"
                                                    title="{{ $audit->valor_nuevo }}">
                                                    {{ Str::limit($audit->valor_nuevo ?? '—', 50) }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- ========================================
                                     USUARIO
                                ======================================== --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                            <i class="fa-solid fa-user text-emerald-600 text-xs"></i>
                                        </div>
                                        <span class="text-slate-600 text-xs font-medium">
                                            {{ $audit->user_name }}
                                        </span>
                                    </div>
                                </td>

                                {{-- ========================================
                                     NOTA ADICIONAL
                                ======================================== --}}
                                <td class="px-4 py-3 text-center">
                                    @if ($audit->nota_adicional)
                                        <button type="button" onclick="showAuditNote{{ $audit->id }}()"
                                            class="inline-flex items-center gap-1 px-2 py-1 bg-amber-50 text-amber-700 rounded-md hover:bg-amber-100 transition text-xs">
                                            <i class="fa-solid fa-note-sticky"></i>
                                            Ver nota
                                        </button>

                                        {{-- Script único para cada nota --}}
                                        <script>
                                            function showAuditNote{{ $audit->id }}() {
                                                Swal.fire({
                                                    title: '📝 Nota del Cambio',
                                                    html: `
                                                    <div class="text-left space-y-3">
                                                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                                                            <p class="text-xs text-slate-500 mb-1">Fecha del cambio:</p>
                                                            <p class="text-sm font-semibold text-slate-700">{{ $audit->created_at->format('d/m/Y H:i:s') }}</p>
                                                        </div>
                                                        <div class="p-4 bg-amber-50 rounded-lg border border-amber-200">
                                                            <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $audit->nota_adicional }}</p>
                                                        </div>
                                                    </div>
                                                `,
                                                    icon: 'info',
                                                    confirmButtonText: 'Cerrar',
                                                    confirmButtonColor: '#10b981',
                                                    width: '500px'
                                                });
                                            }
                                        </script>
                                    @else
                                        <span class="text-slate-400 text-xs italic">Sin nota</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ========================================
                 INFO ADICIONAL
            ======================================== --}}
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-info-circle text-blue-600 mt-0.5"></i>
                    <div class="text-xs text-blue-700">
                        <p class="font-semibold mb-2">📊 Resumen del historial:</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="bg-white rounded-lg p-2 border border-blue-200">
                                <p class="text-slate-500 text-xs">Primer cambio</p>
                                <p class="font-semibold text-slate-700">
                                    {{ $audits->last()->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="bg-white rounded-lg p-2 border border-blue-200">
                                <p class="text-slate-500 text-xs">Último cambio</p>
                                <p class="font-semibold text-slate-700">
                                    {{ $audits->first()->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="bg-white rounded-lg p-2 border border-blue-200">
                                <p class="text-slate-500 text-xs">Total de cambios</p>
                                <p class="font-semibold text-slate-700">{{ $audits->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
