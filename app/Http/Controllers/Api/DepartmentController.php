<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            $query = Department::with(['provinces']);
            $columns = Schema::getColumnListing('departments');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            if ($all) {
                $departments = $query->get();
                return response()->json([
                    'success' => true,
                    'data' => $departments,
                    'message' => 'Departamentos obtenidos exitosamente (todos)',
                    'pagination' => null
                ]);
            }

            $departments = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'data' => $departments->items(),
                'message' => 'Departamentos listados correctamente',
                'pagination' => [
                    'current_page' => $departments->currentPage(),
                    'last_page' => $departments->lastPage(),
                    'per_page' => $departments->perPage(),
                    'total' => $departments->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar departamentos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:departments,name',
            'code' => 'required|string|max:10|unique:departments,code'
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

            $department = Department::create($request->all());

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $department->load(['provinces']),
                'message' => 'Departamento creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear departamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $department = Department::with(['provinces'])->find($id);
            if (!$department) {
                return response()->json(['success' => false, 'message' => 'Departamento no encontrado'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $department,
                'message' => 'Departamento obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener departamento', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar departamento
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $department = Department::find($id);
            if (!$department) {
                return response()->json(['success' => false, 'message' => 'Departamento no encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:departments,name,' . $id,
                'code' => 'required|string|max:10|unique:departments,code,' . $id
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            $department->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $department->load(['provinces']),
                'message' => 'Departamento actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar departamento', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $department = Department::find($id);
            if (!$department) {
                return response()->json(['success' => false, 'message' => 'Departamento no encontrado'], 404);
            }

            $department->delete();

            return response()->json(['success' => true, 'message' => 'Departamento eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar departamento', 'error' => $e->getMessage()], 500);
        }
    }
}
