<?php

namespace App\Http\Controllers\Api\Vehicle;

use App\Http\Controllers\Controller;
use App\Models\VehicleColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class VehicleColorController extends Controller
{
    /**
     * Mostrar listado de colores (vista web)
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);

            $query = VehicleColor::query();
            $columns = Schema::getColumnListing('vehiclecolors');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            $colors = $query->orderBy('id', 'asc')->paginate($perPage)->appends(['search' => $search]);

            return view('vehiclecolors.index', compact('colors', 'search'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar colores: ' . $e->getMessage());
        }
    }

    /**
     * Crear un nuevo color
     */
    /**
     * Guardar nuevo color (soporta Turbo frames y peticiones normales)
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:vehiclecolors,name',
            'rgb_code' => 'required|string|max:10'
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
            VehicleColor::create($validator->validated());
            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Color registrado correctamente.',
                ], 201);
            }

            return redirect()->route('vehiclecolors.index')->with('success', 'Color registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear color: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al crear color: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar un color específico
     */
    /**
     * Mostrar detalle de un color (vista)
     */
    public function show(int $id)
    {
        try {
            $color = VehicleColor::find($id);

            if (!$color) {
                return back()->with('error', 'Color no encontrado');
            }

            return view('vehiclecolors.show', compact('color'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al obtener color: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar un color
     */
    /**
     * Actualizar color (soporta Turbo frames y peticiones normales)
     */
    public function update(Request $request, int $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        try {
            $color = VehicleColor::find($id);
            if (!$color) {
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => 'Color no encontrado.'], 404);
                }
                return back()->with('error', 'Color no encontrado.');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:vehiclecolors,name,' . $id,
                'rgb_code' => 'required|string|max:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $color->update($validator->validated());

            if ($isTurbo) {
                return response()->json(['success' => true, 'message' => 'Color actualizado correctamente.'], 200);
            }

            return redirect()->route('vehiclecolors.index')->with('success', 'Color actualizado correctamente.');
        } catch (\Exception $e) {
            if ($isTurbo) {
                return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un color (soft delete)
     */
    /**
     * Eliminar (soft delete)
     */
    public function destroy(Request $request, int $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        try {
            $color = VehicleColor::find($id);

            if (!$color) {
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => 'Color no encontrado.'], 404);
                }
                return back()->with('error', 'Color no encontrado.');
            }

            $color->delete();

            if ($isTurbo) {
                return response()->json(['success' => true, 'message' => 'Color eliminado correctamente.'], 200);
            }

            return redirect()->route('vehiclecolors.index')->with('success', 'Color eliminado correctamente.');
        } catch (\Exception $e) {
            if ($isTurbo) {
                return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de creación (Turbo modal)
     */
    public function create(Request $request)
    {
        $color = new VehicleColor();

        return response()
            ->view('vehiclecolors._modal_create', compact('color'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Formulario de edición (Turbo modal)
     */
    public function edit($id)
    {
        $color = VehicleColor::findOrFail($id);

        return response()
            ->view('vehiclecolors._modal_edit', compact('color'))
            ->header('Turbo-Frame', 'modal-frame');
    }
}
