@extends('layouts.app')
@section('title', 'Detalles de Vehículo — RSU Reciclaje')

@section('content')
<div class="space-y-8">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">🚛 {{ $vehicle->name }}</h1>
            <p class="text-slate-500">Detalles e información del vehículo de recolección.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('vehicles.edit', $vehicle->id) }}"
               data-turbo-frame="modal-frame"
               class="flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
               <i class="fa-solid fa-pen"></i> Editar
            </a>
            <a href="{{ route('vehicles.index') }}"
               class="flex items-center gap-2 px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition">
               <i class="fa-solid fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- INFORMACIÓN GENERAL --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Datos básicos --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-info-circle text-emerald-600"></i>
                    Información General
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-500">Nombre</label>
                        <p class="text-slate-800 font-medium">{{ $vehicle->name }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-slate-500">Código</label>
                        <p class="text-slate-800 font-mono">{{ $vehicle->code }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-slate-500">Placa</label>
                        <p class="text-slate-800 font-mono text-lg font-bold">{{ $vehicle->plate }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-slate-500">Año</label>
                        <p class="text-slate-800">{{ $vehicle->year }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-slate-500">Estado</label>
                        @php
                            $statusColors = [
                                'DISPONIBLE' => 'bg-green-100 text-green-800',
                                'OCUPADO' => 'bg-yellow-100 text-yellow-800',
                                'MANTENIMIENTO' => 'bg-orange-100 text-orange-800',
                                'INACTIVO' => 'bg-red-100 text-red-800'
                            ];
                            $statusIcons = [
                                'DISPONIBLE' => 'fa-check-circle',
                                'OCUPADO' => 'fa-clock',
                                'MANTENIMIENTO' => 'fa-wrench',
                                'INACTIVO' => 'fa-times-circle'
                            ];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$vehicle->status] ?? 'bg-slate-100 text-slate-800' }}">
                            <i class="fa-solid {{ $statusIcons[$vehicle->status] ?? 'fa-question' }} mr-2"></i>
                            {{ $vehicle->status }}
                        </span>
                    </div>
                    
                    @if($vehicle->description)
                    <div>
                        <label class="text-sm font-medium text-slate-500">Descripción</label>
                        <p class="text-slate-800">{{ $vehicle->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Especificaciones técnicas --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-cogs text-emerald-600"></i>
                    Especificaciones Técnicas
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Marca y Modelo --}}
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-slate-500">Marca</label>
                            <p class="text-slate-800 font-medium">{{ $vehicle->brand->name ?? '—' }}</p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-slate-500">Modelo</label>
                            <p class="text-slate-800">{{ $vehicle->model->name ?? '—' }}</p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-slate-500">Tipo</label>
                            <p class="text-slate-800">{{ $vehicle->type->name ?? '—' }}</p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-slate-500">Color</label>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full bg-slate-300 border border-slate-400"></div>
                                <span class="text-slate-800">{{ $vehicle->color->name ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Capacidades --}}
                    <div class="space-y-4">
                        @if($vehicle->occupant_capacity)
                        <div>
                            <label class="text-sm font-medium text-slate-500">Capacidad de Pasajeros</label>
                            <p class="text-slate-800">{{ $vehicle->occupant_capacity }} personas</p>
                        </div>
                        @endif
                        
                        @if($vehicle->load_capacity)
                        <div>
                            <label class="text-sm font-medium text-slate-500">Capacidad de Carga</label>
                            <p class="text-slate-800">{{ number_format($vehicle->load_capacity) }} kg</p>
                        </div>
                        @endif
                        
                        @if($vehicle->compaction_capacity)
                        <div>
                            <label class="text-sm font-medium text-slate-500">Capacidad de Compactación</label>
                            <p class="text-slate-800">{{ number_format($vehicle->compaction_capacity) }} m³</p>
                        </div>
                        @endif
                        
                        @if($vehicle->fuel_capacity)
                        <div>
                            <label class="text-sm font-medium text-slate-500">Capacidad de Combustible</label>
                            <p class="text-slate-800">{{ number_format($vehicle->fuel_capacity) }} L</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- INFORMACIÓN ADICIONAL --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Fechas --}}
        <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-calendar text-emerald-600"></i>
                Fechas de Registro
            </h3>
            
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-slate-500">Creado</label>
                    <p class="text-slate-800">{{ $vehicle->created_at->format('d/m/Y H:i') }}</p>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-slate-500">Última actualización</label>
                    <p class="text-slate-800">{{ $vehicle->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Estadísticas --}}
        <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-bar text-emerald-600"></i>
                Estadísticas
            </h3>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Antigüedad</span>
                    <span class="text-slate-800 font-medium">{{ date('Y') - $vehicle->year }} años</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Estado del vehículo</span>
                    <span class="text-slate-800 font-medium">{{ $vehicle->status }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
