<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ProvinceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            $query = Province::with(['department']);
            $columns = Schema::getColumnListing('provinces');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            // Aplicar filtros exactos por campo (si vienen en el request)
            foreach ($request->all() as $key => $value) {
                if (Schema::hasColumn('provinces', $key) && $key !== 'search' && $key !== 'perPage' && $key !== 'all') {
                    $query->where($key, $value);
                }
            }

            if ($all) {
                $provinces = $query->get();
                return response()->json([
                    'success' => true,
                    'data' => $provinces,
                    'message' => 'Provincias obtenidas exitosamente (todos)',
                    'pagination' => null
                ]);
            }

            $provinces = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'data' => $provinces->items(),
                'message' => 'Provincias listadas correctamente',
                'pagination' => [
                    'current_page' => $provinces->currentPage(),
                    'last_page' => $provinces->lastPage(),
                    'per_page' => $provinces->perPage(),
                    'total' => $provinces->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar provincias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:provinces,name',
            'code' => 'required|string|max:10|unique:provinces,code',
            'department_id' => 'required|exists:departments,id'
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

            $province = Province::create($request->all());

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $province->load(['department']),
                'message' => 'Provincia creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear provincia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $province = Province::with(['department'])->find($id);
            if (!$province) {
                return response()->json(['success' => false, 'message' => 'Provincia no encontrada'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $province,
                'message' => 'Provincia obtenida exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener provincia', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar provincia
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $province = Province::find($id);
            if (!$province) {
                return response()->json(['success' => false, 'message' => 'Provincia no encontrada'], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:provinces,name,' . $id,
                'code' => 'required|string|max:10|unique:provinces,code,' . $id,
                'department_id' => 'required|exists:departments,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            $province->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $province->load(['department']),
                'message' => 'Provincia actualizada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar provincia', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $province = Province::find($id);
            if (!$province) {
                return response()->json(['success' => false, 'message' => 'Provincia no encontrada'], 404);
            }

            $province->delete();

            return response()->json(['success' => true, 'message' => 'Provincia eliminada correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar provincia', 'error' => $e->getMessage()], 500);
        }
    }
}
