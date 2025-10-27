<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class BrandController extends Controller
{
    /**
     * Mostrar listado de brands
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $query = Brand::query();
            $columns = Schema::getColumnListing('brands');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            $brands = $query->paginate(10);
            return view('brands.index', compact('brands', 'search'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar marcas: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de creación (Turbo modal)
     */
    public function create(Request $request)
    {
        $brand = new Brand();

        return response()
            ->view('brands._modal_create', compact('brand'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Formulario de edición (Turbo modal)
     */
    public function edit($id)
    {
        $brand = Brand::findOrFail($id);

        return response()
            ->view('brands._modal_edit', compact('brand'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Guardar nuevo registro
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:brands,name',
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
            $data = $validator->validated();

            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('brand-logos', 'public');
            }

            $brand = Brand::create($data);

            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Marca registrada exitosamente.',
                ], 201);
            }

            return redirect()->route('brands.index')
                ->with('success', 'Marca registrada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear marca: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al crear marca: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar datos
     */
    public function update(Request $request, $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        try {
            $brand = Brand::find($id);
            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marca no encontrado.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:brands,name,' . $id,
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('brand-logos', 'public');
            }

            $brand->update($data);

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Datos de la marca actualizados correctamente.',
                ], 200);
            }

            return redirect()->route('brands.index')
                ->with('success', 'Datos de la marca actualizados correctamente.');
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
            $brand = Brand::find($id);
            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marca no encontrada.',
                ], 404);
            }

            $brand->delete();

            return response()->json([
                'success' => true,
                'message' => 'Marca eliminada correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar marca: ' . $e->getMessage(),
            ], 500);
        }
    }
}
