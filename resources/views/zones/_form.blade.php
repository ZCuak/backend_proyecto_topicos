<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- ===========================
         📍 DATOS DE LA ZONA
    ============================ --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50/60 hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-map-location-dot text-emerald-600"></i> Datos de la zona
        </legend>

        {{-- Nombre de la zona --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre de la zona <span class="text-red-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-tag absolute left-3 top-2.5 text-slate-400"></i>
                <input type="text" name="name" maxlength="100"
                    value="{{ old('name', $zone->name ?? '') }}"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Ej. Zona Norte - Centro Histórico">
            </div>
        </div>

        {{-- Distrito y Sector --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Distrito <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i class="fa-solid fa-building absolute left-3 top-2.5 text-slate-400"></i>
                    <select name="district_id" id="district_id"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Seleccionar distrito...</option>
                        @foreach($districts as $district)
                            <option value="{{ $district->id }}"
                                {{ old('district_id', $zone->district_id ?? '') == $district->id ? 'selected' : '' }}>
                                {{ $district->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Sector <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i class="fa-solid fa-layer-group absolute left-3 top-2.5 text-slate-400"></i>
                    <select name="sector_id" id="sector_id"
                            class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Seleccionar sector...</option>
                        @if(isset($zone) && ($zone->sector_id || $zone->district_id))
                            @foreach($sectors as $sector)
                                <option value="{{ $sector->id }}"
                                    {{ old('sector_id', $zone->sector_id) == $sector->id ? 'selected' : '' }}>
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>

        {{-- Área estimada --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Área estimada (m²)</label>
            <div class="relative">
                <i class="fa-solid fa-ruler-combined absolute left-3 top-2.5 text-slate-400"></i>
                <input type="number" name="area" step="1" min="0"
                    value="{{ old('area', $zone->area ?? '') }}"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Ej. 2500000"
                    onchange="updateAreaDisplay(this.value)">
            </div>
            <div id="areaConversion" class="mt-1 text-xs text-slate-500 hidden">
                <!-- Conversiones se mostrarán aquí -->
            </div>
        </div>

        {{-- Descripción --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
            <textarea name="description" rows="3"
                class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                placeholder="Descripción general de la zona...">{{ old('description', $zone->description ?? '') }}</textarea>
        </div>
    </fieldset>

    {{-- ===========================
         🗺️ MAPA INTERACTIVO
    ============================ --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-white hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-map text-emerald-600"></i> Perímetro de la zona
        </legend>

        {{-- Controles del mapa --}}
        <div class="flex flex-wrap gap-2 mb-4">
            <button type="button" id="startDrawing" 
                    class="px-3 py-1 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm">
                <i class="fa-solid fa-pen mr-1"></i> Dibujar Polígono
            </button>
            <button type="button" id="clearPolygon" 
                    class="px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                <i class="fa-solid fa-trash mr-1"></i> Limpiar
            </button>
        </div>

        {{-- Mapa siguiendo la guía oficial --}}
        <div id="map" style="height: 320px;" class="w-full rounded-lg border border-slate-300"></div>
        
        {{-- Tabla de coordenadas mejorada --}}
        <div id="coordinatesTable" class="mt-4 hidden">
            <div class="bg-gradient-to-r from-slate-50 to-slate-100 rounded-xl border border-slate-200 shadow-sm">
                <div class="p-4 border-b border-slate-200">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-table text-emerald-600"></i>
                            <h4 class="text-sm font-semibold text-slate-700">Coordenadas del Polígono</h4>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                <i class="fa-solid fa-map-pin mr-1"></i>
                                <span id="pointCount">0</span> puntos
                            </span>
                            <button type="button" id="addCoordinate" 
                                class="px-3 py-1 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-xs transition">
                                <i class="fa-solid fa-plus mr-1"></i> Agregar
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50">
                                    <th class="text-left py-3 px-3 font-semibold text-slate-600">#</th>
                                    <th class="text-left py-3 px-3 font-semibold text-slate-600">Latitud</th>
                                    <th class="text-left py-3 px-3 font-semibold text-slate-600">Longitud</th>
                                    <th class="text-center py-3 px-3 font-semibold text-slate-600">Estado</th>
                                    <th class="text-center py-3 px-3 font-semibold text-slate-600">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="coordinatesList" class="divide-y divide-slate-200">
                                <!-- Las filas se agregarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Mensaje cuando no hay coordenadas --}}
                    <div id="emptyCoordinates" class="text-center py-8 text-slate-500">
                        <i class="fa-solid fa-map-location-dot text-4xl mb-3 text-slate-300"></i>
                        <p class="text-sm">No hay coordenadas dibujadas</p>
                        <p class="text-xs text-slate-400 mt-1">Haz clic en "Dibujar Polígono" para comenzar</p>
                    </div>
                </div>
            </div>
        </div>


        {{-- Campo oculto para coordenadas --}}
        @php
            $existingCoords = '';
            if (isset($zone) && $zone->coordinates) {
                $existingCoords = json_encode($zone->coordinates->map(function($c) { 
                    return ['latitude' => $c->latitude, 'longitude' => $c->longitude]; 
                }));
            }
        @endphp
        <input type="hidden" name="coords" id="coords" value="{{ old('coords', $existingCoords) }}">
    </fieldset>
</div>

{{-- BOTONES --}}
@php
    $modalId = isset($zone) && isset($zone->id) ? 'editModal' : 'createModal';
@endphp
<div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
    <button type="button"
        onclick="FlyonUI.modal.close('{{ $modalId }}')"
        class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark mr-1"></i> Cancelar
    </button>
    <button type="submit"
        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
        <i class="fa-solid fa-save"></i> {{ $buttonText }}
    </button>
</div>

{{-- Estilos para marcadores de puntos --}}
<style>
.custom-marker {
    background: transparent !important;
    border: none !important;
}

.marker-point {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
}

.marker-number {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #10b981;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
    z-index: 1000;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.marker-icon {
    font-size: 20px;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}
</style>

{{-- Scripts del mapa siguiendo la guía oficial --}}
<script>
// Evitar ejecución múltiple del script
if (window.zoneMapInitialized) {
    console.log('Script del mapa ya inicializado, pero cargando coordenadas...');
    
    // Cargar coordenadas existentes incluso si el script ya se ejecutó
    setTimeout(() => {
        // Reinicializar el mapa si es necesario
        if (typeof map === 'undefined' || !map) {
            console.log('Reinicializando mapa para edición...');
            reinitMap();
        }
        
        loadExistingCoordinates();
        showCoordinatesTableInEdit();
    }, 100);
} else {
    window.zoneMapInitialized = true;

// Variables globales - usar var para evitar redeclaración
var map;
var polygon;
var coordinates = [];
var isDrawing = false;

// Coordenadas por defecto (Chiclayo, Perú)
const defaultLat = -6.7639;
const defaultLng = -79.8367;
const defaultZoom = 13;

// Inicializar mapa siguiendo EXACTAMENTE la guía oficial
function initMap() {
    // Paso 1: Crear el mapa (como dice la guía)
    map = L.map('map').setView([defaultLat, defaultLng], defaultZoom);
    
    // Paso 2: Agregar tiles (copiado exactamente de la guía)
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    console.log('Mapa inicializado siguiendo la guía oficial de Leaflet');
}

// Dibujar polígono siguiendo la guía oficial
function drawPolygon() {
    if (coordinates.length < 3) {
        console.log('No hay suficientes puntos para dibujar polígono');
        return;
    }

    console.log('Dibujando polígono con', coordinates.length, 'puntos');

    // Limpiar polígono anterior
    if (typeof polygon !== 'undefined' && polygon) {
        if (typeof map !== 'undefined' && map) {
            map.removeLayer(polygon);
            console.log('Polígono anterior removido');
        }
    }

    // Crear polígono (como dice la guía oficial)
    const latLngs = coordinates.map(coord => [coord.latitude, coord.longitude]);
    polygon = L.polygon(latLngs, {
        color: '#10b981',
        fillColor: '#10b981',
        fillOpacity: 0.3,
        weight: 2
    }).addTo(map);

    console.log('Nuevo polígono creado y agregado al mapa');

    // Centrar mapa en el polígono
    if (latLngs.length > 0) {
        map.fitBounds(polygon.getBounds());
    }

    updatePolygonInfo();
    updateCoordinatesTable();
    
    // Calcular área automáticamente si hay suficientes puntos
    if (coordinates.length >= 3) {
        calculatePolygonArea();
    }
    
    // Dibujar marcadores de puntos
    drawPointMarkers();
}

// Función para dibujar marcadores de puntos
function drawPointMarkers() {
    // Limpiar marcadores anteriores
    clearPointMarkers();
    
    // Crear marcadores para cada punto
    coordinates.forEach((coord, index) => {
        const marker = L.marker([coord.latitude, coord.longitude], {
            icon: L.divIcon({
                className: 'custom-marker',
                html: `<div class="marker-point">
                    <div class="marker-number">${index + 1}</div>
                    <div class="marker-icon">📍</div>
                </div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            })
        }).addTo(map);
        
        // Agregar popup con información
        marker.bindPopup(`
            <div class="text-center">
                <strong>Punto ${index + 1}</strong><br>
                <small>Lat: ${parseFloat(coord.latitude).toFixed(6)}</small><br>
                <small>Lng: ${parseFloat(coord.longitude).toFixed(6)}</small>
            </div>
        `);
        
        // Guardar referencia del marcador
        if (!window.pointMarkers) {
            window.pointMarkers = [];
        }
        window.pointMarkers.push(marker);
    });
    
    console.log('Marcadores de puntos dibujados:', coordinates.length);
}

// Función para limpiar marcadores de puntos
function clearPointMarkers() {
    if (window.pointMarkers) {
        window.pointMarkers.forEach(marker => {
            if (typeof map !== 'undefined' && map) {
                map.removeLayer(marker);
            }
        });
        window.pointMarkers = [];
    }
}

// Actualizar tabla de coordenadas mejorada
function updateCoordinatesTable() {
    console.log('updateCoordinatesTable ejecutándose, coordenadas:', coordinates.length);
    
    const coordinatesTable = document.getElementById('coordinatesTable');
    const coordinatesList = document.getElementById('coordinatesList');
    const pointCount = document.getElementById('pointCount');
    const emptyCoordinates = document.getElementById('emptyCoordinates');
    
    console.log('Elementos encontrados:', {
        coordinatesTable: !!coordinatesTable,
        coordinatesList: !!coordinatesList,
        pointCount: !!pointCount,
        emptyCoordinates: !!emptyCoordinates
    });
    
    if (coordinates.length > 0) {
        console.log('Mostrando tabla con', coordinates.length, 'coordenadas');
        
        // Mostrar tabla
        if (coordinatesTable) {
            coordinatesTable.classList.remove('hidden');
            coordinatesTable.style.display = 'block';
            console.log('Tabla mostrada');
        }
        
        // Ocultar mensaje vacío
        if (emptyCoordinates) {
            emptyCoordinates.style.display = 'none';
        }
        
        // Actualizar contador
        if (pointCount) {
            pointCount.textContent = coordinates.length;
        }
        
        // Limpiar tabla
        if (coordinatesList) {
            coordinatesList.innerHTML = '';
        }
        
        // Agregar filas con mejor diseño
        coordinates.forEach((coord, index) => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-slate-50 transition-colors';
            row.innerHTML = `
                <td class="py-3 px-3">
                    <span class="inline-flex items-center justify-center w-6 h-6 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-full">
                        ${index + 1}
                    </span>
                </td>
                <td class="py-3 px-3">
                    <div class="flex items-center gap-2">
                        <input type="number" 
                            value="${parseFloat(coord.latitude).toFixed(6)}" 
                            step="0.000001"
                            onchange="updateCoordinate(${index}, 'latitude', this.value)"
                            class="w-full px-2 py-1 text-xs border border-slate-300 rounded focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                        <span class="text-xs text-slate-500">°N</span>
                    </div>
                </td>
                <td class="py-3 px-3">
                    <div class="flex items-center gap-2">
                        <input type="number" 
                            value="${parseFloat(coord.longitude).toFixed(6)}" 
                            step="0.000001"
                            onchange="updateCoordinate(${index}, 'longitude', this.value)"
                            class="w-full px-2 py-1 text-xs border border-slate-300 rounded focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                        <span class="text-xs text-slate-500">°W</span>
                    </div>
                </td>
                <td class="py-3 px-3 text-center">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fa-solid fa-check-circle mr-1"></i>
                        Válida
                    </span>
                </td>
                <td class="py-3 px-3 text-center">
                    <div class="flex justify-center gap-1">
                        <button type="button" onclick="centerOnCoordinate(${index})" 
                            class="p-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition"
                            title="Centrar en mapa">
                            <i class="fa-solid fa-crosshairs text-xs"></i>
                        </button>
                        <button type="button" onclick="removeCoordinate(${index})" 
                            class="p-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition"
                            title="Eliminar">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </td>
            `;
            if (coordinatesList) {
                coordinatesList.appendChild(row);
            }
        });
    } else {
        // Ocultar tabla
        if (coordinatesTable) {
            coordinatesTable.classList.add('hidden');
        }
        
        // Mostrar mensaje vacío
        if (emptyCoordinates) {
            emptyCoordinates.style.display = 'block';
        }
    }
}

// Actualizar coordenada específica
function updateCoordinate(index, field, value) {
    if (index >= 0 && index < coordinates.length) {
        const numValue = parseFloat(value);
        if (!isNaN(numValue)) {
            coordinates[index][field] = numValue;
            drawPolygon();
            saveCoordinates();
            console.log(`Coordenada ${index + 1} actualizada: ${field} = ${value}`);
        }
    }
}

// Centrar mapa en coordenada específica
function centerOnCoordinate(index) {
    if (index >= 0 && index < coordinates.length && map) {
        const coord = coordinates[index];
        map.setView([coord.latitude, coord.longitude], 16);
        
        // Crear marcador temporal
        const marker = L.marker([coord.latitude, coord.longitude])
            .addTo(map)
            .bindPopup(`Punto ${index + 1}<br>Lat: ${parseFloat(coord.latitude).toFixed(6)}<br>Lng: ${parseFloat(coord.longitude).toFixed(6)}`)
            .openPopup();
        
        // Remover marcador después de 3 segundos
        setTimeout(() => {
            map.removeLayer(marker);
        }, 3000);
        
        console.log(`Centrado en coordenada ${index + 1}`);
    }
}

// Remover coordenada específica
function removeCoordinate(index) {
    if (confirm(`¿Estás seguro de eliminar el punto ${index + 1}?`)) {
        coordinates.splice(index, 1);
        console.log(`Coordenada ${index + 1} eliminada, total restante: ${coordinates.length}`);
        
        // Actualizar tabla
        updateCoordinatesTable();
        
        // Redibujar polígono si hay suficientes puntos
        if (coordinates.length >= 3) {
            drawPolygon();
        } else {
            // Limpiar polígono si no hay suficientes puntos
            if (typeof polygon !== 'undefined' && polygon) {
                if (typeof map !== 'undefined' && map) {
                    map.removeLayer(polygon);
                }
                polygon = null;
            }
        }
        
        // Actualizar marcadores de puntos
        drawPointMarkers();
        
        // Guardar coordenadas
        saveCoordinates();
        
        console.log('Mapa actualizado después de eliminar coordenada');
    }
}

// Actualizar información del polígono (simplificado)
function updatePolygonInfo() {
    // Función simplificada - el área se calcula automáticamente
    // y se guarda en el campo de entrada
}

// Guardar coordenadas
function saveCoordinates() {
    const coordsInput = document.getElementById('coords');
    if (coordsInput) {
        coordsInput.value = JSON.stringify(coordinates);
    }
}

// Filtro de sectores por distrito
document.getElementById('district_id').addEventListener('change', function() {
    const districtId = this.value;
    const sectorSelect = document.getElementById('sector_id');
    
    // Limpiar opciones
    sectorSelect.innerHTML = '<option value="">Seleccionar sector...</option>';
    
    if (districtId) {
        fetch(`/zones/sectors/${districtId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(sector => {
                        const option = document.createElement('option');
                        option.value = sector.id;
                        option.textContent = sector.name;
                        sectorSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading sectors:', error));
    }
});

// Función para reinicializar el mapa
function reinitMap() {
    // Limpiar mapa anterior si existe
    if (typeof map !== 'undefined' && map) {
        map.remove();
    }
    
    // Reinicializar
    initMap();
    
    // Configurar eventos siguiendo EXACTAMENTE la guía oficial
    var popup = L.popup();

    function onMapClick(e) {
        if (isDrawing) {
            coordinates.push({
                latitude: e.latlng.lat,
                longitude: e.latlng.lng
            });
            
            console.log('Nueva coordenada agregada, total:', coordinates.length);
            drawPolygon();
            saveCoordinates();
            updateCoordinatesTable();
            
            // Dibujar marcadores de puntos
            drawPointMarkers();
            
            // Forzar visualización de la tabla
            const coordinatesTable = document.getElementById('coordinatesTable');
            if (coordinatesTable) {
                coordinatesTable.classList.remove('hidden');
                coordinatesTable.style.display = 'block';
                console.log('Tabla forzada a mostrarse');
            }
            
            // Mostrar popup (como en la guía oficial)
            popup
                .setLatLng(e.latlng)
                .setContent("Punto agregado: " + e.latlng.toString())
                .openOn(map);
        }
    }

    map.on('click', onMapClick);
    
    console.log('Mapa reinicializado con eventos');
}

// Cargar coordenadas existentes si estamos editando
function loadExistingCoordinates() {
    console.log('loadExistingCoordinates ejecutándose');
    const coordsInput = document.getElementById('coords');
    console.log('Campo coords encontrado:', !!coordsInput);
    console.log('Valor del campo coords:', coordsInput ? coordsInput.value : 'No encontrado');
    
    if (coordsInput && coordsInput.value) {
        try {
            const existingCoords = JSON.parse(coordsInput.value);
            console.log('Coordenadas parseadas:', existingCoords);
            
            if (Array.isArray(existingCoords) && existingCoords.length > 0) {
                // Convertir coordenadas a números si vienen como strings
                coordinates = existingCoords.map(coord => ({
                    latitude: parseFloat(coord.latitude),
                    longitude: parseFloat(coord.longitude)
                }));
                console.log('Coordenadas asignadas al array global:', coordinates.length);
                
                // Forzar visualización de la tabla inmediatamente
                const coordinatesTable = document.getElementById('coordinatesTable');
                if (coordinatesTable) {
                    coordinatesTable.classList.remove('hidden');
                    coordinatesTable.style.display = 'block';
                    console.log('Tabla forzada a mostrarse en edición');
                }
                
                // Mostrar la tabla de coordenadas
                updateCoordinatesTable();
                
                // Dibujar el polígono
                drawPolygon();
                
                // Dibujar marcadores de puntos
                drawPointMarkers();
                
                console.log('Coordenadas existentes cargadas:', coordinates.length);
                
                // Centrar el mapa en las coordenadas existentes
                if (coordinates.length > 0 && typeof map !== 'undefined' && map) {
                    const bounds = L.latLngBounds(coordinates.map(coord => [coord.latitude, coord.longitude]));
                    map.fitBounds(bounds);
                }
            } else {
                console.log('No hay coordenadas válidas para cargar');
            }
        } catch (e) {
            console.error('Error al cargar coordenadas existentes:', e);
        }
    } else {
        console.log('No hay campo coords o está vacío');
    }
}

// Función para mostrar tabla de coordenadas en edición
function showCoordinatesTableInEdit() {
    const coordinatesTable = document.getElementById('coordinatesTable');
    const coordsInput = document.getElementById('coords');
    
    // Verificar si hay coordenadas existentes
    if (coordsInput && coordsInput.value) {
        try {
            const existingCoords = JSON.parse(coordsInput.value);
            if (Array.isArray(existingCoords) && existingCoords.length > 0) {
                // Mostrar tabla inmediatamente
                if (coordinatesTable) {
                    coordinatesTable.classList.remove('hidden');
                    coordinatesTable.style.display = 'block';
                    console.log('Tabla mostrada para edición con coordenadas existentes');
                }
            }
        } catch (e) {
            console.log('No se pudieron parsear las coordenadas existentes');
        }
    }
}

// Función para cargar sectores de un distrito específico
function loadSectorsForDistrict(districtId) {
    const sectorSelect = document.getElementById('sector_id');
    
    // Limpiar opciones
    sectorSelect.innerHTML = '<option value="">Seleccionar sector...</option>';
    
    if (districtId) {
        fetch(`/zones/sectors/${districtId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(sector => {
                        const option = document.createElement('option');
                        option.value = sector.id;
                        option.textContent = sector.name;
                        sectorSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading sectors:', error));
    }
}

// Inicializar el mapa inmediatamente
reinitMap();

// Cargar sectores si hay un distrito preseleccionado
setTimeout(() => {
    const districtSelect = document.getElementById('district_id');
    if (districtSelect && districtSelect.value) {
        loadSectorsForDistrict(districtSelect.value);
    }
    
    // Cargar coordenadas existentes (para edición)
    loadExistingCoordinates();
    
    // Mostrar tabla si estamos en modo edición
    showCoordinatesTableInEdit();
    
    // Asegurar que la tabla se muestre después de cargar coordenadas
    setTimeout(() => {
        if (coordinates.length > 0) {
            updateCoordinatesTable();
            
            // Forzar visualización adicional
            const coordinatesTable = document.getElementById('coordinatesTable');
            if (coordinatesTable) {
                coordinatesTable.classList.remove('hidden');
                coordinatesTable.style.display = 'block';
                console.log('Tabla forzada a mostrarse en timeout adicional');
            }
        }
    }, 200);
}, 100);

// Observar cambios en el DOM para reinicializar cuando se abra el modal
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'childList') {
            const mapDiv = document.getElementById('map');
            if (mapDiv && !mapDiv.hasChildNodes()) {
                // El div del mapa está vacío, reinicializar
                setTimeout(() => {
                    reinitMap();
                    
                    // Cargar coordenadas si estamos en modo edición
                    setTimeout(() => {
                        loadExistingCoordinates();
                        showCoordinatesTableInEdit();
                    }, 200);
                }, 100);
            }
        }
    });
});

// Observar el contenedor del modal
const modalContainer = document.querySelector('.flyonui-body');
if (modalContainer) {
    observer.observe(modalContainer, { childList: true, subtree: true });
}

// Función para verificar y reinicializar el mapa
function checkAndReinitMap() {
    const mapDiv = document.getElementById('map');
    if (mapDiv) {
        // Verificar si el mapa está vacío o no se ha inicializado
        if (!mapDiv.hasChildNodes() || !map) {
            console.log('Reinicializando mapa...');
            reinitMap();
        }
    }
}

// Verificar cada vez que se haga clic en el modal
document.addEventListener('click', function(e) {
    if (e.target.closest('[data-turbo-frame="modal-frame"]')) {
        setTimeout(() => {
            checkAndReinitMap();
            
            // Cargar coordenadas si estamos en modo edición
            setTimeout(() => {
                loadExistingCoordinates();
                showCoordinatesTableInEdit();
            }, 300);
        }, 200);
    }
});

// Verificar cuando se carga el contenido del modal
document.addEventListener('turbo:frame-load', function(e) {
    if (e.target.id === 'modal-frame') {
        setTimeout(() => {
            checkAndReinitMap();
            
            // Cargar coordenadas si estamos en modo edición
            setTimeout(() => {
                loadExistingCoordinates();
                showCoordinatesTableInEdit();
            }, 300);
        }, 200);
    }
});

// Limpiar estado cuando se cierra el modal
document.addEventListener('turbo:before-cache', function(e) {
    if (e.target.id === 'modal-frame') {
        // Limpiar coordenadas
        coordinates = [];
        if (typeof polygon !== 'undefined' && polygon) {
            if (typeof map !== 'undefined' && map) {
                map.removeLayer(polygon);
            }
            polygon = null;
        }
        if (typeof map !== 'undefined' && map) {
            map.remove();
            map = null;
        }
        isDrawing = false;
    }
});

// Limpiar estado cuando se descarga el frame
document.addEventListener('turbo:before-frame-render', function(e) {
    if (e.target.id === 'modal-frame') {
        // Limpiar coordenadas
        coordinates = [];
        if (typeof polygon !== 'undefined' && polygon) {
            if (typeof map !== 'undefined' && map) {
                map.removeLayer(polygon);
            }
            polygon = null;
        }
        if (typeof map !== 'undefined' && map) {
            map.remove();
            map = null;
        }
        isDrawing = false;
    }
});

// Función para agregar coordenada manualmente
function addCoordinateManually() {
    const lat = prompt('Ingresa la latitud:');
    const lng = prompt('Ingresa la longitud:');
    
    if (lat && lng) {
        const latitude = parseFloat(lat);
        const longitude = parseFloat(lng);
        
        if (!isNaN(latitude) && !isNaN(longitude)) {
            // Validar rangos de coordenadas
            if (latitude >= -90 && latitude <= 90 && longitude >= -180 && longitude <= 180) {
                coordinates.push({
                    latitude: latitude,
                    longitude: longitude
                });
                
                drawPolygon();
                updateCoordinatesTable();
                saveCoordinates();
                
                // Centrar en la nueva coordenada
                if (map) {
                    map.setView([latitude, longitude], 16);
                }
                
                console.log('Coordenada agregada manualmente');
            } else {
                alert('Coordenadas inválidas. Latitud debe estar entre -90 y 90, Longitud entre -180 y 180');
            }
        } else {
            alert('Por favor ingresa valores numéricos válidos');
        }
    }
}

// Eventos de botones - versión simplificada
document.addEventListener('click', function(e) {
    // Botón de dibujar/parar
    if (e.target.closest('#startDrawing')) {
        if (isDrawing) {
            // Parar dibujo
            isDrawing = false;
            e.target.classList.remove('bg-emerald-700');
            e.target.innerHTML = '<i class="fa-solid fa-pen mr-1"></i> Dibujar Polígono';
            if (map) {
                map.getContainer().style.cursor = 'default';
            }
            console.log('Modo dibujo desactivado');
        } else {
            // Iniciar dibujo
            isDrawing = true;
            coordinates = [];
            e.target.classList.add('bg-emerald-700');
            e.target.innerHTML = '<i class="fa-solid fa-stop mr-1"></i> Parar';
            if (map) {
                map.getContainer().style.cursor = 'crosshair';
            }
            
            // Mostrar tabla de coordenadas inmediatamente
            const coordinatesTable = document.getElementById('coordinatesTable');
            if (coordinatesTable) {
                coordinatesTable.classList.remove('hidden');
                coordinatesTable.style.display = 'block';
                console.log('Tabla mostrada al iniciar dibujo');
            }
            
            console.log('Modo dibujo activado');
        }
    }
    
    // Botón de agregar coordenada manualmente
    if (e.target.closest('#addCoordinate')) {
        addCoordinateManually();
    }
    
    // Botón de limpiar
    if (e.target.closest('#clearPolygon')) {
        if (coordinates.length > 0 && confirm('¿Estás seguro de limpiar todas las coordenadas?')) {
            coordinates = [];
            if (polygon) {
                map.removeLayer(polygon);
                polygon = null;
            }
            const coordsInput = document.getElementById('coords');
            if (coordsInput) {
                coordsInput.value = '';
            }
            updatePolygonInfo();
            updateCoordinatesTable();
            
            const drawBtn = document.getElementById('startDrawing');
            if (drawBtn) {
                drawBtn.classList.remove('bg-emerald-700');
                drawBtn.innerHTML = '<i class="fa-solid fa-pen mr-1"></i> Dibujar Polígono';
            }
            isDrawing = false;
            if (map) {
                map.getContainer().style.cursor = 'default';
            }
            console.log('Polígono limpiado');
        }
    }
    
});

// Función para calcular el área del polígono usando la fórmula de Shoelace esférica
function calculatePolygonArea() {
    if (coordinates.length < 3) {
        showNotification('Necesitas al menos 3 puntos para calcular el área', 'warning');
        return;
    }
    
    // Validar coordenadas antes de calcular
    if (!validateCoordinates()) {
        return;
    }
    
    // Convertir coordenadas a radianes
    const coordsRad = coordinates.map(coord => ({
        lat: coord.latitude * Math.PI / 180,
        lng: coord.longitude * Math.PI / 180
    }));
    
    // Radio de la Tierra en metros
    const earthRadius = 6371000; // metros
    
    // Fórmula de Shoelace esférica para calcular el área
    let area = 0;
    const n = coordsRad.length;
    
    for (let i = 0; i < n; i++) {
        const j = (i + 1) % n;
        area += (coordsRad[j].lng - coordsRad[i].lng) * (2 + Math.sin(coordsRad[i].lat) + Math.sin(coordsRad[j].lat));
    }
    
    area = Math.abs(area) * earthRadius * earthRadius / 2;
    
    // Convertir a diferentes unidades
    const areaM2 = Math.round(area); // metros cuadrados
    const areaKm2 = (area / 1000000).toFixed(6); // kilómetros cuadrados
    const areaHectares = (area / 10000).toFixed(2); // hectáreas
    
    // Actualizar el campo de área
    const areaInput = document.querySelector('input[name="area"]');
    
    if (areaInput) {
        areaInput.value = areaM2;
        // Actualizar la visualización de conversiones
        updateAreaDisplay(areaM2);
    }
    
    console.log(`Área calculada: ${areaM2.toLocaleString()} m² (${areaKm2} km², ${areaHectares} ha)`);
    
    // Mostrar notificación de éxito
    showNotification(`Área calculada: ${areaM2.toLocaleString()} m²`, 'success');
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
    
    // Colores según el tipo
    const colors = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white',
        warning: 'bg-yellow-500 text-white'
    };
    
    notification.className += ` ${colors[type] || colors.info}`;
    notification.innerHTML = `
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Función para validar coordenadas
function validateCoordinates() {
    const invalidCoords = coordinates.filter(coord => 
        coord.latitude < -90 || coord.latitude > 90 || 
        coord.longitude < -180 || coord.longitude > 180
    );
    
    if (invalidCoords.length > 0) {
        showNotification('Algunas coordenadas están fuera de rango válido', 'warning');
        return false;
    }
    
    return true;
}

// Función para actualizar la visualización de conversiones de área
function updateAreaDisplay(areaM2) {
    const areaConversion = document.getElementById('areaConversion');
    
    if (areaM2 && areaM2 > 0) {
        const areaKm2 = (areaM2 / 1000000).toFixed(6);
        const areaHectares = (areaM2 / 10000).toFixed(2);
        
        areaConversion.innerHTML = `
            <div class="flex gap-4 text-xs">
                <span><strong>${parseInt(areaM2).toLocaleString()} m²</strong></span>
                <span>• ${areaKm2} km²</span>
                <span>• ${areaHectares} ha</span>
            </div>
        `;
        areaConversion.classList.remove('hidden');
    } else {
        areaConversion.classList.add('hidden');
    }
}

console.log('Leaflet inicializado siguiendo la guía oficial');

} // Fin del bloque de prevención de ejecución múltiple
</script>