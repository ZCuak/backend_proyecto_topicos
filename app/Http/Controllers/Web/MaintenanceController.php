<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    /**
     * Mostrar listado de mantenimientos
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $query = Maintenance::query();

            if ($search) {
                $query->where('name', 'ILIKE', "%{$search}%");
            }

            $maintenances = $query->orderBy('start_date', 'desc')->paginate(10);
            return view('maintenances.index', compact('maintenances', 'search'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar mantenimientos: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de creación (Turbo modal)
     */
    public function create(Request $request)
    {
        $maintenance = new Maintenance();

        return response()
            ->view('maintenances._modal_create', compact('maintenance'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Formulario de edición (Turbo modal)
     */
    public function edit($id)
    {
        $maintenance = Maintenance::findOrFail($id);

        return response()
            ->view('maintenances._modal_edit', compact('maintenance'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Guardar nuevo registro
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
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

            $overlap = Maintenance::where(function ($q) use ($data) {
                $q->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                    ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                    ->orWhere(function ($q2) use ($data) {
                        $q2->where('start_date', '<=', $data['start_date'])
                            ->where('end_date', '>=', $data['end_date']);
                    });
            })->exists();

            if ($overlap) {
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Solapamiento de fechas.',
                        'errors' => ['name' => ['Ya existe un mantenimiento entre las fechas seleccionadas']]
                    ], 422);
                }
                // return back()->with('error', 'Las fechas se solapan con otro mantenimiento existente.')->withInput();
            }

            Maintenance::create($data);
            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mantenimiento registrado exitosamente.',
                ], 201);
            }

            return redirect()->route('maintenances.index')
                ->with('success', 'Mantenimiento registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear mantenimiento: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al crear mantenimiento: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar datos
     */
    public function update(Request $request, $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        try {
            $maintenance = Maintenance::find($id);
            if (!$maintenance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mantenimiento no encontrado.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();
            $overlap = Maintenance::where('id', '!=', $id)
                ->where(function ($q) use ($data) {
                    $q->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                        ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                        ->orWhere(function ($q2) use ($data) {
                            $q2->where('start_date', '<=', $data['start_date'])
                                ->where('end_date', '>=', $data['end_date']);
                        });
                })->exists();

            if ($overlap) {
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Solapamiento de fechas.',
                        'errors' => ['name' => ['Ya existe un mantenimiento entre las fechas seleccionadas']]
                    ], 422);
                }
                // return back()->with('error', 'Las fechas se solapan con otro mantenimiento existente.')->withInput();
            }

            $data = $validator->validated();
            $maintenance->update($data);

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Datos del mantenimiento actualizados correctamente.',
                ], 200);
            }

            return redirect()->route('maintenances.index')
                ->with('success', 'Datos del mantenimiento actualizados correctamente.');
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
            $maintenance = Maintenance::find($id);
            if (!$maintenance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mantenimiento no encontrado.',
                ], 404);
            }

            // No permitir eliminar si existen horarios asociados
            if ($maintenance->schedules()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el mantenimiento porque tiene horarios asociados.',
                ], 400);
            }

            $maintenance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mantenimiento eliminado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar mantenimiento: ' . $e->getMessage(),
            ], 500);
        }
    }
}
