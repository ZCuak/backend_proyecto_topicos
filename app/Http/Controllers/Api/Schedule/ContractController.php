<?php

namespace App\Http\Controllers\Api\Schedule;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

class ContractController extends Controller
{
    /**
     * Listado paginado de contratos con búsqueda, filtros y ordenamiento.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $sortBy = $request->input('sortBy', 'id');
            $sortOrder = $request->input('sortOrder', 'asc');

            $query = Contract::query();

            if ($search) {
                $columns = Schema::getColumnListing('contracts');
                $excluir = ['id', 'created_at', 'updated_at', 'deleted_at'];
                $columns = array_diff($columns, $excluir);

                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'LIKE', "%{$search}%");
                    }
                });
            }

            foreach ($request->all() as $key => $value) {
                if (Schema::hasColumn('contracts', $key) && ! in_array($key, ['search', 'sortBy', 'sortOrder', 'per_page', 'all'])) {
                    $query->where($key, $value);
                }
            }

            if (Schema::hasColumn('contracts', $sortBy)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $all = $request->boolean('all', false);
            $data = $all ? $query->get() : $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Contratos obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener contratos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacena un nuevo contrato.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:nombrado,permanente,eventual',
                'date_start' => 'required|date',
                'date_end' => 'nullable|date|after_or_equal:date_start',
                'description' => 'nullable|string',
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validación 1: Verificar si el usuario ya tiene un contrato activo
            $activeContract = Contract::where('user_id', $request->user_id)
                ->where('is_active', true)
                ->first();

            if ($activeContract) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario ya tiene un contrato activo. No se puede agregar un nuevo contrato.',
                    'active_contract_id' => $activeContract->id
                ], 422);
            }

            // Validación 2: Si es nombrado o permanente, no debe tener fecha de fin
            if (in_array($request->type, ['nombrado', 'permanente'])) {
                if ($request->date_end) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Los contratos de tipo nombrado o permanente no deben tener fecha de fin.'
                    ], 422);
                }
                // Asegurarse que date_end sea null
                $request->merge(['date_end' => null]);
            }

            // Validación 3: Para contratos eventuales, verificar que hayan pasado mínimo 2 meses
            if ($request->type === 'eventual') {
                $lastEventualContract = Contract::where('user_id', $request->user_id)
                    ->where('type', 'eventual')
                    ->whereNotNull('date_end')
                    ->orderBy('date_end', 'desc')
                    ->first();

                if ($lastEventualContract && $lastEventualContract->date_end) {
                    // Calcular los meses entre la fecha de fin del último contrato y la fecha de inicio del nuevo
                    $dateStart = Carbon::parse($request->date_start);
                    $monthsSinceLastContract = $lastEventualContract->date_end->diffInMonths($dateStart);
                    
                    if ($monthsSinceLastContract < 2) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Para contratos eventuales, deben pasar mínimo 2 meses desde el último contrato.',
                            'last_contract_end_date' => $lastEventualContract->date_end->format('Y-m-d'),
                            'new_contract_start_date' => $request->date_start,
                            'months_since_last_contract' => $monthsSinceLastContract,
                            'months_remaining' => 2 - $monthsSinceLastContract
                        ], 422);
                    }
                }

                // Para eventuales, la fecha de fin es requerida
                if (!$request->date_end) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Los contratos eventuales deben tener una fecha de fin.'
                    ], 422);
                }
            }

            $contract = Contract::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $contract,
                'message' => 'Contrato creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear contrato',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra un contrato por ID.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $contract = Contract::find($id);

            if (! $contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrato no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $contract,
                'message' => 'Contrato obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener contrato',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza un contrato existente.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $contract = Contract::find($id);

            if (! $contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrato no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|required|in:nombrado,permanente,eventual',
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

            // Si se está cambiando el usuario, validar que el nuevo usuario no tenga un contrato activo
            if ($request->has('user_id') && $request->user_id != $contract->user_id) {
                $activeContract = Contract::where('user_id', $request->user_id)
                    ->where('is_active', true)
                    ->where('id', '!=', $id)
                    ->first();

                if ($activeContract) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El nuevo usuario ya tiene un contrato activo. No se puede asignar este contrato.',
                        'active_contract_id' => $activeContract->id
                    ], 422);
                }
            }

            $contractType = $request->has('type') ? $request->type : $contract->type;
            $userId = $request->has('user_id') ? $request->user_id : $contract->user_id;

            // Validación: Si es nombrado o permanente, no debe tener fecha de fin
            if (in_array($contractType, ['nombrado', 'permanente'])) {
                if ($request->has('date_end') && $request->date_end) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Los contratos de tipo nombrado o permanente no deben tener fecha de fin.'
                    ], 422);
                }
                // Asegurarse que date_end sea null
                $request->merge(['date_end' => null]);
            }

            // Validación: Para contratos eventuales, verificar que hayan pasado mínimo 2 meses
            if ($contractType === 'eventual') {
                $lastEventualContract = Contract::where('user_id', $userId)
                    ->where('type', 'eventual')
                    ->where('id', '!=', $id)
                    ->whereNotNull('date_end')
                    ->orderBy('date_end', 'desc')
                    ->first();

                if ($lastEventualContract && $lastEventualContract->date_end) {
                    $dateStart = $request->has('date_start') ? \Carbon\Carbon::parse($request->date_start) : $contract->date_start;
                    $monthsSinceLastContract = $lastEventualContract->date_end->diffInMonths($dateStart);
                    
                    if ($monthsSinceLastContract < 2) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Para contratos eventuales, deben pasar mínimo 2 meses desde el último contrato.',
                            'last_contract_end_date' => $lastEventualContract->date_end->format('Y-m-d'),
                            'new_contract_start_date' => $dateStart->format('Y-m-d'),
                            'months_since_last_contract' => $monthsSinceLastContract,
                            'months_remaining' => 2 - $monthsSinceLastContract
                        ], 422);
                    }
                }

                // Para eventuales, la fecha de fin es requerida
                $dateEnd = $request->has('date_end') ? $request->date_end : $contract->date_end;
                if (!$dateEnd) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Los contratos eventuales deben tener una fecha de fin.'
                    ], 422);
                }
            }

            $contract->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $contract->fresh(),
                'message' => 'Contrato actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar contrato',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina (soft delete) un contrato.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $contract = Contract::find($id);

            if (! $contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrato no encontrado'
                ], 404);
            }

            $contract->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contrato eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar contrato',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}