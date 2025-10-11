<?php

namespace App\Http\Controllers\Api\Schedule;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    /**
     * Listar todos los turnos (schedules)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            $query = Schedule::query();
            $columns = Schema::getColumnListing('schedules');

            // 🔍 Búsqueda por cualquier campo
            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            // 🔹 Si se piden todos sin paginación
            if ($all) {
                $schedules = $query->orderBy('id', 'asc')->get();

                return response()->json([
                    'success' => true,
                    'data' => $schedules,
                    'message' => 'Turnos obtenidos exitosamente (todos)',
                    'pagination' => null
                ], 200);
            }

            // 🔹 Con paginación
            $schedules = $query->orderBy('id', 'asc')->paginate($perPage)->appends([
                'search' => $search,
                'perPage' => $perPage
            ]);

            return response()->json([
                'success' => true,
                'data' => $schedules->items(),
                'message' => 'Turnos obtenidos exitosamente',
                'pagination' => [
                    'current_page' => $schedules->currentPage(),
                    'last_page' => $schedules->lastPage(),
                    'per_page' => $schedules->perPage(),
                    'total' => $schedules->total()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar turnos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo turno (schedule)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:schedules,name',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'description' => 'nullable|string|max:500'
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
            $schedule = Schedule::create($validator->validated());
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $schedule,
                'message' => 'Turno creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear turno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un turno específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $schedule = Schedule::find($id);

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Turno no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $schedule,
                'message' => 'Turno obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener turno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un turno
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $schedule = Schedule::find($id);

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Turno no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:100|unique:schedules,name,' . $id,
                'time_start' => 'sometimes|required|date_format:H:i',
                'time_end' => 'sometimes|required|date_format:H:i|after:time_start',
                'description' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $schedule->update($validator->validated());

            return response()->json([
                'success' => true,
                'data' => $schedule,
                'message' => 'Turno actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar turno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un turno (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $schedule = Schedule::find($id);

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Turno no encontrado'
                ], 404);
            }

            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Turno eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar turno',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
