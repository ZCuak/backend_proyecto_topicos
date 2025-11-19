@extends('layouts.app')
@section('title', 'Días de Mantenimiento – RSU Reciclaje')

@section('content')
    <div class="space-y-8">

        {{-- ENCABEZADO --}}
        <div class="flex flex-col gap-4">
            {{-- Breadcrumb / Volver --}}
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('maintenances.index') }}" class="text-slate-500 hover:text-slate-700">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Mantenimientos
                </a>
                <span class="text-slate-400">/</span>
                <a href="{{ route('maintenance-schedules.index', ['maintenance_id' => $schedule->maintenance_id]) }}"
                    class="text-slate-500 hover:text-slate-700">
                    Horarios
                </a>
                <span class="text-slate-400">/</span>
                <span class="text-slate-700 font-medium">Días Generados</span>
            </div>

            {{-- Título con info del horario --}}
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6 border border-blue-100">
                <h1 class="text-2xl font-bold text-slate-800 mb-3">
                    📅 {{ $schedule->maintenance->name }} – {{ ucfirst(strtolower($schedule->day)) }} -
                    {{ $schedule->vehicle->plate }}
                </h1>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-truck text-blue-600"></i>
                        <span class="text-slate-600">Vehículo:</span>
                        <span class="font-medium text-slate-800">{{ $schedule->vehicle->name }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-user text-purple-600"></i>
                        <span class="text-slate-600">Responsable:</span>
                        <span class="font-medium text-slate-800">
                            {{ $schedule->responsible->firstname ?? 'N/A' }} {{ $schedule->responsible->lastname ?? '' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-wrench text-green-600"></i>
                        <span class="text-slate-600">Tipo:</span>
                        <span
                            class="px-2 py-1 rounded text-xs font-medium
                        {{ $schedule->type === 'PREVENTIVO' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $schedule->type === 'LIMPIEZA' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $schedule->type === 'REPARACIÓN' ? 'bg-orange-100 text-orange-700' : '' }}">
                            {{ $schedule->type }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-clock text-amber-600"></i>
                        <span class="text-slate-600">Horario:</span>
                        <span class="font-medium text-slate-800">
                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} -
                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ALERTAS --}}
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
            </div>
        @elseif(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <i class="fa-solid fa-circle-exclamation mr-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- TABLA DE DÍAS GENERADOS --}}
        <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-days text-blue-600"></i>
                    Días Programados ({{ $records->count() }})
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wider">Fecha
                            </th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700 uppercase tracking-wider">
                                Observación</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wider">Imagen
                            </th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wider">
                                <i class="fa-solid fa-pen"></i> EDIT
                            </th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700 uppercase tracking-wider">
                                <i class="fa-solid fa-check-circle"></i> EST
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($records as $record)
                            <tr class="hover:bg-slate-50 transition">
                                {{-- FECHA --}}
                                <td class="px-4 py-3 text-center font-medium text-slate-800">
                                    {{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}
                                </td>

                                {{-- OBSERVACIÓN --}}
                                <td class="px-4 py-3 text-slate-700">
                                    {{ $record->description ?? 'Sin observaciones' }}
                                </td>

                                {{-- IMAGEN --}}
                                <td class="px-4 py-3 text-center">
                                    @if ($record->image_path)
                                        <button type="button"
                                            onclick="showImage('{{ asset('storage/' . $record->image_path) }}')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition text-xs">
                                            <i class="fa-solid fa-image"></i> Ver
                                        </button>
                                    @else
                                        <span class="text-slate-400 text-xs">Sin imagen</span>
                                    @endif
                                </td>

                                {{-- EDITAR --}}
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('maintenance-records.edit', $record->id) }}"
                                        data-turbo-frame="modal-frame"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-yellow-100 text-yellow-700 hover:bg-yellow-200 transition"
                                        title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                </td>

                                {{-- ESTADO --}}
                                <td class="px-4 py-3 text-center">
                                    @if ($record->completed)
                                        <div class="flex items-center justify-center">
                                            <i class="fa-solid fa-check-circle text-2xl text-emerald-600"
                                                title="Realizado"></i>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center">
                                            <i class="fa-solid fa-times-circle text-2xl text-red-600"
                                                title="No realizado"></i>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8">
                                    <div class="flex flex-col items-center gap-2 text-slate-400">
                                        <i class="fa-solid fa-inbox text-4xl"></i>
                                        <p class="text-sm">No se generaron días para este horario</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL PARA VER IMAGEN --}}
    <div id="imageModal"
        class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b border-slate-200">
                <h3 class="font-bold text-lg text-slate-800">Imagen del Mantenimiento</h3>
                <button type="button" onclick="closeImageModal()" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-4">
                <img id="modalImage" src="" alt="Imagen de mantenimiento" class="w-full h-auto rounded-lg">
            </div>
        </div>
    </div>


    <script>
        function showImage(imagePath) {
            document.getElementById('modalImage').src = imagePath;
            document.getElementById('imageModal').classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }

        // Cerrar con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });

        // Cerrar al hacer clic fuera
        document.getElementById('imageModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
    </script>
@endsection
