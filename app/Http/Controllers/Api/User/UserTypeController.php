<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserTypeController extends Controller
{
    /**
     * Listar tipos de usuario
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $sortBy = $request->input('sortBy', 'id');
            $sortOrder = $request->input('sortOrder', 'asc');

            $query = UserType::query();

            if ($search) {
                $columns = Schema::getColumnListing('usertypes');
                $excluir = ['id', 'created_at', 'updated_at', 'deleted_at'];
                $columns = array_diff($columns, $excluir);

                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            foreach ($request->all() as $key => $value) {
                if (
                    Schema::hasColumn('usertypes', $key) &&
                    $key !== 'search' &&
                    $key !== 'sortBy' &&
                    $key !== 'sortOrder' &&
                    $key !== 'per_page' &&
                    $key !== 'all'
                ) {
                    $query->where($key, $value);
                }
            }

            if (Schema::hasColumn('usertypes', $sortBy)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $all = $request->input('all', false);
            if ($all) {
                $userTypes = $query->get();
            } else {
                $userTypes = $query->paginate($perPage);
            }

            return response()->json([
                'success' => true,
                'data' => $userTypes,
                'message' => 'Funciones de personal obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las funciones de personal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo tipo de usuario
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'is_system' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userTypeExistente = UserType::withTrashed()
                ->where('name', $request->name)
                ->first();

            if ($userTypeExistente) {
                if ($userTypeExistente->trashed()) {
                    $userTypeExistente->restore();
                    $userTypeExistente->update($request->all());

                    return response()->json([
                        'success' => true,
                        'data' => $userTypeExistente->fresh(),
                        'message' => 'Función de personal restaurada y actualizada exitosamente'
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe una función con este nombre',
                        'errors' => ['name' => ['El nombre ya está registrado']]
                    ], 422);
                }
            }

            $userType = UserType::create($request->all());
            return response()->json([
                'success' => true,
                'data' => $userType,
                'message' => 'Función de personal creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la función de personal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un tipo de usuario
     */
    public function show($id): JsonResponse
    {
        try {
            $type = UserType::find($id);

            if (!$type) {
                return response()->json(['success' => false, 'message' => 'Función de personal no encontrado'], 404);
            }

            return response()->json(['success' => true, 'data' => $type, 'message' => 'Función de personal obtenido exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener la Función de personal', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar tipo de usuario
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $type = UserType::find($id);
            if (!$type) {
                return response()->json(['success' => false, 'message' => 'Función de personal no encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:100|unique:usertypes,name,' . $id,
                'description' => 'nullable|string',
                'is_system' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
            }

            $type->update($request->all());

            return response()->json(['success' => true, 'data' => $type, 'message' => 'Función de personal actualizado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar la función de personal', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar tipo de usuario (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $type = UserType::find($id);
            if (!$type) {
                return response()->json(['success' => false, 'message' => 'Función de personal no encontrado'], 404);
            }

            if ($type->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar esta función porque es una función predefinida del sistema',
                    'errors' => [
                        'is_system' => [
                            'Las funciones "Conductor" y "Ayudante" son requeridas por el sistema y no pueden ser eliminadas'
                        ]
                    ]
                ], 422);
            }

            $type->delete();

            return response()->json(['success' => true, 'message' => 'Función de personal eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar Función de personal', 'error' => $e->getMessage()], 500);
        }
    }
}
