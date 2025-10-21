<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Listar usuarios
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            // Eliminamos 'zone' porque ya no existe la relación
            $query = User::with(['usertype']);
            $columns = Schema::getColumnListing('users');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            if ($all) {
                $users = $query->get();
                return response()->json([
                    'success' => true,
                    'data' => $users,
                    'message' => 'Usuarios obtenidos exitosamente (todos)',
                    'pagination' => null
                ]);
            }

            $users = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'data' => $users->items(),
                'message' => 'Usuarios listados correctamente',
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo usuario
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:users,username',
            'dni' => 'required|digits:8|unique:users,dni',
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'birthdate' => 'nullable|date',
            'license' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'profile_photo_path' => 'nullable|string|max:255',
            'usertype_id' => 'required|exists:usertypes,id',
            'status' => 'in:ACTIVO,INACTIVO'
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
            $data['password'] = Hash::make($data['password']); // Encriptar contraseña

            $user = User::create($data);

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $user->load(['usertype']),
                'message' => 'Usuario creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar usuario
     */
    public function show($id): JsonResponse
    {
        try {
            $user = User::with(['usertype'])->find($id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Usuario obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'username' => 'sometimes|required|string|max:100|unique:users,username,' . $id,
                'dni' => 'sometimes|required|digits:8|unique:users,dni,' . $id,
                'firstname' => 'sometimes|required|string|max:100',
                'lastname' => 'sometimes|required|string|max:100',
                'birthdate' => 'nullable|date',
                'license' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8',
                'profile_photo_path' => 'nullable|string|max:255',
                'usertype_id' => 'exists:usertypes,id',
                'status' => 'in:ACTIVO,INACTIVO'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'data' => $user->load(['usertype']),
                'message' => 'Usuario actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar usuario (soft delete)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
