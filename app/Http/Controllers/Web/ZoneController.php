<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\ZoneCoord;
use App\Models\District;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ZoneController extends Controller
{
    /**
     * Mostrar listado de zonas
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $query = Zone::with(['district', 'sector', 'coordinates']);
            $columns = Schema::getColumnListing('zones');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            $zones = $query->paginate(10);
            return view('zones.index', compact('zones', 'search'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar zonas: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de creación (Turbo modal)
     */
    public function create(Request $request)
    {
        $districts = District::orderBy('name')->get();
        $zone = new Zone();
        
        // Establecer distrito por defecto
        $zone->district_id = 1259; // JOSE LEONARDO ORTIZ
        
        // Cargar sectores del distrito por defecto
        $sectors = Sector::where('district_id', 1259)->orderBy('name')->get();

        return response()
            ->view('zones._modal_create', compact('districts', 'sectors', 'zone'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Formulario de edición (Turbo modal)
     */
    public function edit($id)
    {
        $zone = Zone::with(['district', 'sector', 'coordinates'])->findOrFail($id);
        $districts = District::orderBy('name')->get();
        $sectors = Sector::where('district_id', $zone->district_id)->orderBy('name')->get();

        return response()
            ->view('zones._modal_edit', compact('zone', 'districts', 'sectors'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Guardar nuevo registro
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:zones,name',
            'description' => 'nullable|string',
            'area' => 'nullable|numeric|min:0',
            'district_id' => 'required|exists:districts,id',
            'sector_id' => 'required|exists:sectors,id',
            'coords' => ['required', 'json', function ($attribute, $value, $fail) {
                $decoded = json_decode($value, true);
                if (!is_array($decoded)) {
                    return $fail('El campo coordenadas debe ser un JSON válido.');
                }
                if (count($decoded) < 3) {
                    return $fail('Se necesitan al menos 3 puntos para formar un polígono.');
                }
                foreach ($decoded as $coord) {
                    if (!is_array($coord) || !isset($coord['latitude'], $coord['longitude'])) {
                        return $fail("Cada coordenada debe contener 'latitude' y 'longitude'.");
                    }
                    if (!is_numeric($coord['latitude']) || !is_numeric($coord['longitude'])) {
                        return $fail('Las coordenadas deben ser números válidos.');
                    }
                    if ($coord['latitude'] < -90 || $coord['latitude'] > 90) {
                        return $fail('La latitud debe estar entre -90 y 90.');
                    }
                    if ($coord['longitude'] < -180 || $coord['longitude'] > 180) {
                        return $fail('La longitud debe estar entre -180 y 180.');
                    }
                }
            }],
        ]);

        if ($validator->fails()) {
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $data = $validator->validated();
            
            $zone = Zone::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'area' => $data['area'],
                'district_id' => $data['district_id'],
                'sector_id' => $data['sector_id'],
            ]);

            // Guardar coordenadas
            $coords = json_decode($data['coords'], true);
            foreach ($coords as $coord) {
                ZoneCoord::create([
                    'latitude' => $coord['latitude'],
                    'longitude' => $coord['longitude'],
                    'zone_id' => $zone->id,
                ]);
            }

            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Zona registrada exitosamente.',
                ], 201);
            }

            return redirect()->route('zones.index')
                ->with('success', 'Zona registrada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear zona: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al crear zona: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar detalles de una zona
     */
    public function show($id)
    {
        try {
            $zone = Zone::with(['district', 'sector', 'coordinates'])->findOrFail($id);
            return view('zones.show', compact('zone'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al mostrar zona: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar datos
     */
    public function update(Request $request, $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        try {
            $zone = Zone::find($id);
            if (!$zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zona no encontrada.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:zones,name,' . $id,
                'description' => 'nullable|string',
                'area' => 'nullable|numeric|min:0',
                'district_id' => 'required|exists:districts,id',
                'sector_id' => 'required|exists:sectors,id',
                'coords' => ['required', 'json', function ($attribute, $value, $fail) {
                    $decoded = json_decode($value, true);
                    if (!is_array($decoded)) {
                        return $fail('El campo coordenadas debe ser un JSON válido.');
                    }
                    if (count($decoded) < 3) {
                        return $fail('Se necesitan al menos 3 puntos para formar un polígono.');
                    }
                    foreach ($decoded as $coord) {
                        if (!is_array($coord) || !isset($coord['latitude'], $coord['longitude'])) {
                            return $fail("Cada coordenada debe contener 'latitude' y 'longitude'.");
                        }
                        if (!is_numeric($coord['latitude']) || !is_numeric($coord['longitude'])) {
                            return $fail('Las coordenadas deben ser números válidos.');
                        }
                        if ($coord['latitude'] < -90 || $coord['latitude'] > 90) {
                            return $fail('La latitud debe estar entre -90 y 90.');
                        }
                        if ($coord['longitude'] < -180 || $coord['longitude'] > 180) {
                            return $fail('La longitud debe estar entre -180 y 180.');
                        }
                    }
                }],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            DB::beginTransaction();
            try {
                $zone->update([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'area' => $data['area'],
                    'district_id' => $data['district_id'],
                    'sector_id' => $data['sector_id'],
                ]);

                // Eliminar coordenadas existentes
                ZoneCoord::where('zone_id', $zone->id)->delete();

                // Insertar nuevas coordenadas
                $coords = json_decode($data['coords'], true);
                foreach ($coords as $coord) {
                    ZoneCoord::create([
                        'latitude' => $coord['latitude'],
                        'longitude' => $coord['longitude'],
                        'zone_id' => $zone->id,
                    ]);
                }

                DB::commit();

                if ($isTurbo) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Zona actualizada exitosamente.',
                    ], 200);
                }

                return redirect()->route('zones.index')
                    ->with('success', 'Zona actualizada exitosamente.');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar zona: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al actualizar zona: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar (Soft Delete)
     */
    public function destroy($id)
    {
        try {
            $zone = Zone::find($id);
            if (!$zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zona no encontrada.',
                ], 404);
            }

            $zone->delete();

            return response()->json([
                'success' => true,
                'message' => 'Zona eliminada correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar zona: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener sectores por distrito (AJAX)
     */
    public function getSectorsByDistrict($districtId)
    {
        try {
            $sectors = Sector::where('district_id', $districtId)->orderBy('name')->get();
            return response()->json([
                'success' => true,
                'data' => $sectors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar sectores: ' . $e->getMessage()
            ], 500);
        }
    }
}