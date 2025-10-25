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
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');

            // constructor de consultas
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

            // Ordenamiento por defecto
            $query->orderBy('name', 'asc');

            // Paginación
            $usertypes = $query->paginate(10);

            // Retornamos vista
            return view('usertypes.index', compact('usertypes', 'search'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar funciones: ' . $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $usertype = new UserType();

        return response()
            ->view('usertypes._modal_create', compact('usertype'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Formulario de edición (Turbo modal)
     */
    public function edit($id)
    {
        $usertype = UserType::findOrFail($id);

        return response()
            ->view('usertypes._modal_edit', compact('usertype'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Crear un nuevo tipo de usuario
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_system' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {

            $userTypeExistente = UserType::withTrashed()
                ->where('name', $request->name)
                ->first();

            if ($userTypeExistente) {
                if ($userTypeExistente->trashed()) {
                    $userTypeExistente->restore();
                    $userTypeExistente->update($request->all());
                    DB::commit();

                    if ($isTurbo) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Tipo de personal restaurado y actualizado exitosamente.',
                        ], 200);
                    }

                    return redirect()->route('usertypes.index')
                    ->with('success', 'Función restaurada exitosamente.');
                } else {
                    DB::rollBack();
                    if ($isTurbo) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Ya existe una función con este nombre',
                            'errors' => ['name' => ['El nombre ya está registrado']]
                        ], 422);
                    }
                    return back()->withErrors(['name' => 'Ya existe un tipo de personal con este nombre'])
                        ->withInput();
                }
            }

            UserType::create($request->all());
            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tipo de personal registrado exitosamente.',
                ], 201);
            }
            return redirect()->route('usertypes.index')
                ->with('success', 'Tipo de personal registrada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear función: ' . $e->getMessage(),
                ], 500); // 500 = Internal Server Error
            }
            return back()->with('error', 'Error al crear función: ' . $e->getMessage());
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
    public function update(Request $request, $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();
        try {
            $type = UserType::find($id);
            if (!$type) {
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tipo de personal no encontrado.',
                    ], 404);
                }
                return back()->with('error', 'Tipo de personal no encontrado.');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:usertypes,name,' . $id,
                'description' => 'nullable|string',
                'is_system' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Errores de validación.',
                        'errors' => $validator->errors(),
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            $type->update($request->all());

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tipo de personal actualizado correctamente.',
                ], 200);
            }
            return redirect()->route('usertypes.index')
                ->with('success', 'Tipo de personal actualizado correctamente.');
        } catch (\Exception $e) {
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
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
                return response()->json(['success' => false, 'message' => 'Tipo de personal no encontrado'], 404);
            }

            if ($type->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar esta tipo porque es predefinida del sistema',
                    'errors' => [
                        'is_system' => [
                            'Las funciones "Conductor" y "Ayudante" son requeridas por el sistema y no pueden ser eliminadas'
                        ]
                    ]
                ], 422);
            }

            $type->delete();

            return response()->json(['success' => true, 'message' => 'Tipo de personal eliminado correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar Tipo de personal: '. $e->getMessage()], 500);
        }
    }
}
