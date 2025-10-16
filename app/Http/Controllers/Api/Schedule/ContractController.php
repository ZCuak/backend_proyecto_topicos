<?php

namespace App\Http\Controllers\Api\Schedule;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class ContractController extends Controller
{
    /**
     * Listado paginado de contratos con búsqueda, filtros y ordenamiento.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $sortBy = $request->input('sortBy', 'id');
            $sortOrder = $request->input('sortOrder', 'asc');

            $query = Contract::query();

            if ($search) {
                $columns = Schema::getColumnListing('contracts');
                $excluir = ['id', 'created_at', 'updated_at', 'deleted_at'];
                $columns = array_diff($columns, $excluir);

                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'LIKE', "%{$search}%");
                    }
                });
            }

            foreach ($request->all() as $key => $value) {
                if (Schema::hasColumn('contracts', $key) && ! in_array($key, ['search', 'sortBy', 'sortOrder', 'per_page', 'all'])) {
                    $query->where($key, $value);
                }
            }

            if (Schema::hasColumn('contracts', $sortBy)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $all = $request->boolean('all', false);
            $data = $all ? $query->get() : $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Contratos obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener contratos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacena un nuevo contrato.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:nombrado,permanente,eventual',
                'date_start' => 'required|date',
                'date_end' => 'nullable|date|after_or_equal:date_start',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $contract = Contract::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $contract,
                'message' => 'Contrato creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear contrato',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra un contrato por ID.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $contract = Contract::find($id);

            if (! $contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrato no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $contract,
                'message' => 'Contrato obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener contrato',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza un contrato existente.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $contract = Contract::find($id);

            if (! $contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrato no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|required|in:nombrado,permanente,eventual',
                'date_start' => 'sometimes|required|date',
                'date_end' => 'nullable|date|after_or_equal:date_start',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'user_id' => 'sometimes|required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $contract->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $contract,
                'message' => 'Contrato actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar contrato',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina (soft delete) un contrato.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $contract = Contract::find($id);

            if (! $contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrato no encontrado'
                ], 404);
            }

            $contract->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contrato eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar contrato',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}