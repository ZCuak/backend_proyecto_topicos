<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class MaintenanceRecordController extends Controller
{
    /**
     * Mostrar listado de registros de mantenimiento
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $query = MaintenanceRecord::with(['schedule.maintenance', 'schedule.vehicle']);

            if ($search) {
                $query->where('description', 'ILIKE', "%{$search}%")
                      ->orWhereHas('schedule.maintenance', function ($q) use ($search) {
                          $q->where('name', 'ILIKE', "%{$search}%");
                      });
            }

            $records = $query->orderBy('date', 'desc')->paginate(10);
            return view('maintenance_records.index', compact('records', 'search'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar registros: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de creación (Turbo modal)
     */
    public function create(Request $request)
    {
        $record = new MaintenanceRecord();
        $schedules = MaintenanceSchedule::all();

        return response()
            ->view('maintenance_records._modal_create', compact('record', 'schedules'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Formulario de edición (Turbo modal)
     */
    public function edit($id)
    {
        $record = MaintenanceRecord::findOrFail($id);
        $schedules = MaintenanceSchedule::all();

        return response()
            ->view('maintenance_records._modal_edit', compact('record', 'schedules'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Guardar nuevo registro
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:maintenance_schedules,id',
            'date' => 'required|date',
            'description' => 'required|string|max:1000',
            'image_path' => 'nullable|image|max:2048',
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

            // Validar que la fecha corresponda al día de la semana y al periodo del mantenimiento
            $schedule = MaintenanceSchedule::find($data['schedule_id']);
            if (!$schedule) {
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => 'Horario de mantenimiento no encontrado.'], 422);
                }

                return response()->json(['success' => false, 'message' => 'Horario de mantenimiento no encontrado.'], 404);
            }

            $maintenance = $schedule->maintenance;
            if (!$maintenance) {
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => 'Mantenimiento asociado no encontrado.'], 422);
                }

                return response()->json(['success' => false, 'message' => 'Mantenimiento asociado no encontrado.'], 404);
            }

            $date = Carbon::parse($data['date']);

            $daysMap = [
                'LUNES' => 1,
                'MARTES' => 2,
                'MIÉRCOLES' => 3,
                'MIERCOLES' => 3,
                'JUEVES' => 4,
                'VIERNES' => 5,
                'SÁBADO' => 6,
                'SABADO' => 6,
                'DOMINGO' => 7,
            ];

            $expectedWeek = $daysMap[strtoupper($schedule->day)] ?? null;
            $actualWeek = (int) $date->format('N');

            if ($expectedWeek === null || $expectedWeek !== $actualWeek) {
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => 'La fecha debe corresponder al día de la semana del horario: ' . $schedule->day], 422);
                }

                return back()->with('error', 'La fecha debe corresponder al día de la semana del horario: ' . $schedule->day)->withInput();
            }

            $start = Carbon::parse($maintenance->start_date);
            $end = Carbon::parse($maintenance->end_date);
            if ($date->lt($start) || $date->gt($end)) {
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => 'La fecha debe estar dentro del periodo del mantenimiento: ' . $maintenance->start_date . ' - ' . $maintenance->end_date], 422);
                }

                return back()->with('error', 'La fecha debe estar dentro del periodo del mantenimiento: ' . $maintenance->start_date . ' - ' . $maintenance->end_date)->withInput();
            }

            if ($request->hasFile('image_path')) {
                $data['image_path'] = $request->file('image_path')->store('maintenance_records', 'public');
            }

            MaintenanceRecord::create($data);

            DB::commit();

            if ($isTurbo) {
                return response()->json(['success' => true, 'data' => $data, 'message' => 'Registro de mantenimiento creado exitosamente.'], 201);
            }

            return redirect()->route('maintenance-records.index')
                ->with('success', 'Registro de mantenimiento creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear registro: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al crear registro: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar datos
     */
    public function update(Request $request, $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        try {
            $record = MaintenanceRecord::find($id);
            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro no encontrado.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'schedule_id' => 'required|exists:maintenance_schedules,id',
                'date' => 'required|date',
                'description' => 'required|string|max:1000',
                'image_path' => 'nullable|image|max:2048',
            ]);

            if ($validator->fails()) {
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Errores de validación.',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            // Validar que la fecha corresponda al día de la semana y al periodo del mantenimiento (igual que en store)
            $schedule = MaintenanceSchedule::find($data['schedule_id']);
            if (!$schedule) {
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => 'Horario de mantenimiento no encontrado.'], 422);
                }

                return response()->json(['success' => false, 'message' => 'Horario de mantenimiento no encontrado.'], 404);
            }

            $maintenance = $schedule->maintenance;
            if (!$maintenance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mantenimiento asociado no encontrado.',
                ], 404);
            }

            $date = Carbon::parse($data['date']);

            $daysMap = [
                'LUNES' => 1,
                'MARTES' => 2,
                'MIÉRCOLES' => 3,
                'MIERCOLES' => 3,
                'JUEVES' => 4,
                'VIERNES' => 5,
                'SÁBADO' => 6,
                'SABADO' => 6,
                'DOMINGO' => 7,
            ];

            $expectedWeek = $daysMap[strtoupper($schedule->day)] ?? null;
            $actualWeek = (int) $date->format('N');

            if ($expectedWeek === null || $expectedWeek !== $actualWeek) {
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => 'La fecha debe corresponder al día de la semana del horario: ' . $schedule->day], 422);
                }

                return response()->json(['success' => false, 'message' => 'La fecha debe corresponder al día de la semana del horario: ' . $schedule->day], 422);
            }

            $start = Carbon::parse($maintenance->start_date);
            $end = Carbon::parse($maintenance->end_date);
            if ($date->lt($start) || $date->gt($end)) {
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => 'La fecha debe estar dentro del periodo del mantenimiento: ' . $maintenance->start_date . ' - ' . $maintenance->end_date], 422);
                }

                return response()->json(['success' => false, 'message' => 'La fecha debe estar dentro del periodo del mantenimiento: ' . $maintenance->start_date . ' - ' . $maintenance->end_date], 422);
            }

            if ($request->hasFile('image_path')) {
                if ($record->image_path) {
                    Storage::disk('public')->delete($record->image_path);
                }
                $data['image_path'] = $request->file('image_path')->store('maintenance_records', 'public');
            }

            $record->update($data);

            if ($isTurbo) {
                return response()->json(['success' => true, 'data' => $record->fresh(), 'message' => 'Registro actualizado correctamente.'], 200);
            }

            return redirect()->route('maintenance-records.index')
                ->with('success', 'Registro actualizado correctamente.');
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
            $record = MaintenanceRecord::find($id);
            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro no encontrado.',
                ], 404);
            }

            if ($record->image_path) {
                Storage::disk('public')->delete($record->image_path);
            }

            $record->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar registro: ' . $e->getMessage(),
            ], 500);
        }
    }
}