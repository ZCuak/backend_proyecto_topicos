<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\BrandModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class BrandModelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            $query = BrandModel::with(['brand']);
            $columns = Schema::getColumnListing('brandmodels');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            if ($all) {
                $models = $query->get();
                return response()->json([
                    'success' => true,
                    'data' => $models,
                    'message' => 'Modelos obtenidos exitosamente (todos)',
                    'pagination' => null
                ]);
            }

            $models = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'data' => $models->items(),
                'message' => 'Modelos listados correctamente',
                'pagination' => [
                    'current_page' => $models->currentPage(),
                    'last_page' => $models->lastPage(),
                    'per_page' => $models->perPage(),
                    'total' => $models->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar modelos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:brandmodels,name',
            'brand_id' => 'required|exists:brands,id'
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

            $user = BrandModel::create($request->all());

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $user->load(['brand']),
                'message' => 'Modelo creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear modelo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $model = BrandModel::with(['brand'])->find($id);
            if (!$model) {
                return response()->json(['success' => false, 'message' => 'Modelo no encontrado'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $model,
                'message' => 'Modelo obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener modelo', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar modelo
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $model = BrandModel::find($id);
            if (!$model) {
                return response()->json(['success' => false, 'message' => 'Modelo no encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:brandmodels,name',
                'brand_id' => 'required|exists:brands,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            $model->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $model->load(['brand']),
                'message' => 'Modelo actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar modelo', 'error' => $e->getMessage()], 500);
        }
    }


    public function destroy($id): JsonResponse
    {
        try {
            $model = BrandModel::find($id);
            if (!$model) {
                return response()->json(['success' => false, 'message' => 'Modelo no encontrado'], 404);
            }

            $model->delete();

            return response()->json(['success' => true, 'message' => 'Modelo eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar modelo', 'error' => $e->getMessage()], 500);
        }
    }
}
