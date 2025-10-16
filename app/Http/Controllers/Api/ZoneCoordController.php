<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZoneCoord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ZoneCoordController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            $query = ZoneCoord::with(['zone']);
            $columns = Schema::getColumnListing('zonecoords');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            // Aplicar filtros exactos por campo (si vienen en el request)
            foreach ($request->all() as $key => $value) {
                if (Schema::hasColumn('zonecoords', $key) && $key !== 'search' && $key !== 'perPage' && $key !== 'all') {
                    $query->where($key, $value);
                }
            }

            if ($all) {
                $zoneCoords = $query->get();
                return response()->json([
                    'success' => true,
                    'data' => $zoneCoords,
                    'message' => 'Coordenadas de zona obtenidas exitosamente (todos)',
                    'pagination' => null
                ]);
            }

            $zoneCoords = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'data' => $zoneCoords->items(),
                'message' => 'Coordenadas de zona listadas correctamente',
                'pagination' => [
                    'current_page' => $zoneCoords->currentPage(),
                    'last_page' => $zoneCoords->lastPage(),
                    'per_page' => $zoneCoords->perPage(),
                    'total' => $zoneCoords->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar coordenadas de zona',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'zone_id' => 'required|exists:zones,id'
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
            $data = $validator->validated();

            $zoneCoord = ZoneCoord::create($request->all());

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $zoneCoord->load(['zone']),
                'message' => 'Coordenada de zona creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear coordenada de zona',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $zoneCoord = ZoneCoord::with(['zone'])->find($id);
            if (!$zoneCoord) {
                return response()->json(['success' => false, 'message' => 'Coordenada de zona no encontrada'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $zoneCoord,
                'message' => 'Coordenada de zona obtenida exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener coordenada de zona', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar coordenada de zona
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $zoneCoord = ZoneCoord::find($id);
            if (!$zoneCoord) {
                return response()->json(['success' => false, 'message' => 'Coordenada de zona no encontrada'], 404);
            }

            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'zone_id' => 'required|exists:zones,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            $zoneCoord->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $zoneCoord->load(['zone']),
                'message' => 'Coordenada de zona actualizada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar coordenada de zona', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $zoneCoord = ZoneCoord::find($id);
            if (!$zoneCoord) {
                return response()->json(['success' => false, 'message' => 'Coordenada de zona no encontrada'], 404);
            }

            $zoneCoord->delete();

            return response()->json(['success' => true, 'message' => 'Coordenada de zona eliminada correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar coordenada de zona', 'error' => $e->getMessage()], 500);
        }
    }
}
