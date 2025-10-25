<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            // $perPage = $request->input('per_page', 10);
            // $sortBy = $request->input('sortBy', 'id');
            // $sortOrder = $request->input('sortOrder', 'asc');

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
            // foreach ($request->all() as $key => $value) {
            //     if (Schema::hasColumn('vehicletypes', $key) && $key !== 'search' && $key !== 'sortBy' && $key !== 'sortOrder' && $key !== 'per_page' && $key !== 'all') {
            //         $query->where($key, $value);
            //     }
            // }

            // Ordenamiento
            $query->orderBy('name', 'asc');

            $vehicletypes = $query->paginate(10);

            return view('vehicletypes.index', compact('vehicletypes', 'search'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar tipos de vehículos: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vehicletype = new VehicleType();

        // Retornar modal con header Turbo
        return response()
            ->view('vehicletypes._modal_create', compact('vehicletype'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
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

        DB::beginTransaction();
        try {

            // Verificar si existe un tipo con el mismo nombre (incluyendo eliminados)
            $vehicleTypeExistente = VehicleType::withTrashed()
                ->where('name', $request->name)
                ->first();

            if ($vehicleTypeExistente) {
                // Si existe pero fue eliminado, restaurarlo y actualizar datos
                if ($vehicleTypeExistente->trashed()) {
                    $vehicleTypeExistente->restore();
                    $vehicleTypeExistente->update($request->all());
                    DB::commit();

                    if ($isTurbo) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Tipo de vehículo restaurado exitosamente.',
                        ], 200);
                    }

                    return redirect()->route('vehicletypes.index')
                        ->with('success', 'Tipo de vehículo restaurado exitosamente.');
                } else {
                    // Si existe y no está eliminado, retornar error
                    DB::rollBack();
                    if ($isTurbo) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Ya existe un tipo de vehículo con este nombre',
                            'errors' => ['name' => ['El nombre ya está registrado']]
                        ], 422);
                    }

                    return back()
                        ->withErrors(['name' => 'Ya existe un tipo de vehículo con este nombre'])
                        ->withInput();
                }
            }

            // Si no existe, crear nuevo tipo de vehículo
            $vehicleType = VehicleType::create($request->all());
            DB::commit();

            // Respuesta dual
            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tipo de vehículo registrado exitosamente.',
                ], 201);
            }

            return redirect()->route('vehicletypes.index')
                ->with('success', 'Tipo de vehículo registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear tipo de vehículo: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al crear tipo de vehículo: ' . $e->getMessage());
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
        $vehicletype = VehicleType::findOrFail($id);

        return response()
            ->view('vehicletypes._modal_edit', compact('vehicletype'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        try {
            $vehicleType = VehicleType::find($id);

            if (!$vehicleType) {
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tipo de vehículo no encontrado.',
                    ], 404);
                }
                return back()->with('error', 'Tipo de vehículo no encontrado.');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:100|unique:vehicletypes,name,' . $id,
                'description' => 'nullable|string',
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

            $vehicleType->update($request->all());

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tipo de vehículo actualizado correctamente.',
                ], 200);
            }

            return redirect()->route('vehicletypes.index')
                ->with('success', 'Tipo de vehículo actualizado correctamente.');
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

            $vehiculo = $vehicleType->vehicles()->count();
            if ($vehiculo > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el tipo: tiene ' . $vehiculo . ' vehículo(s) asociado(s).',
                    'errors' => [
                        'vehicles' => [
                            'Debes reasignar o eliminar los vehículos asociados primero.'
                        ]
                    ]
                ], 422);
            }


            $vehicleType->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'Tipo de vehículo eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
