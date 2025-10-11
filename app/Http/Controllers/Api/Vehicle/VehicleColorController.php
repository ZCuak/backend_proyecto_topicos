<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\VehicleColor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class VehicleColorController extends Controller
{
    /**
     * Listar colores de vehículos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            $query = VehicleColor::query();
            $columns = Schema::getColumnListing('vehiclecolors');

            // 🔍 Filtro de búsqueda en todas las columnas
            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            // 🔹 Retornar todos o paginados
            if ($all) {
                $colors = $query->orderBy('id', 'asc')->get();

                return response()->json([
                    'success' => true,
                    'data' => $colors,
                    'message' => 'Colores de vehículo obtenidos exitosamente (todos los registros)',
                    'pagination' => null
                ], 200);
            } else {
                $colors = $query->orderBy('id', 'asc')->paginate($perPage)->appends([
                    'search' => $search,
                    'perPage' => $perPage
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $colors->items(),
                    'message' => 'Colores de vehículo obtenidos exitosamente',
                    'pagination' => [
                        'current_page' => $colors->currentPage(),
                        'last_page' => $colors->lastPage(),
                        'per_page' => $colors->perPage(),
                        'total' => $colors->total()
                    ]
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener colores de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo color
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:vehiclecolors,name',
            'rgb_code' => 'required|string|max:10'
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
            $color = VehicleColor::create($validator->validated());
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $color,
                'message' => 'Color de vehículo creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear color de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un color específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $color = VehicleColor::find($id);

            if (!$color) {
                return response()->json([
                    'success' => false,
                    'message' => 'Color no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $color,
                'message' => 'Color de vehículo obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener color de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un color
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $color = VehicleColor::find($id);

            if (!$color) {
                return response()->json([
                    'success' => false,
                    'message' => 'Color no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:100|unique:vehiclecolors,name,' . $id,
                'rgb_code' => 'sometimes|required|string|max:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $color->update($validator->validated());

            return response()->json([
                'success' => true,
                'data' => $color,
                'message' => 'Color de vehículo actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar color de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un color (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $color = VehicleColor::find($id);

            if (!$color) {
                return response()->json([
                    'success' => false,
                    'message' => 'Color no encontrado'
                ], 404);
            }

            $color->delete();

            return response()->json([
                'success' => true,
                'message' => 'Color de vehículo eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar color de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
