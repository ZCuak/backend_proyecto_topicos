<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceRecord;
use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


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
            $query = MaintenanceSchedule::with(['maintenance', 'vehicle', 'user']);

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
        $maintenances = Maintenance::all();
        $vehicles = Vehicle::all();
        $users = User::all();

        return response()
            ->view('maintenance_schedules._modal_create', compact('schedule', 'maintenances', 'vehicles', 'users'))
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
        $users = User::all();

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
            'user_id' => 'required|exists:users,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required',
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
                ], 500);
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $data = $validator->validated();

            // Validar solapamiento
            $overlap = MaintenanceSchedule::where('vehicle_id', $data['vehicle_id'])
                ->where('day', $data['day'])
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->exists();

            if ($overlap) {
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Existe un solapamiento entre horarios del vehículo.',
                    ], 500);
                }
                return back()->with('error', 'Existe un solapamiento entre horarios del vehículo.')->withInput();
            }

            // 1. Crear el Schedule
            $schedule = MaintenanceSchedule::create($data);

            // 2. Obtener el mantenimiento al cual pertenece
            $maintenance = Maintenance::find($data['maintenance_id']);

            // 3. Determinar el mes y año de la programación
            $startMonth = Carbon::parse($maintenance->start_date);
            $endMonth = Carbon::parse($maintenance->end_date);

            // Solo se genera para el MES del mantenimiento
            $month = $startMonth->month;
            $year  = $startMonth->year;

            // 4. Convertir el día español a número (Carbon::dayOfWeek)
            $daysMap = [
                'LUNES' => 1,
                'MARTES' => 2,
                'MIÉRCOLES' => 3,
                'JUEVES' => 4,
                'VIERNES' => 5,
                'SÁBADO' => 6,
            ];

            $dayNumber = $daysMap[$data['day']];

            // 5. Recorrer todos los días del mes
            $date = Carbon::create($year, $month, 1);
            $endOfMonth = $date->copy()->endOfMonth();

            while ($date->lte($endOfMonth)) {
                if ($date->dayOfWeek === $dayNumber) {
                    MaintenanceRecord::create([
                        'schedule_id' => $schedule->id,
                        'date' => $date->format('Y-m-d'),
                        'description' => '-',
                        'image_path' => null,
                        'status' => 'NO',
                    ]);
                }
                $date->addDay();
            }

            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Horario registrado exitosamente con sus detalles generados.',
                ], 201);
            }

            return redirect()->route('maintenance-schedules.index')
                ->with('success', 'Horario registrado exitosamente con sus detalles.');
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
                'user_id' => 'required|exists:users,id',
                'vehicle_id' => 'required|exists:vehicles,id',
                'type' => 'required',
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

            // Validar solapamiento al actualizar (excluir el propio registro)
            $overlap = MaintenanceSchedule::where('vehicle_id', $data['vehicle_id'])
                ->where('day', $data['day'])
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->where('id', '<>', $schedule->id)
                ->exists();

            if ($overlap) {
                return response()->json([
                    'success' => false,
                    'message' => 'El horario se solapa con otro existente para el mismo vehículo y día.',
                ], 422);
            }

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

            
           $schedule->records()->delete();

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