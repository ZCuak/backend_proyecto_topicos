<?php

namespace App\Http\Controllers\Api\Schedule;

use App\Http\Controllers\Controller;
use App\Models\Vacation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class VacationController extends Controller
{
    /**
     * Listado paginado de vacaciones con búsqueda, filtros y ordenamiento.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $sortBy = $request->input('sortBy', 'id');
            $sortOrder = $request->input('sortOrder', 'asc');

            $query = Vacation::query();

            if ($search) {
                $columns = Schema::getColumnListing('vacations');
                $excluir = ['id', 'created_at', 'updated_at', 'deleted_at'];
                $columns = array_diff($columns, $excluir);

                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'LIKE', "%{$search}%");
                    }
                });
            }

            foreach ($request->all() as $key => $value) {
                if (Schema::hasColumn('vacations', $key) && ! in_array($key, ['search', 'sortBy', 'sortOrder', 'per_page', 'all'])) {
                    $query->where($key, $value);
                }
            }

            if (Schema::hasColumn('vacations', $sortBy)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $all = $request->boolean('all', false);
            $data = $all ? $query->get() : $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Vacaciones obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vacaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacena una nueva solicitud de vacaciones.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
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

            $vacation = Vacation::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $vacation,
                'message' => 'Vacación creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear vacación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra una solicitud de vacaciones por ID.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $vacation = Vacation::find($id);

            if (! $vacation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacación no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $vacation,
                'message' => 'Vacación obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vacación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza una solicitud de vacaciones existente.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $vacation = Vacation::find($id);

            if (! $vacation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacación no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
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

            $vacation->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $vacation,
                'message' => 'Vacación actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar vacación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina (soft delete) una solicitud de vacaciones.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $vacation = Vacation::find($id);

            if (! $vacation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacación no encontrada'
                ], 404);
            }

            $vacation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vacación eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar vacación',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}