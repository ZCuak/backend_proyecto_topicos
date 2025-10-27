<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Brand;
use App\Models\BrandModel;
use App\Models\VehicleType;
use App\Models\VehicleColor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    /**
     * Listar vehículos (Web)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $query = Vehicle::with(['brand', 'model', 'type', 'color']);

        // Búsqueda general
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('code', 'ILIKE', "%{$search}%")
                  ->orWhere('plate', 'ILIKE', "%{$search}%")
                  ->orWhere('year', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhereHas('brand', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"))
                  ->orWhereHas('model', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"))
                  ->orWhereHas('type', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"))
                  ->orWhereHas('color', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"));
            });
        }

        $vehicles = $query->orderBy('id', 'desc')->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage
        ]);

        return view('vehicles.index', compact('vehicles', 'search'));
    }

    /**
     * Mostrar formulario de creación (Web)
     */
    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        $models = BrandModel::orderBy('name')->get();
        $types = VehicleType::orderBy('name')->get();
        $colors = VehicleColor::orderBy('name')->get();

        return view('vehicles._modal_create', compact('brands', 'models', 'types', 'colors'));
    }

    /**
     * Almacenar nuevo vehículo (Web)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:vehicles,code',
            'plate' => [
                'required', 
                'string', 
                'min:3',
                'max:10', 
                'regex:/^[A-Z0-9\-]{3,10}$/',
                'unique:vehicles,plate'
            ],
            'year' => 'required|integer|min:1990|max:' . date('Y'),
            'occupant_capacity' => 'nullable|integer|min:0',
            'load_capacity' => 'nullable|integer|min:0',
            'compaction_capacity' => 'nullable|integer|min:0',
            'fuel_capacity' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:DISPONIBLE,OCUPADO,MANTENIMIENTO,INACTIVO',
            'brand_id' => 'required|exists:brands,id',
            'model_id' => 'required|exists:brandmodels,id',
            'type_id' => 'required|exists:vehicletypes,id',
            'color_id' => 'required|exists:vehiclecolors,id'
        ], [
            'plate.regex' => 'La placa debe contener solo letras, números y guiones (3-10 caracteres)',
            'plate.unique' => 'Esta placa ya está registrada en otro vehículo',
            'code.unique' => 'Este código ya está registrado en otro vehículo',
            'year.min' => 'El año debe ser mayor o igual a 1990',
            'year.max' => 'El año no puede ser mayor al año actual',
            'brand_id.required' => 'La marca es obligatoria',
            'brand_id.exists' => 'La marca seleccionada no es válida',
            'model_id.required' => 'El modelo es obligatorio',
            'model_id.exists' => 'El modelo seleccionado no es válido',
            'type_id.required' => 'El tipo de vehículo es obligatorio',
            'type_id.exists' => 'El tipo seleccionado no es válido',
            'color_id.required' => 'El color es obligatorio',
            'color_id.exists' => 'El color seleccionado no es válido'
        ]);

        DB::beginTransaction();
        try {
            $vehicle = Vehicle::create($request->all());
            DB::commit();

            return redirect()->route('vehicles.index')
                ->with('success', 'Vehículo registrado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar vehículo: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar vehículo específico (Web)
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['brand', 'model', 'type', 'color']);
        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * Mostrar formulario de edición (Web)
     */
    public function edit(Vehicle $vehicle)
    {
        $brands = Brand::orderBy('name')->get();
        $models = BrandModel::orderBy('name')->get();
        $types = VehicleType::orderBy('name')->get();
        $colors = VehicleColor::orderBy('name')->get();

        return view('vehicles._modal_edit', compact('vehicle', 'brands', 'models', 'types', 'colors'));
    }

    /**
     * Actualizar vehículo (Web)
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:vehicles,code,' . $vehicle->id,
            'plate' => [
                'required', 
                'string', 
                'min:3',
                'max:10', 
                'regex:/^[A-Z0-9\-]{3,10}$/',
                'unique:vehicles,plate,' . $vehicle->id
            ],
            'year' => 'required|integer|min:1990|max:' . date('Y'),
            'occupant_capacity' => 'nullable|integer|min:0',
            'load_capacity' => 'nullable|integer|min:0',
            'compaction_capacity' => 'nullable|integer|min:0',
            'fuel_capacity' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:DISPONIBLE,OCUPADO,MANTENIMIENTO,INACTIVO',
            'brand_id' => 'required|exists:brands,id',
            'model_id' => 'exists:brandmodels,id',
            'type_id' => 'exists:vehicletypes,id',
            'color_id' => 'exists:vehiclecolors,id'
        ], [
            'plate.regex' => 'La placa debe contener solo letras, números y guiones (3-10 caracteres)',
            'plate.unique' => 'Esta placa ya está registrada en otro vehículo',
            'code.unique' => 'Este código ya está registrado en otro vehículo',
            'year.min' => 'El año debe ser mayor o igual a 1990',
            'year.max' => 'El año no puede ser mayor al año actual',
            'brand_id.required' => 'La marca es obligatoria',
            'brand_id.exists' => 'La marca seleccionada no es válida',
            'model_id.required' => 'El modelo es obligatorio',
            'model_id.exists' => 'El modelo seleccionado no es válido',
            'type_id.required' => 'El tipo de vehículo es obligatorio',
            'type_id.exists' => 'El tipo seleccionado no es válido',
            'color_id.required' => 'El color es obligatorio',
            'color_id.exists' => 'El color seleccionado no es válido'
        ]);

        try {
            $vehicle->update($request->all());

            return redirect()->route('vehicles.index')
                ->with('success', 'Vehículo actualizado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar vehículo: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar vehículo (Web)
     */
    public function destroy(Vehicle $vehicle)
    {
        try {
            $vehicle->delete();

            return redirect()->route('vehicles.index')
                ->with('success', 'Vehículo eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar vehículo: ' . $e->getMessage());
        }
    }

    /**
     * Obtener modelos por marca
     */
    public function getModelsByBrand($brandId)
    {
        $models = BrandModel::where('brand_id', $brandId)->orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $models
        ]);
    }

    /**
     * Listar vehículos (API)
     */
    public function apiIndex(Request $request): JsonResponse
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

}
