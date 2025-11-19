<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\Maintenance;
use App\Models\MaintenanceRecord;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
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
            $maintenanceId = $request->input('maintenance_id');
            $query = MaintenanceSchedule::with(['maintenance', 'vehicle']);

            if ($search) {
                $query->whereHas('maintenance', function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%");
                });
            }

            if ($maintenanceId) {
                $query->where('maintenance_id', $maintenanceId);
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
        $preselectedMaintenanceId = $request->query('maintenance_id');
        $maintenances = Maintenance::all();
        $vehicles = Vehicle::all();
        $users = User::orderBy('firstname')->orderBy('lastname')->get();

        return response()
            ->view('maintenance_schedules._modal_create', compact('schedule', 'maintenances', 'vehicles', 'users', 'preselectedMaintenanceId'))
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
        $users = User::orderBy('firstname')->orderBy('lastname')->get();

        return response()
            ->view('maintenance_schedules._modal_edit', compact('schedule', 'maintenances', 'vehicles', 'users'))
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
            'responsible_id' => 'required|exists:users,id', // 🎯 NUEVO
            'type' => 'required|in:PREVENTIVO,LIMPIEZA,REPARACIÓN', // 🎯 ACTUALIZADO
            'day' => 'required|in:LUNES,MARTES,MIÉRCOLES,JUEVES,VIERNES,SÁBADO,DOMINGO', // 🎯 ACTUALIZADO
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

            // Validar solapamiento por vehículo y día
            $overlap = MaintenanceSchedule::where('vehicle_id', $data['vehicle_id'])
                ->where('day', $data['day'])
                ->where(function ($q) use ($data) {
                    $q->where('start_time', '<', $data['end_time'])
                        ->where('end_time', '>', $data['start_time']);
                })
                ->exists();

            if ($overlap) {
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El horario se solapa con otro existente para el mismo vehículo y día.',
                        'errors' => ['name' => ['Ya existe un mantenimiento entre las horas y vehiculo seleccionadas']]
                    ], 422);
                }

                return back()->with('error', 'El horario se solapa con otro existente para el mismo vehículo y día.')->withInput();
            }

            $schedule = MaintenanceSchedule::create($data);

            // 🎯 AUTO-GENERAR REGISTROS DE DÍAS
            $this->generateMaintenanceRecords($schedule);

            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Horario registrado exitosamente y días generados automáticamente.',
                ], 201);
            }

            return redirect()->route('maintenance-schedules.index')
                ->with('success', 'Horario registrado exitosamente y días generados automáticamente.');
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

    private function generateMaintenanceRecords(MaintenanceSchedule $schedule)
    {
        $maintenance = $schedule->maintenance;
        $start = Carbon::parse($maintenance->start_date);
        $end = Carbon::parse($maintenance->end_date);

        // Mapeo de días
        $daysMap = [
            'LUNES' => 1,
            'MARTES' => 2,
            'MIÉRCOLES' => 3,
            'MIERCOLES' => 3,
            'JUEVES' => 4,
            'VIERNES' => 5,
            'SÁBADO' => 6,
            'SABADO' => 6,
            'DOMINGO' => 7
        ];

        $targetDayOfWeek = $daysMap[strtoupper($schedule->day)];

        // Encontrar el primer día que coincida
        $current = $start->copy();
        while ($current->dayOfWeekIso != $targetDayOfWeek && $current->lte($end)) {
            $current->addDay();
        }

        // Generar registros cada semana
        while ($current->lte($end)) {
            MaintenanceRecord::create([
                'schedule_id' => $schedule->id,
                'date' => $current->format('Y-m-d'),
                'description' => 'Pendiente de realizar',
                'completed' => false
            ]);

            $current->addWeek();
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
                'responsible_id' => 'required|exists:users,id', // 🎯 NUEVO
                'type' => 'required|in:PREVENTIVO,LIMPIEZA,REPARACIÓN', // 🎯 ACTUALIZADO
                'day' => 'required|in:LUNES,MARTES,MIÉRCOLES,JUEVES,VIERNES,SÁBADO,DOMINGO', // 🎯 ACTUALIZADO
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

            // 🎯 Validar solapamiento al actualizar (excluir el propio registro)
            $overlap = MaintenanceSchedule::where('vehicle_id', $data['vehicle_id'])
                ->where('day', $data['day'])
                ->where(function ($q) use ($data) {
                    $q->where('start_time', '<', $data['end_time'])
                        ->where('end_time', '>', $data['start_time']);
                })
                ->where('id', '<>', $schedule->id)
                ->exists();

            if ($overlap) {
                return response()->json([
                    'success' => false,
                    'message' => 'El horario se solapa con otro existente para el mismo vehículo y día.',
                    'errors' => ['name' => ['Ya existe un mantenimiento entre las horas y vehiculo seleccionadas']]
                ], 422);
            }

            // 🎯 Si cambiaron día o mantenimiento, regenerar registros
            $shouldRegenerate = (
                $schedule->day !== $data['day'] ||
                $schedule->maintenance_id !== $data['maintenance_id']
            );

            $schedule->update($data);

            if ($shouldRegenerate) {
                // Eliminar registros antiguos
                MaintenanceRecord::where('schedule_id', $schedule->id)->delete();

                // Generar nuevos registros
                $this->generateMaintenanceRecords($schedule);
            }

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

            // 🎯 Eliminar también los registros generados automáticamente
            $recordsCount = $schedule->records()->count();

            $schedule->records()->delete();
            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => "Horario eliminado correctamente junto con {$recordsCount} día(s) generado(s).",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar horario: ' . $e->getMessage(),
            ], 500);
        }
    }
}
