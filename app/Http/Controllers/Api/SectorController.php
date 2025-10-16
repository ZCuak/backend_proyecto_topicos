<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SectorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            $query = Sector::with(['district']);
            $columns = Schema::getColumnListing('sectors');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            if ($all) {
                $sectors = $query->get();
                return response()->json([
                    'success' => true,
                    'data' => $sectors,
                    'message' => 'Sectores obtenidos exitosamente (todos)',
                    'pagination' => null
                ]);
            }

            $sectors = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'data' => $sectors->items(),
                'message' => 'Sectores listados correctamente',
                'pagination' => [
                    'current_page' => $sectors->currentPage(),
                    'last_page' => $sectors->lastPage(),
                    'per_page' => $sectors->perPage(),
                    'total' => $sectors->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar sectores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:sectors,name',
            'area' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'district_id' => 'required|exists:districts,id'
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

            $sector = Sector::create($request->all());

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $sector->load(['district']),
                'message' => 'Sector creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear sector',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $sector = Sector::with(['district'])->find($id);
            if (!$sector) {
                return response()->json(['success' => false, 'message' => 'Sector no encontrado'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $sector,
                'message' => 'Sector obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener sector', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar sector
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $sector = Sector::find($id);
            if (!$sector) {
                return response()->json(['success' => false, 'message' => 'Sector no encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:sectors,name,' . $id,
                'area' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'district_id' => 'required|exists:districts,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            $sector->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $sector->load(['district']),
                'message' => 'Sector actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar sector', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $sector = Sector::find($id);
            if (!$sector) {
                return response()->json(['success' => false, 'message' => 'Sector no encontrado'], 404);
            }

            $sector->delete();

            return response()->json(['success' => true, 'message' => 'Sector eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar sector', 'error' => $e->getMessage()], 500);
        }
    }
}
