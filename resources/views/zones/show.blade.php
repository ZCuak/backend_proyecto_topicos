@extends('layouts.app')
@section('title', 'Detalles de Zona — RSU Reciclaje')

@section('content')
<div class="space-y-8">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">🗺️ {{ $zone->name }}</h1>
            <p class="text-slate-500">Detalles y información de la zona de recolección.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('zones.index') }}"
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
                        <p class="text-slate-800 font-medium">{{ $zone->name }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-slate-500">Distrito</label>
                        <p class="text-slate-800">{{ $zone->district->name ?? '—' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-slate-500">Sector</label>
                        <p class="text-slate-800">{{ $zone->sector->name ?? '—' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-slate-500">Área</label>
                        <p class="text-slate-800">
                            @if($zone->area)
                                {{ number_format($zone->area, 2) }} km²
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-slate-500">Coordenadas</label>
                        <p class="text-slate-800">{{ $zone->coordinates->count() }} puntos</p>
                    </div>
                    
                    @if($zone->description)
                    <div>
                        <label class="text-sm font-medium text-slate-500">Descripción</label>
                        <p class="text-slate-800">{{ $zone->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mapa --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-map text-emerald-600"></i>
                    Perímetro de la Zona
                </h3>
                
                <div id="map" class="w-full h-96 rounded-lg border border-slate-300"></div>
            </div>
        </div>
    </div>

    {{-- COORDENADAS DETALLADAS --}}
    @if($zone->coordinates->count() > 0)
    <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-list text-emerald-600"></i>
            Coordenadas del Perímetro
        </h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">#</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Latitud</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Longitud</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($zone->coordinates as $index => $coord)
                        <tr>
                            <td class="px-4 py-3 text-slate-600">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-slate-700 font-mono">{{ number_format($coord->latitude, 6) }}</td>
                            <td class="px-4 py-3 text-slate-700 font-mono">{{ number_format($coord->longitude, 6) }}</td>
                            <td class="px-4 py-3">
                                <button type="button" 
                                        onclick="centerOnPoint({{ $coord->latitude }}, {{ $coord->longitude }})"
                                        class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 text-xs">
                                    <i class="fa-solid fa-location-dot"></i> Centrar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

{{-- Scripts del mapa --}}
<script>
(function() {
    'use strict';
    
    function initZoneMap() {
        try {
            const mapContainer = document.getElementById('map');
            if (!mapContainer) return;

            // 🧹 Si ya hay un mapa Leaflet activo, destruirlo completamente
            if (mapContainer._leaflet_map_instance) {
                mapContainer._leaflet_map_instance.remove();
                mapContainer._leaflet_map_instance = null;
            }

            // Coordenadas de la zona
            const zoneCoords = @json($zone->coordinates->map(function($coord) {
                return [$coord->latitude, $coord->longitude];
            }));

            // 🗺️ Crear nuevo mapa limpio
            const map = L.map('map', { zoomControl: true, scrollWheelZoom: true, dragging: true })
                .setView([-6.7639, -79.8367], 13);

            // Guardar referencia del mapa en el DOM (para futuras limpiezas)
            mapContainer._leaflet_map_instance = map;

            // Cargar capa base
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Dibujar polígono si hay coordenadas
            if (zoneCoords.length > 0) {
                const polygon = L.polygon(zoneCoords, {
                    color: '#10b981',
                    fillColor: '#10b981',
                    fillOpacity: 0.3,
                    weight: 2
                }).addTo(map);

                // Centrar mapa en el polígono
                map.fitBounds(polygon.getBounds());
            }

            // 🧭 Asegurar controles activos
            map.scrollWheelZoom.enable();
            map.dragging.enable();

            // Centrar en un punto específico
            window.centerOnPoint = function(lat, lng) {
                if (map) {
                    map.setView([lat, lng], 16);
                }
            };

        } catch (e) {
            console.error('Error inicializando mapa de zona:', e);
        }
    }

    // Ejecutar tanto en DOMContentLoaded como en turbo:load
    document.addEventListener('DOMContentLoaded', initZoneMap);
    document.addEventListener('turbo:load', initZoneMap);
    
    // Ejecutar inmediatamente si el DOM ya está listo
    if (document.readyState === 'loading') {
        // DOM aún no está listo, esperar eventos
    } else {
        // DOM ya está listo, ejecutar inmediatamente
        initZoneMap();
    }
})();
</script>
@endsection