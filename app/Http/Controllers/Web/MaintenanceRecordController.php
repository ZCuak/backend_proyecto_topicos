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
            $scheduleId = $request->input('schedule_id'); // 🎯 NUEVO - Filtrar por horario

            $query = MaintenanceRecord::with(['schedule.maintenance', 'schedule.vehicle', 'schedule.responsible']); // 🎯 Agregar responsible

            if ($search) {
                $query->where('description', 'ILIKE', "%{$search}%")
                    ->orWhereHas('schedule.maintenance', function ($q) use ($search) {
                        $q->where('name', 'ILIKE', "%{$search}%");
                    });
            }

            if ($scheduleId) {
                $query->where('schedule_id', $scheduleId);
            }

            $records = $query->orderBy('date', 'desc')->paginate(10);

            // 🎯 NUEVO - Pasar schedules para filtro
            $schedules = MaintenanceSchedule::with(['maintenance', 'vehicle'])
                ->orderBy('id')
                ->get();

            return view('maintenance_records.index', compact('records', 'search', 'schedules', 'scheduleId'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar registros: ' . $e->getMessage());
        }
    }

    /**
     * 🎯 VER días generados de un horario específico
     */
    public function show($scheduleId)
    {
        try {
            $schedule = MaintenanceSchedule::with(['maintenance', 'vehicle', 'responsible'])
                ->findOrFail($scheduleId);

            $records = MaintenanceRecord::where('schedule_id', $scheduleId)
                ->orderBy('date', 'asc')
                ->get();

            return view('maintenance_records.show', compact('schedule', 'records'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar registros: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de creación (Turbo modal)
     */
    public function create(Request $request)
    {
        $record = new MaintenanceRecord();
        $schedules = MaintenanceSchedule::with(['maintenance', 'vehicle'])->get();

        return response()
            ->view('maintenance_records._modal_create', compact('record', 'schedules'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Formulario de edición (Turbo modal)
     */
    public function edit($id)
    {
        $record = MaintenanceRecord::with(['schedule.maintenance', 'schedule.vehicle'])->findOrFail($id);
        $schedules = MaintenanceSchedule::all();

        return response()
            ->view('maintenance_records._modal_edit', compact('record'))
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
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 🎯 Especificar tipos
            'completed' => 'nullable|boolean', // 🎯 NUEVO
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

            // 🎯 Validar que la fecha corresponda al día de la semana y al periodo del mantenimiento
            $schedule = MaintenanceSchedule::find($data['schedule_id']);
            if (!$schedule) {
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => 'Horario de mantenimiento no encontrado.'], 404);
                }
                return back()->with('error', 'Horario de mantenimiento no encontrado.')->withInput();
            }

            $maintenance = $schedule->maintenance;
            if (!$maintenance) {
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => 'Mantenimiento asociado no encontrado.'], 404);
                }
                return back()->with('error', 'Mantenimiento asociado no encontrado.')->withInput();
            }

            $date = Carbon::parse($data['date']);

            // 🎯 Validar día de la semana
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
            $actualWeek = (int) $date->dayOfWeekIso;

            if ($expectedWeek === null || $expectedWeek !== $actualWeek) {
                $message = 'La fecha debe corresponder al día de la semana del horario: ' . $schedule->day;
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return back()->with('error', $message)->withInput();
            }

            // 🎯 Validar que esté dentro del periodo
            $start = Carbon::parse($maintenance->start_date);
            $end = Carbon::parse($maintenance->end_date);

            if ($date->lt($start) || $date->gt($end)) {
                $message = 'La fecha debe estar dentro del periodo del mantenimiento: ' .
                    $maintenance->start_date . ' - ' . $maintenance->end_date;
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return back()->with('error', $message)->withInput();
            }

            // 🎯 FIX: Guardar imagen correctamente
            if ($request->hasFile('image_path')) {
                $file = $request->file('image_path');

                // Validar que el archivo sea válido
                if (!$file->isValid()) {
                    throw new \Exception('Archivo de imagen inválido');
                }

                // Generar nombre único
                $filename = time() . '_' . $file->getClientOriginalName();

                // Guardar en storage/app/public/maintenance_records
                $path = $file->storeAs('maintenance_records', $filename, 'public');

                $data['image_path'] = $path;
            }

            // 🎯 Asegurar que completed sea booleano
            $data['completed'] = $data['completed'] ?? false;

            MaintenanceRecord::create($data);

            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registro de mantenimiento creado exitosamente.'
                ], 201);
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
                // 🎯 NO permitir cambiar schedule_id ni date en edición
                'description' => 'required|string|max:1000',
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 🎯 Especificar tipos
                'completed' => 'nullable|boolean', // 🎯 NUEVO
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

            $data = $validator->validated();

            // 🎯 FIX: Guardar imagen correctamente
            if ($request->hasFile('image_path')) {
                // Eliminar imagen anterior si existe
                if ($record->image_path && Storage::disk('public')->exists($record->image_path)) {
                    Storage::disk('public')->delete($record->image_path);
                }

                $file = $request->file('image_path');

                // Validar que el archivo sea válido
                if (!$file->isValid()) {
                    throw new \Exception('Archivo de imagen inválido');
                }

                // Generar nombre único
                $filename = time() . '_' . $file->getClientOriginalName();

                // Guardar en storage/app/public/maintenance_records
                $path = $file->storeAs('maintenance_records', $filename, 'public');

                $data['image_path'] = $path;
            }

            // 🎯 Asegurar que completed sea booleano
            if (isset($data['completed'])) {
                $data['completed'] = (bool) $data['completed'];
            }

            $record->update($data);

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registro actualizado correctamente.',
                    'data' => $record->fresh()
                ], 200);
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

            // 🎯 Eliminar imagen si existe
            if ($record->image_path && Storage::disk('public')->exists($record->image_path)) {
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
