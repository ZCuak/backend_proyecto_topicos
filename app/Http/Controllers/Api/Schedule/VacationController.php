<?php

namespace App\Http\Controllers\Api\Schedule;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Vacation;
use Carbon\Carbon;
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
                'user_id' => 'required|exists:users,id',
                'year' => 'required|integer|min:1900',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'max_days' => 'required|integer',
                'reason' => 'nullable|string',
                'status' => 'sometimes|in:pendiente,aprobada,rechazada',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Calcular días programados automáticamente (diferencia de fechas + 1)
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $max_days  = $request->max_days;
            $daysProgrammed = $startDate->diffInDays($endDate) + 1; // +1 para incluir ambos días

            // Validar que no supere los 30 días
            if ($daysProgrammed > 30) {
                return response()->json([
                    'success' => false,
                    'message' => 'El período de vacaciones no puede superar los 30 días.',
                    'days_calculated' => $daysProgrammed,
                    'max_allowed' => 30
                ], 422);
            }

            // Validación 1: Verificar que el usuario tenga un contrato activo de tipo nombrado o permanente
            $activeContract = Contract::where('user_id', $request->user_id)
                ->where('is_active', true)
                ->whereIn('type', ['nombrado', 'permanente'])
                ->first();

            if (!$activeContract) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no tiene un contrato activo de tipo nombrado o permanente. Solo estos tipos de contrato pueden solicitar vacaciones.'
                ], 422);
            }

            // Validación 2: Verificar que las fechas NO se solapen con otras vacaciones del mismo usuario
            $overlappingVacations = Vacation::where('user_id', $request->user_id)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                        ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                        ->orWhere(function ($q) use ($request) {
                            $q->where('start_date', '<=', $request->start_date)
                              ->where('end_date', '>=', $request->end_date);
                        });
                })
                ->whereIn('status', ['pendiente', 'aprobada']) // Solo considerar vacaciones pendientes o aprobadas
                ->exists();

            if ($overlappingVacations) {
                return response()->json([
                    'success' => false,
                    'message' => 'Las fechas de vacaciones se solapan con otra solicitud existente (pendiente o aprobada).'
                ], 422);
            }

            $data = $request->only([
                'user_id',
                'year',
                'start_date',
                'end_date',
                'reason',
                'status',
            ]);

            // Establecer valores calculados automáticamente
            $data['days_programmed'] = $daysProgrammed;
            $data['days_pending'] = $max_days - $daysProgrammed;

            // Establecer status por defecto si no se proporciona
            if (!isset($data['status'])) {
                $data['status'] = 'pendiente';
            }

            $vacation = Vacation::create($data);

            return response()->json([
                'success' => true,
                'data' => $vacation,
                'message' => 'Solicitud de vacación creada exitosamente'
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
                'user_id' => 'sometimes|required|exists:users,id',
                'year' => 'sometimes|required|integer|min:1900',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date|after_or_equal:start_date',
                'reason' => 'nullable|string',
                'status' => 'sometimes|in:pendiente,aprobada,rechazada',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->has('user_id') ? $request->user_id : $vacation->user_id;

            // Validación 1: Si se cambia el usuario o se está actualizando, verificar contrato
            if ($request->has('user_id')) {
                $activeContract = Contract::where('user_id', $userId)
                    ->where('is_active', true)
                    ->whereIn('type', ['nombrado', 'permanente'])
                    ->first();

                if (!$activeContract) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El usuario no tiene un contrato activo de tipo nombrado o permanente.'
                    ], 422);
                }
            }

            // Calcular días programados si se actualizan las fechas
            if ($request->has('start_date') || $request->has('end_date')) {
                $startDate = \Carbon\Carbon::parse($request->has('start_date') ? $request->start_date : $vacation->start_date);
                $endDate = \Carbon\Carbon::parse($request->has('end_date') ? $request->end_date : $vacation->end_date);
                $daysProgrammed = $startDate->diffInDays($endDate) + 1;

                // Validar que no supere los 30 días
                if ($daysProgrammed > 30) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El período de vacaciones no puede superar los 30 días.',
                        'days_calculated' => $daysProgrammed,
                        'max_allowed' => 30
                    ], 422);
                }

                // Validación 2: Verificar solapamiento
                $overlappingVacations = Vacation::where('user_id', $userId)
                    ->where('id', '!=', $id) // Excluir la vacación actual
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function ($q) use ($startDate, $endDate) {
                                $q->where('start_date', '<=', $startDate)
                                  ->where('end_date', '>=', $endDate);
                            });
                    })
                    ->whereIn('status', ['pendiente', 'aprobada'])
                    ->exists();

                if ($overlappingVacations) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Las fechas de vacaciones se solapan con otra solicitud existente.'
                    ], 422);
                }
            }

            $data = $request->only([
                'user_id',
                'year',
                'start_date',
                'end_date',
                'reason',
                'status',
            ]);

            // Recalcular days_programmed y days_pending si se actualizan las fechas
            if ($request->has('start_date') || $request->has('end_date')) {
                $data['days_programmed'] = $daysProgrammed;
                $data['days_pending'] = 30 - $daysProgrammed;
            }

            $vacation->update($data);

            return response()->json([
                'success' => true,
                'data' => $vacation->fresh(),
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

    /**
     * Aprobar una solicitud de vacaciones y restar los días programados de los días pendientes.
     */
    public function approve(string $id): JsonResponse
    {
        try {
            $vacation = Vacation::find($id);

            if (! $vacation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacación no encontrada'
                ], 404);
            }

            if ($vacation->status === 'aprobada') {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitud de vacación ya está aprobada'
                ], 422);
            }

            // Verificar que hay suficientes días pendientes
            if ($vacation->days_pending < $vacation->days_programmed) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay suficientes días pendientes para aprobar esta solicitud',
                    'days_pending' => $vacation->days_pending,
                    'days_requested' => $vacation->days_programmed
                ], 422);
            }

            // Aprobar y restar días
            $vacation->status = 'aprobada';
            $vacation->days_pending = $vacation->days_pending - $vacation->days_programmed;
            $vacation->save();

            return response()->json([
                'success' => true,
                'data' => $vacation,
                'message' => 'Vacación aprobada exitosamente. Se han restado ' . $vacation->days_programmed . ' días.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar vacación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar una solicitud de vacaciones.
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        try {
            $vacation = Vacation::find($id);

            if (! $vacation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vacación no encontrada'
                ], 404);
            }

            if ($vacation->status === 'rechazada') {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitud de vacación ya está rechazada'
                ], 422);
            }

            $vacation->status = 'rechazada';
            
            // Opcional: guardar razón del rechazo
            if ($request->has('rejection_reason')) {
                $vacation->reason = $request->rejection_reason;
            }
            
            $vacation->save();

            return response()->json([
                'success' => true,
                'data' => $vacation,
                'message' => 'Vacación rechazada'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar vacación',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}