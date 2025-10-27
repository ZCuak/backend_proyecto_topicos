<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Scheduling;
use App\Models\EmployeeGroup;
use App\Models\Schedule;
use App\Models\Vehicle;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SchedulingController extends Controller
{
    /**
     * Listar programaciones (Web)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('perPage', 10);
        $dateFilter = $request->input('date_filter');

        $query = Scheduling::with(['group', 'schedule', 'vehicle', 'zone']);

        // Búsqueda general
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'ILIKE', "%{$search}%")
                  ->orWhere('date', 'ILIKE', "%{$search}%")
                  ->orWhereHas('group', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"))
                  ->orWhereHas('schedule', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"))
                  ->orWhereHas('vehicle', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"))
                  ->orWhereHas('zone', fn($q) => $q->where('name', 'ILIKE', "%{$search}%"));
            });
        }

        // Filtro por fecha
        if ($dateFilter) {
            $query->whereDate('date', $dateFilter);
        }

        $schedulings = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate($perPage)->appends([
            'search' => $search,
            'perPage' => $perPage,
            'date_filter' => $dateFilter
        ]);

        return view('schedulings.index', compact('schedulings', 'search', 'dateFilter'));
    }

    /**
     * Mostrar formulario de creación (Web)
     */
    public function create()
    {
        $groups = EmployeeGroup::orderBy('name')->get();
        $schedules = Schedule::orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'DISPONIBLE')->orderBy('name')->get();
        $zones = Zone::orderBy('name')->get();

        return view('schedulings._modal_create', compact('groups', 'schedules', 'vehicles', 'zones'));
    }

    /**
     * Almacenar nueva programación (Web)
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        // Console logs para debuggear
        Log::info('=== CREATE SCHEDULING DEBUG ===');
        Log::info('Request data: ', $request->all());
        Log::info('Is Turbo: ' . ($isTurbo ? 'YES' : 'NO'));

        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:employeegroups,id',
            'schedule_id' => 'required|exists:schedules,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'zone_id' => 'nullable|exists:zones,id',
            'date' => 'required|date|after_or_equal:today',
            'status' => 'required|integer|in:0,1,2,3', // 0=Pendiente, 1=En Proceso, 2=Completado, 3=Cancelado
            'notes' => 'nullable|string|max:500'
        ], [
            'group_id.required' => 'El grupo de empleados es obligatorio',
            'group_id.exists' => 'El grupo seleccionado no es válido',
            'schedule_id.required' => 'El horario es obligatorio',
            'schedule_id.exists' => 'El horario seleccionado no es válido',
            'vehicle_id.exists' => 'El vehículo seleccionado no es válido',
            'zone_id.exists' => 'La zona seleccionada no es válida',
            'date.required' => 'La fecha es obligatoria',
            'date.date' => 'La fecha debe tener un formato válido',
            'date.after_or_equal' => 'La fecha no puede ser anterior a hoy',
            'status.required' => 'El estado es obligatorio',
            'status.in' => 'El estado seleccionado no es válido'
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
            
            Log::info('Validated data: ', $data);
            
            $scheduling = Scheduling::create($data);
            
            Log::info('Scheduling created with ID: ' . $scheduling->id);
            
            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Programación registrada exitosamente.',
                ], 201);
            }

            return redirect()->route('schedulings.index')
                ->with('success', 'Programación registrada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating scheduling: ' . $e->getMessage());
            
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar programación: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar programación: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar programación específica (Web)
     */
    public function show(Scheduling $scheduling)
    {
        $scheduling->load(['group', 'schedule', 'vehicle', 'zone']);
        return view('schedulings.show', compact('scheduling'));
    }

    /**
     * Mostrar formulario de edición (Web)
     */
    public function edit(Scheduling $scheduling)
    {
        $groups = EmployeeGroup::orderBy('name')->get();
        $schedules = Schedule::orderBy('name')->get();
        $vehicles = Vehicle::orderBy('name')->get();
        $zones = Zone::orderBy('name')->get();

        return view('schedulings._modal_edit', compact('scheduling', 'groups', 'schedules', 'vehicles', 'zones'));
    }

    /**
     * Actualizar programación (Web)
     */
    public function update(Request $request, Scheduling $scheduling)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        // Console logs para debuggear
        Log::info('=== UPDATE SCHEDULING DEBUG ===');
        Log::info('Scheduling ID: ' . $scheduling->id);
        Log::info('Request data: ', $request->all());
        Log::info('Is Turbo: ' . ($isTurbo ? 'YES' : 'NO'));

        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:employeegroups,id',
            'schedule_id' => 'required|exists:schedules,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'zone_id' => 'nullable|exists:zones,id',
            'date' => 'required|date',
            'status' => 'required|integer|in:0,1,2,3',
            'notes' => 'nullable|string|max:500'
        ], [
            'group_id.required' => 'El grupo de empleados es obligatorio',
            'group_id.exists' => 'El grupo seleccionado no es válido',
            'schedule_id.required' => 'El horario es obligatorio',
            'schedule_id.exists' => 'El horario seleccionado no es válido',
            'vehicle_id.exists' => 'El vehículo seleccionado no es válido',
            'zone_id.exists' => 'La zona seleccionada no es válida',
            'date.required' => 'La fecha es obligatoria',
            'date.date' => 'La fecha debe tener un formato válido',
            'status.required' => 'El estado es obligatorio',
            'status.in' => 'El estado seleccionado no es válido'
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

        try {
            $data = $validator->validated();
            
            Log::info('Validated data: ', $data);
            
            $scheduling->update($data);
            
            Log::info('Scheduling updated successfully');

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Programación actualizada exitosamente.',
                ], 200);
            }

            return redirect()->route('schedulings.index')
                ->with('success', 'Programación actualizada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error updating scheduling: ' . $e->getMessage());
            
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar programación: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar programación: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar programación (Web)
     */
    public function destroy(Scheduling $scheduling)
    {
        try {
            $scheduling->delete();

            return redirect()->route('schedulings.index')
                ->with('success', 'Programación eliminada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error deleting scheduling: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al eliminar programación: ' . $e->getMessage());
        }
    }
}
