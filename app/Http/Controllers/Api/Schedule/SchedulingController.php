<?php

namespace App\Http\Controllers\Api\Scheduling;

use App\Http\Controllers\Controller;
use App\Models\Scheduling;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SchedulingController extends Controller
{
    /**
     * Listar todas las programaciones (schedulings)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            $query = Scheduling::query();
            $columns = Schema::getColumnListing('schedulings');

            // 🔍 Búsqueda por cualquier campo
            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            // 🔹 Si se piden todas sin paginación
            if ($all) {
                $schedulings = $query->with(['group','schedule','vehicle','zone'])->orderBy('id', 'asc')->get();

                return response()->json([
                    'success' => true,
                    'data' => $schedulings,
                    'message' => 'Programaciones obtenidas exitosamente (todas)',
                    'pagination' => null
                ], 200);
            }

            // 🔹 Con paginación
            $schedulings = $query->orderBy('id', 'asc')->paginate($perPage)->appends([
                'search' => $search,
                'perPage' => $perPage
            ]);

            return response()->json([
                'success' => true,
                'data' => $schedulings->items(),
                'message' => 'Programaciones obtenidas exitosamente',
                'pagination' => [
                    'current_page' => $schedulings->currentPage(),
                    'last_page' => $schedulings->lastPage(),
                    'per_page' => $schedulings->perPage(),
                    'total' => $schedulings->total()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar programaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo programación (scheduling)
     */
    public function store(Request $request): JsonResponse
    {
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|string|max:100|unique:schedulings,name',
        //     'time_start' => 'required|date_format:H:i',
        //     'time_end' => 'required|date_format:H:i|after:time_start',
        //     'description' => 'nullable|string|max:500'
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Error de validación',
        //         'errors' => $validator->errors()
        //     ], 422);
        // }

        // DB::beginTransaction();
        // try {
        //     $scheduling = Scheduling::create($validator->validated());
        //     DB::commit();

        //     return response()->json([
        //         'success' => true,
        //         'data' => $scheduling,
        //         'message' => 'Programación creado exitosamente'
        //     ], 201);
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Error al crear programación',
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
    }

    /**
     * Mostrar un programación específica
     */
    public function show(int $id): JsonResponse
    {
        try {
            $scheduling = Scheduling::with(['group','schedule','vehicle','zone'])->find($id);

            if (!$scheduling) {
                return response()->json([
                    'success' => false,
                    'message' => 'Programación no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $scheduling,
                'message' => 'Programación obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener programación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un programación
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // try {
        //     $scheduling = Scheduling::find($id);

        //     if (!$scheduling) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Programación no encontrado'
        //         ], 404);
        //     }

        //     $validator = Validator::make($request->all(), [
        //         'name' => 'sometimes|required|string|max:100|unique:schedulings,name,' . $id,
        //         'time_start' => 'sometimes|required|date_format:H:i',
        //         'time_end' => 'sometimes|required|date_format:H:i|after:time_start',
        //         'description' => 'nullable|string|max:500'
        //     ]);

        //     if ($validator->fails()) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Error de validación',
        //             'errors' => $validator->errors()
        //         ], 422);
        //     }

        //     $scheduling->update($validator->validated());

        //     return response()->json([
        //         'success' => true,
        //         'data' => $scheduling,
        //         'message' => 'Programación actualizado exitosamente'
        //     ], 200);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Error al actualizar programación',
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
    }

    /**
     * Eliminar un programación (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $scheduling = Scheduling::find($id);

            if (!$scheduling) {
                return response()->json([
                    'success' => false,
                    'message' => 'Programación no encontrada'
                ], 404);
            }

            $scheduling->delete();

            return response()->json([
                'success' => true,
                'message' => 'Programación eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar programación',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
