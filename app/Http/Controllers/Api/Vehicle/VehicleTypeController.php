<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class VehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $sortBy = $request->input('sortBy', 'id');
            $sortOrder = $request->input('sortOrder', 'asc');

            $query = VehicleType::query();

            if ($search) {
                $columns = Schema::getColumnListing('vehicletypes');
                $excluir = ['id', 'created_at', 'updated_at', 'deleted_at'];
                $columns = array_diff($columns, $excluir);
                $query->when(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            // Aplicar filtros exactos por campo (si vienen en el request)
            foreach ($request->all() as $key => $value) {
                if (Schema::hasColumn('vehicletypes', $key) && $key !== 'search' && $key !== 'sortBy' && $key !== 'sortOrder' && $key !== 'per_page' && $key !== 'all') {
                    $query->where($key, $value);
                }
            }

            // 🔌 Ordenamiento
            if (Schema::hasColumn('vehicletypes', $sortBy)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // 📄 Paginación o todos los registros
            $all = $request->input('all', false);
            $pagination = [];
            if ($all) {
                $vehicleTypes = $query->get();
            } else {
                $vehicleTypes = $query->paginate($perPage)->appends([
                    'search' => $search,
                    'perPage' => $perPage
                ]);
                $pagination = [
                    'current_page' => $vehicleTypes->currentPage(),
                    'last_page' => $vehicleTypes->lastPage(),
                    'per_page' => $vehicleTypes->perPage(),
                    'total' => $vehicleTypes->total()
                ];
                $vehicleTypes = $vehicleTypes->items();
            }

            return response()->json([
                'success' => true,
                'data' => $vehicleTypes,
                'message' => 'Tipos de vehículos obtenidos exitosamente',
                'pagination' => $pagination
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los tipos de vehículos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar si existe un tipo con el mismo nombre (incluyendo eliminados)
            $vehicleTypeExistente = VehicleType::withTrashed()
                ->where('name', $request->name)
                ->first();

            if ($vehicleTypeExistente) {
                if ($vehicleTypeExistente->trashed()) {
                    // Si existe pero fue eliminado, restaurarlo y actualizar datos
                    $vehicleTypeExistente->restore();
                    $vehicleTypeExistente->update($request->all());

                    return response()->json([
                        'success' => true,
                        'data' => $vehicleTypeExistente->fresh(),
                        'message' => 'Tipo de vehículo restaurado y actualizado exitosamente'
                    ], 200);
                } else {
                    // Si existe y no está eliminado, retornar error
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe un tipo de vehículo con este nombre',
                        'errors' => ['name' => ['El nombre ya está registrado']]
                    ], 422);
                }
            }

            // Si no existe, crear nuevo tipo de vehículo
            $vehicleType = VehicleType::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $vehicleType,
                'message' => 'Tipo de vehículo creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el tipo de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene los datos de un tipo de vehículo específico por su ID.
     * 
     * @param string $id ID único del tipo de vehículo a consultar
     * 
     * @return JsonResponse Respuesta JSON con:
     *   - success: boolean indicando si la operación fue exitosa
     *   - data: Objeto del tipo de vehículo encontrado (null si no existe)
     *   - message: Mensaje descriptivo del resultado
     * 
     * @throws 404 Si el tipo de vehículo no existe en la base de datos
     */
    public function show(string $id): JsonResponse
    {
        try {
            $vehicleType = VehicleType::find($id);

            if (!$vehicleType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de vehículo no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $vehicleType,
                'message' => 'Tipo de vehículo obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el tipo de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $vehicleType = VehicleType::find($id);

            if (!$vehicleType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de vehículo no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:100|unique:vehicletypes,name,' . $id,
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vehicleType->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $vehicleType,
                'message' => 'Tipo de vehículo actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el tipo de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $vehicleType = VehicleType::find($id);

            if (!$vehicleType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de vehículo no encontrado'
                ], 404);
            }

            $vehicleType->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'Tipo de vehículo eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el tipo de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
