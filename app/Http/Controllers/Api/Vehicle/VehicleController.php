<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    /**
     * Listar vehículos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            $query = Vehicle::with(['brand', 'model', 'type', 'color']);
            $columns = Schema::getColumnListing('vehicles');

            // 🔍 Búsqueda general
            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                })
                ->orWhereHas('brand', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"))
                ->orWhereHas('model', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"))
                ->orWhereHas('type', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"))
                ->orWhereHas('color', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"));
            }

            if ($all) {
                $vehicles = $query->orderBy('id', 'asc')->get();
                return response()->json([
                    'success' => true,
                    'data' => $vehicles,
                    'message' => 'Vehículos obtenidos exitosamente (todos)',
                    'pagination' => null
                ]);
            }

            $vehicles = $query->orderBy('id', 'asc')->paginate($perPage)->appends([
                'search' => $search,
                'perPage' => $perPage
            ]);

            return response()->json([
                'success' => true,
                'data' => $vehicles->items(),
                'message' => 'Vehículos obtenidos exitosamente',
                'pagination' => [
                    'current_page' => $vehicles->currentPage(),
                    'last_page' => $vehicles->lastPage(),
                    'per_page' => $vehicles->perPage(),
                    'total' => $vehicles->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar vehículos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo vehículo
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:vehicles,code',
            'plate' => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9-]+$/', 'unique:vehicles,plate'],
            'year' => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'occupant_capacity' => 'nullable|integer|min:0',
            'load_capacity' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
            'status' => 'in:DISPONIBLE,OCUPADO,MANTENIMIENTO,INACTIVO',
            'brand_id' => 'required|exists:brands,id',
            'model_id' => 'required|exists:brandmodels,id',
            'type_id' => 'required|exists:vehicletypes,id',
            'color_id' => 'required|exists:vehiclecolors,id'
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
            $vehicle = Vehicle::create($validator->validated());
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $vehicle->load(['brand', 'model', 'type', 'color']),
                'message' => 'Vehículo registrado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar vehículo específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $vehicle = Vehicle::with(['brand', 'model', 'type', 'color'])->find($id);

            if (!$vehicle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vehículo no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $vehicle,
                'message' => 'Vehículo obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar vehículo
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $vehicle = Vehicle::find($id);
            if (!$vehicle) {
                return response()->json(['success' => false, 'message' => 'Vehículo no encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:150',
                'code' => 'sometimes|required|string|max:50|unique:vehicles,code,' . $id,
                'plate' => ['sometimes', 'required', 'string', 'max:10', 'regex:/^[A-Z0-9-]+$/', 'unique:vehicles,plate,' . $id],
                'year' => 'sometimes|required|integer|min:1990|max:' . (date('Y') + 1),
                'occupant_capacity' => 'nullable|integer|min:0',
                'load_capacity' => 'nullable|integer|min:0',
                'description' => 'nullable|string|max:500',
                'status' => 'in:DISPONIBLE,OCUPADO,MANTENIMIENTO,INACTIVO',
                'brand_id' => 'exists:brands,id',
                'model_id' => 'exists:brandmodels,id',
                'type_id' => 'exists:vehicletypes,id',
                'color_id' => 'exists:vehiclecolors,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $validator->errors()], 422);
            }

            $vehicle->update($validator->validated());

            return response()->json([
                'success' => true,
                'data' => $vehicle->load(['brand', 'model', 'type', 'color']),
                'message' => 'Vehículo actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar vehículo', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar vehículo (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $vehicle = Vehicle::find($id);

            if (!$vehicle) {
                return response()->json(['success' => false, 'message' => 'Vehículo no encontrado'], 404);
            }

            $vehicle->delete();

            return response()->json(['success' => true, 'message' => 'Vehículo eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar vehículo', 'error' => $e->getMessage()], 500);
        }
    }
}
