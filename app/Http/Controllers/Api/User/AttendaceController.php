<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Attendace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AttendaceController extends Controller
{
    /**
     * Obtiene lista de asistencias con filtros avanzados.
     * 
     * Filtros disponibles:
     * - user_id: ID del usuario
     * - date: Fecha específica
     * - start_date y end_date: Rango de fechas
     * - status: Estado (PRESENTE, AUSENTE, TARDANZA)
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $sortBy = $request->input('sortBy', 'date');
            $sortOrder = $request->input('sortOrder', 'desc');

            $query = Attendace::with('user:id,name,dni'); // Cargar relación user

            if ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                        ->orWhere('dni', 'ILIKE', "%{$search}%");
                });
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->dateRange($request->start_date, $request->end_date);
            }

            if ($request->has('user_id')) {
                $query->byUser($request->user_id);
            }

            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            if ($request->has('date')) {
                $query->whereDate('date', $request->date);
            }

            foreach ($request->all() as $key => $value) {
                if (
                    Schema::hasColumn('attendances', $key) &&
                    !in_array($key, ['search', 'sortBy', 'sortOrder', 'per_page', 'all', 'start_date', 'end_date', 'user_id', 'status', 'date'])
                ) {
                    $query->where($key, $value);
                }
            }

            if (Schema::hasColumn('attendances', $sortBy)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $all = $request->input('all', false);
            $pagination = [];
            if ($all) {
                $attendances = $query->get();
            } else {
                $attendances = $query->paginate($perPage)->appends([
                    'search' => $search,
                    'perPage' => $perPage
                ]);
                $pagination = [
                    'current_page' => $attendances->currentPage(),
                    'last_page' => $attendances->lastPage(),
                    'per_page' => $attendances->perPage(),
                    'total' => $attendances->total()
                ];
                $attendances = $attendances->items();
            }

            return response()->json([
                'success' => true,
                'data' => $attendances,
                'message' => 'Asistencias obtenidas exitosamente',
                'pagination' => $pagination
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las asistencias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'check_in' => 'nullable|date_format:H:i:s',
                'check_out' => 'nullable|date_format:H:i:s',
                'dni_key' => 'nullable|string|max:20',
                'status' => 'required|in:PRESENTE,AUSENTE,TARDANZA',
            ], [
                'user_id.required' => 'El ID del usuario es obligatorio',
                'user_id.exists' => 'El usuario no existe',
                'date.required' => 'La fecha es obligatoria',
                'date.date' => 'La fecha no tiene un formato válido',
                'status.required' => 'El estado es obligatorio',
                'status.in' => 'El estado debe ser PRESENTE, AUSENTE o TARDANZA',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar si ya existe asistencia para ese usuario en esa fecha
            $attendanceExistente = Attendace::withTrashed()
                ->where('user_id', $request->user_id)
                ->whereDate('date', $request->date)
                ->first();

            if ($attendanceExistente) {
                if ($attendanceExistente->trashed()) {
                    $attendanceExistente->restore();
                    $attendanceExistente->update($request->all());

                    return response()->json([
                        'success' => true,
                        'data' => $attendanceExistente->load('user:id,name,dni'),
                        'message' => 'Asistencia restaurada y actualizada exitosamente'
                    ], 200);
                } else {
                    // Ya existe asistencia activa para ese día
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe una asistencia registrada para este usuario en esta fecha',
                        'errors' => [
                            'date' => ['El usuario ya tiene asistencia registrada para esta fecha']
                        ]
                    ], 422);
                }
            }

            // Crear nueva asistencia
            $attendance = Attendace::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $attendance, //, //->load('user:id,name,dni'),
                'message' => 'Asistencia registrada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $attendance = Attendace::with('user:id,name,dni')->find($id);

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asistencia no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $attendance,
                'message' => 'Asistencia obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $attendance = Attendace::find($id);

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asistencia no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|required|exists:users,id',
                'date' => 'sometimes|required|date',
                'check_in' => 'nullable|date_format:H:i:s',
                'check_out' => 'nullable|date_format:H:i:s',
                'dni_key' => 'nullable|string|max:20',
                'status' => 'sometimes|required|in:PRESENTE,AUSENTE,TARDANZA',
            ], [
                'user_id.exists' => 'El usuario no existe',
                'date.date' => 'La fecha no tiene un formato válido',
                'status.in' => 'El estado debe ser PRESENTE, AUSENTE o TARDANZA',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            // Verificar duplicado si se cambia user_id o date
            if ($request->has('user_id') || $request->has('date')) {
                $userId = $request->user_id ?? $attendance->user_id;
                $date = $request->date ?? $attendance->date;

                $duplicado = Attendace::where('user_id', $userId)
                    ->whereDate('date', $date)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($duplicado) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe una asistencia para este usuario en esta fecha',
                        'errors' => ['date' => ['Conflicto con asistencia existente']]
                    ], 422);
                }
            }

            $attendance->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $attendance, //->load('user:id,name,dni'),
                'message' => 'Asistencia actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $attendance = Attendace::find($id);

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asistencia no encontrada'
                ], 404);
            }

            $attendance->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'Asistencia eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
