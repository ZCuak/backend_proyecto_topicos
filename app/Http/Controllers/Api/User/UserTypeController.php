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
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            $query = UserType::query();
            $columns = Schema::getColumnListing('usertypes');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            if ($all) {
                $types = $query->orderBy('id', 'asc')->get();

                return response()->json([
                    'success' => true,
                    'data' => $types,
                    'message' => 'Tipos de usuario obtenidos exitosamente (todos)',
                    'pagination' => null
                ]);
            } else {
                $types = $query->orderBy('id', 'asc')->paginate($perPage);

                return response()->json([
                    'success' => true,
                    'data' => $types->items(),
                    'message' => 'Tipos de usuario obtenidos exitosamente',
                    'pagination' => [
                        'current_page' => $types->currentPage(),
                        'last_page' => $types->lastPage(),
                        'per_page' => $types->perPage(),
                        'total' => $types->total()
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar tipos de usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo tipo de usuario
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:usertypes,name',
            'is_system' => 'boolean'
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
            $type = UserType::create($validator->validated());
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $type,
                'message' => 'Tipo de usuario creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tipo de usuario',
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
                return response()->json(['success' => false, 'message' => 'Tipo de usuario no encontrado'], 404);
            }

            return response()->json(['success' => true, 'data' => $type, 'message' => 'Tipo de usuario obtenido exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener tipo de usuario', 'error' => $e->getMessage()], 500);
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
                return response()->json(['success' => false, 'message' => 'Tipo de usuario no encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:100|unique:usertypes,name,' . $id,
                'is_system' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
            }

            $type->update($validator->validated());

            return response()->json(['success' => true, 'data' => $type, 'message' => 'Tipo de usuario actualizado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar tipo de usuario', 'error' => $e->getMessage()], 500);
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
                return response()->json(['success' => false, 'message' => 'Tipo de usuario no encontrado'], 404);
            }

            $type->delete();

            return response()->json(['success' => true, 'message' => 'Tipo de usuario eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar tipo de usuario', 'error' => $e->getMessage()], 500);
        }
    }
}
