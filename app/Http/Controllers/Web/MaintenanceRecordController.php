<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

            if ($request->hasFile('image_path')) {
                $data['image_path'] = $request->file('image_path')->store('maintenance_records', 'public');
            }

            MaintenanceRecord::create($data);

            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registro de mantenimiento creado exitosamente.',
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
                'schedule_id' => 'required|exists:maintenance_schedules,id',
                'date' => 'required|date',
                'description' => 'required|string|max:1000',
                'image_path' => 'nullable|image|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('image_path')) {
                if ($record->image_path) {
                    \Storage::disk('public')->delete($record->image_path);
                }
                $data['image_path'] = $request->file('image_path')->store('maintenance_records', 'public');
            }

            $record->update($data);

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registro actualizado correctamente.',
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

            if ($record->image_path) {
                \Storage::disk('public')->delete($record->image_path);
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