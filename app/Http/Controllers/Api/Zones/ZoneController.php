<?php

namespace App\Http\Controllers\Api\Zones;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\ZoneCoord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ZoneController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            $query = Zone::with(['sector','district','coordinates']);
            $columns = Schema::getColumnListing('zones');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            if ($all) {
                $zones = $query->get();
                return response()->json([
                    'success' => true,
                    'data' => $zones,
                    'message' => 'Zonas obtenidas exitosamente (todas)',
                    'pagination' => null
                ]);
            }

            $zones = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'data' => $zones->items(),
                'message' => 'Zonas listadas correctamente',
                'pagination' => [
                    'current_page' => $zones->currentPage(),
                    'last_page' => $zones->lastPage(),
                    'per_page' => $zones->perPage(),
                    'total' => $zones->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar zonas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:zones,name',
            'description' => 'nullable|string',
            'sector_id' => 'required|exists:sectors,id',
            'district_id' => 'required|exists:districts,id',
            //json con la estructura {"1":{"latitude":x, "longitude":y},"2":...}
            'coords' => ['required', 'json', function ($attribute, $value, $fail) {
                $decoded = json_decode($value, true);
                if (!is_array($decoded)) {
                    return $fail('El campo coords debe ser un JSON válido.');
                }
                foreach ($decoded as $key => $coord) {
                    if (!is_array($coord) || !isset($coord['latitude'], $coord['longitude'])) {
                        return $fail("Cada coordenada debe contener 'latitude' y 'longitude'.");
                    }
                }
            }],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {

            $zone = Zone::create([
                'name' => $request->name,
                'area' => $request->area,
                'description' => $request->description,
                'sector_id' => $request->sector_id,
                'district_id' => $request->district_id,
            ]);

            $coords = json_decode($request->coords, true);
            foreach ($coords as $coord) {
                ZoneCoord::create([
                    'latitude' => $coord['latitude'],
                    'longitude' => $coord['longitude'],
                    'zone_id' => $zone->id,
                ]);
            }
            

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $zone->load(['sector','district','coordinates']),
                'message' => 'Zona creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear zona',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $zone = Zone::with(['sector','district','coordinates'])->find($id);
            if (!$zone) {
                return response()->json(['success' => false, 'message' => 'Zona no encontrada'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $zone,
                'message' => 'Zona obtenida exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener zona', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar zona
     */
    public function update(Request $request, $id): JsonResponse
    {
        $zone = Zone::find($id);
        if (!$zone) {
            return response()->json(['success' => false, 'message' => 'Zona no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100|unique:zones,name,' . $id,
            'description' => 'nullable|string',
            'sector_id' => 'required|exists:sectors,id',
            'district_id' => 'required|exists:districts,id',
            'coords' => ['required', 'json', function ($attribute, $value, $fail) {
                $decoded = json_decode($value, true);
                if (!is_array($decoded)) {
                    return $fail('El campo coords debe ser un JSON válido.');
                }
                foreach ($decoded as $key => $coord) {
                    if (!is_array($coord) || !isset($coord['latitude'], $coord['longitude'])) {
                        return $fail("Cada coordenada debe contener 'latitude' y 'longitude'.");
                    }
                }
            }],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $zone->update([
                'name' => $request->name,
                'area' => $request->area,
                'description' => $request->description,
                'sector_id' => $request->sector_id,
                'district_id' => $request->district_id,
            ]);

            // Eliminar coordenadas existentes
            ZoneCoord::where('zone_id', $zone->id)->delete();

            // Insertar nuevas coordenadas
            $coords = json_decode($request->coords, true);
            foreach ($coords as $coord) {
                ZoneCoord::create([
                    'latitude' => $coord['latitude'],
                    'longitude' => $coord['longitude'],
                    'zone_id' => $zone->id,
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $zone->load(['sector', 'district', 'coordinates']),
                'message' => 'Zona actualizada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar zona',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id): JsonResponse
    {
        try {
            $zone = Zone::find($id);
            if (!$zone) {
                return response()->json(['success' => false, 'message' => 'Zona no encontrada'], 404);
            }

            $zone->delete();

            return response()->json(['success' => true, 'message' => 'Zona eliminada correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar zona', 'error' => $e->getMessage()], 500);
        }
    }
}
