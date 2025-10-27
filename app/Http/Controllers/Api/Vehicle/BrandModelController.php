<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\BrandModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class BrandModelController extends Controller
{
    /**
     * Mostrar listado de brandmodel
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $query = BrandModel::with(['brand']);
            $columns = Schema::getColumnListing('brandmodels');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            $models = $query->paginate(10);
            return view('brandmodels.index', compact('models', 'search'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar modelos: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de creación (Turbo modal)
     */
    public function create(Request $request)
    {
        $brands = Brand::all();
        $model = new BrandModel();

        return response()
            ->view('brandmodels._modal_create', compact('brands', 'model'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Formulario de edición (Turbo modal)
     */
    public function edit($id)
    {
        $model = BrandModel::findOrFail($id);
        $brands = Brand::all();

        return response()
            ->view('brandmodels._modal_edit', compact('model', 'brands'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Guardar nuevo registro
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:brandmodels,name',
            'code' => 'nullable|string|unique:brandmodels,code',
            'description' => 'nullable|string',
            'brand_id' => 'required|exists:brands,id'
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
            $data = $validator->validated();

            $model = BrandModel::create($data);

            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Modelo registrado exitosamente.',
                ], 201);
            }

            return redirect()->route('brand-models.index')
                ->with('success', 'Modelo registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear modelo: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al crear modelo: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar datos
     */
    public function update(Request $request, $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        try {
            $model = BrandModel::find($id);
            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modelo no encontrado.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:brandmodels,name,' . $id,
                'code' => 'nullable|string|unique:brandmodels,code,' . $id,
                'description' => 'nullable|string',
                'brand_id' => 'required|exists:brands,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            $model->update($data);

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Datos del modelo actualizados correctamente.',
                ], 200);
            }

            return redirect()->route('brand-models.index')
                ->with('success', 'Datos del modelo actualizados correctamente.');
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
     * Eliminar (Soft Delete)
     */
    public function destroy($id)
    {
        try {
            $model = BrandModel::find($id);
            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modelo no encontrado.',
                ], 404);
            }

            $model->delete();

            return response()->json([
                'success' => true,
                'message' => 'Modelo eliminado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar modelo: ' . $e->getMessage(),
            ], 500);
        }
    }
}
