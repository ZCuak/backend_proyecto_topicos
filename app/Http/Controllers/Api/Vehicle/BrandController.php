<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BrandController extends Controller
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

            $query = Brand::query();
            $columns = Schema::getColumnListing('brands');

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
                $brands = $query->orderBy('id', 'asc')->get();

                return response()->json([
                    'success' => true,
                    'data' => $brands,
                    'message' => 'Marcas de vehículo obtenidos exitosamente (todos los registros)',
                    'pagination' => null
                ], 200);
            } else {
                $brands = $query->orderBy('id', 'asc')->paginate($perPage)->appends([
                    'search' => $search,
                    'perPage' => $perPage
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $brands->items(),
                    'message' => 'Marcas de vehículo obtenidos exitosamente',
                    'pagination' => [
                        'current_page' => $brands->currentPage(),
                        'last_page' => $brands->lastPage(),
                        'per_page' => $brands->perPage(),
                        'total' => $brands->total()
                    ]
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener marcas de vehículo',
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
            'name' => 'required|string|max:100|unique:brands,name',
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
            $logo = "";

            $brand = Brand::create($request->all());

            if ($request->hasFile('logo')) {

                // Guardar nuevo logo
                $path = $request->file('logo')->store('brand_logos', 'public');
                $brand->logo = Storage::url($path);
            }
            $brand->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $brand,
                'message' => 'Marca de vehículo creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear marca de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $brand = Brand::find($id);

            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marca no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $brand,
                'message' => 'Marca de vehículo obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener marca de vehículo',
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
            $brand = Brand::find($id);


            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marca no encontrada'
                ], 404);
            }
            if ($request->isMethod('put') && empty($request->all())) {
                $request = Request::createFromBase(\Symfony\Component\HttpFoundation\Request::createFromGlobals());
            }



            // 🔹 Validación
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:100|unique:brands,name,' . $id,
                'logo' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // 🔹 Actualizar nombre si viene en el request
            if ($request->filled('name')) {
                $brand->name = $request->input('name');
            }
            // Eliminar logo anterior si existe
            if ($brand->logo) {
                $oldPath = str_replace('/storage/', '', $brand->logo);
                Storage::disk('public')->delete($oldPath);
            }
            // 🔹 Subir nuevo logo si se envía
            if ($request->hasFile('logo')) {


                // Guardar nuevo logo
                $path = $request->file('logo')->store('brand_logos', 'public');
                $brand->logo = Storage::url($path);
            }

            $brand->save();

            return response()->json([
                'success' => true,
                'data' => $brand,
                'message' => 'Marca de vehículo actualizada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar marca de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(int $id): JsonResponse
    {
        try {
            $brand = Brand::find($id);

            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marca no encontrado'
                ], 404);
            }

            $brand->delete();

            return response()->json([
                'success' => true,
                'message' => 'Marca de vehículo eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar marca de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
