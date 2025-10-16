<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DistrictController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            $query = District::with(['sectors', 'zones']);
            $columns = Schema::getColumnListing('districts');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            // Aplicar filtros exactos por campo (si vienen en el request)
            foreach ($request->all() as $key => $value) {
                if (Schema::hasColumn('districts', $key) && $key !== 'search' && $key !== 'perPage' && $key !== 'all') {
                    $query->where($key, $value);
                }
            }

            if ($all) {
                $districts = $query->get();
                return response()->json([
                    'success' => true,
                    'data' => $districts,
                    'message' => 'Distritos obtenidos exitosamente (todos)',
                    'pagination' => null
                ]);
            }

            $districts = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'data' => $districts->items(),
                'message' => 'Distritos listados correctamente',
                'pagination' => [
                    'current_page' => $districts->currentPage(),
                    'last_page' => $districts->lastPage(),
                    'per_page' => $districts->perPage(),
                    'total' => $districts->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar distritos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:districts,name',
            'code' => 'required|string|max:10|unique:districts,code',
            'department_id' => 'required|exists:departments,id',
            'province_id' => 'required|exists:provinces,id'
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

            $district = District::create($request->all());

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $district->load(['sectors', 'zones']),
                'message' => 'Distrito creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear distrito',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $district = District::with(['sectors', 'zones'])->find($id);
            if (!$district) {
                return response()->json(['success' => false, 'message' => 'Distrito no encontrado'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $district,
                'message' => 'Distrito obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener distrito', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar distrito
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $district = District::find($id);
            if (!$district) {
                return response()->json(['success' => false, 'message' => 'Distrito no encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:districts,name,' . $id,
                'code' => 'required|string|max:10|unique:districts,code,' . $id,
                'department_id' => 'required|exists:departments,id',
                'province_id' => 'required|exists:provinces,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            $district->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $district->load(['sectors', 'zones']),
                'message' => 'Distrito actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar distrito', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $district = District::find($id);
            if (!$district) {
                return response()->json(['success' => false, 'message' => 'Distrito no encontrado'], 404);
            }

            $district->delete();

            return response()->json(['success' => true, 'message' => 'Distrito eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar distrito', 'error' => $e->getMessage()], 500);
        }
    }
}
