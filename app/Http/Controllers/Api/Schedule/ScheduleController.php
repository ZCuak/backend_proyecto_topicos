<?php

namespace App\Http\Controllers\Api\Schedule;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    /**
     * Mostrar listado de turnos (vista web)
     */
public function index(Request $request)
{
    try {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);

        $query = Schedule::query();
        $columns = Schema::getColumnListing('schedules');

        if ($search) {
            $query->where(function ($q) use ($columns, $search) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'ILIKE', "%{$search}%");
                }
            });
        }

        $schedules = $query->orderBy('id', 'asc')
            ->paginate($perPage)
            ->appends(['search' => $search]);

        // 🔸 Devuelve JSON solo si es API o Turbo
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $schedules,
                'search' => $search,
            ]);
        }

        // 🔸 Si viene del navegador, renderiza la vista
        return view('schedules.index', compact('schedules', 'search'));

    } catch (\Exception $e) {
        // 🔸 Muestra JSON si es API
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar turnos',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }

        // 🔸 Si es vista normal, muestra alerta
        return back()->with('error', 'Error al listar turnos: ' . $e->getMessage());
    }
}



    /**
     * Crear un nuevo turno (schedule)
     */
    /**
     * Guardar nuevo turno (soporta Turbo frames y peticiones normales)
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:schedules,name',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'description' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            if ($isTurbo) {
                return response()->json(['success' => false, 'message' => 'Errores de validación.', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            Schedule::create($validator->validated());
            DB::commit();

            if ($isTurbo) {
                return response()->json(['success' => true, 'message' => 'Turno registrado correctamente.'], 201);
            }

            return redirect()->route('schedules.index')->with('success', 'Turno registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json(['success' => false, 'message' => 'Error al crear turno: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al crear turno: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar un turno específico
     */
    /**
     * Mostrar detalle de un turno (vista)
     */
    public function show(int $id)
    {
        try {
            $schedule = Schedule::find($id);

            if (!$schedule) {
                return back()->with('error', 'Turno no encontrado');
            }

            return view('schedules.show', compact('schedule'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al obtener turno: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar un turno
     */
    /**
     * Actualizar turno (soporta Turbo frames y peticiones normales)
     */
    public function update(Request $request, int $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        try {
            $schedule = Schedule::find($id);
            if (!$schedule) {
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => 'Turno no encontrado.'], 404);
                }
                return back()->with('error', 'Turno no encontrado.');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:schedules,name,' . $id,
                'time_start' => 'required|date_format:H:i',
                'time_end' => 'required|date_format:H:i|after:time_start',
                'description' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Errores de validación.', 'errors' => $validator->errors()], 422);
            }

            $schedule->update($validator->validated());

            if ($isTurbo) {
                return response()->json(['success' => true, 'message' => 'Turno actualizado correctamente.'], 200);
            }

            return redirect()->route('schedules.index')->with('success', 'Turno actualizado correctamente.');
        } catch (\Exception $e) {
            if ($isTurbo) {
                return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un turno (soft delete)
     */
    /**
     * Eliminar (soft delete)
     */
    public function destroy(Request $request, int $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        try {
            $schedule = Schedule::find($id);

            if (!$schedule) {
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => 'Turno no encontrado.'], 404);
                }
                return back()->with('error', 'Turno no encontrado.');
            }

            $schedule->delete();

            if ($isTurbo) {
                return response()->json(['success' => true, 'message' => 'Turno eliminado correctamente.'], 200);
            }

            return redirect()->route('schedules.index')->with('success', 'Turno eliminado correctamente.');
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
        $schedule = new Schedule();

        return response()->view('schedules._modal_create', compact('schedule'))->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Formulario de edición (Turbo modal)
     */
    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);

        return response()->view('schedules._modal_edit', compact('schedule'))->header('Turbo-Frame', 'modal-frame');
    }
}
