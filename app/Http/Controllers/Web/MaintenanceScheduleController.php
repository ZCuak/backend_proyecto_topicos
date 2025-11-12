<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MaintenanceScheduleController extends Controller
{
    /**
     * Mostrar listado de horarios de mantenimiento
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $query = MaintenanceSchedule::with(['maintenance', 'vehicle']);

            if ($search) {
                $query->whereHas('maintenance', function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%");
                });
            }

            $schedules = $query->orderBy('day')->orderBy('start_time')->paginate(10);
            return view('maintenance_schedules.index', compact('schedules', 'search'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar horarios: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de creación (Turbo modal)
     */
    public function create(Request $request)
    {
        $schedule = new MaintenanceSchedule();
        $maintenances = Maintenance::all();
        $vehicles = Vehicle::all();

        return response()
            ->view('maintenance_schedules._modal_create', compact('schedule', 'maintenances', 'vehicles'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Formulario de edición (Turbo modal)
     */
    public function edit($id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        $maintenances = Maintenance::all();
        $vehicles = Vehicle::all();

        return response()
            ->view('maintenance_schedules._modal_edit', compact('schedule', 'maintenances', 'vehicles'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Guardar nuevo registro
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'maintenance_id' => 'required|exists:maintenances,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:LIMPIEZA,REPARACIÓN',
            'day' => 'required|in:LUNES,MARTES,MIÉRCOLES,JUEVES,VIERNES,SÁBADO',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
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
            MaintenanceSchedule::create($data);

            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Horario registrado exitosamente.',
                ], 201);
            }

            return redirect()->route('maintenance-schedules.index')
                ->with('success', 'Horario registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear horario: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al crear horario: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar datos
     */
    public function update(Request $request, $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        try {
            $schedule = MaintenanceSchedule::find($id);
            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Horario no encontrado.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'maintenance_id' => 'required|exists:maintenances,id',
                'vehicle_id' => 'required|exists:vehicles,id',
                'type' => 'required|in:LIMPIEZA,REPARACIÓN',
                'day' => 'required|in:LUNES,MARTES,MIÉRCOLES,JUEVES,VIERNES,SÁBADO',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();
            $schedule->update($data);

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Datos del horario actualizados correctamente.',
                ], 200);
            }

            return redirect()->route('maintenance-schedules.index')
                ->with('success', 'Datos del horario actualizados correctamente.');
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
            $schedule = MaintenanceSchedule::find($id);
            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Horario no encontrado.',
                ], 404);
            }

            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Horario eliminado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar horario: ' . $e->getMessage(),
            ], 500);
        }
    }
}