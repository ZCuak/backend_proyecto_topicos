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
            'status' => 'nullable|integer|in:0,1,2,3', // 0=Pendiente, 1=En Proceso, 2=Completado, 3=Cancelado
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
            
            // Establecer valores por defecto si no se proporcionan
            if (!isset($data['date']) || empty($data['date'])) {
                $data['date'] = now()->format('Y-m-d');
            }
            if (!isset($data['status']) || $data['status'] === '') {
                $data['status'] = 0; // Pendiente
            }
            
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
            'status' => 'nullable|integer|in:0,1,2,3',
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
            
            // Establecer valores por defecto si no se proporcionan
            if (!isset($data['date']) || empty($data['date'])) {
                $data['date'] = now()->format('Y-m-d');
            }
            if (!isset($data['status']) || $data['status'] === '') {
                $data['status'] = 0; // Pendiente
            }
            
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

    /**
     * Mostrar formulario de programación masiva (Web)
     */
    public function createMassive()
    {
        $groups = EmployeeGroup::orderBy('name')->get();
        $schedules = Schedule::orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'DISPONIBLE')->orderBy('name')->get();
        $zones = Zone::orderBy('name')->get();

        return view('schedulings.massive._modal_create', compact('groups', 'schedules', 'vehicles', 'zones'));
    }

    /**
     * Almacenar programación masiva (Web)
     */
    public function storeMassive(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        Log::info('=== CREATE MASSIVE SCHEDULING DEBUG ===');
        Log::info('Request data: ', $request->all());
        Log::info('Is Turbo: ' . ($isTurbo ? 'YES' : 'NO'));

        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:employeegroups,id',
            'schedule_id' => 'required|exists:schedules,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'zone_id' => 'nullable|exists:zones,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|integer|in:0,1,2,3',
            'notes' => 'nullable|string|max:500',
            'exclude_weekends' => 'boolean',
            'exclude_specific_dates' => 'nullable|string'
        ], [
            'group_id.required' => 'El grupo de empleados es obligatorio',
            'group_id.exists' => 'El grupo seleccionado no es válido',
            'schedule_id.required' => 'El horario es obligatorio',
            'schedule_id.exists' => 'El horario seleccionado no es válido',
            'vehicle_id.exists' => 'El vehículo seleccionado no es válido',
            'zone_id.exists' => 'La zona seleccionada no es válida',
            'start_date.required' => 'La fecha de inicio es obligatoria',
            'start_date.date' => 'La fecha de inicio debe tener un formato válido',
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy',
            'end_date.required' => 'La fecha de fin es obligatoria',
            'end_date.date' => 'La fecha de fin debe tener un formato válido',
            'end_date.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio',
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
            
            // Establecer valores por defecto
            if (!isset($data['status']) || $data['status'] === '') {
                $data['status'] = 0; // Pendiente
            }
            
            $startDate = \Carbon\Carbon::parse($data['start_date']);
            $endDate = \Carbon\Carbon::parse($data['end_date']);
            $excludeWeekends = $data['exclude_weekends'] ?? false;
            $excludeSpecificDates = $data['exclude_specific_dates'] ? explode(',', $data['exclude_specific_dates']) : [];
            
            $createdCount = 0;
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Excluir fines de semana si está marcado
                if ($excludeWeekends && $currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }
                
                // Excluir fechas específicas
                $dateString = $currentDate->format('Y-m-d');
                if (in_array($dateString, $excludeSpecificDates)) {
                    $currentDate->addDay();
                    continue;
                }
                
                // Verificar si ya existe una programación para esta fecha, grupo y horario
                $existingScheduling = Scheduling::where('date', $dateString)
                    ->where('group_id', $data['group_id'])
                    ->where('schedule_id', $data['schedule_id'])
                    ->first();
                
                if (!$existingScheduling) {
                    Scheduling::create([
                        'group_id' => $data['group_id'],
                        'schedule_id' => $data['schedule_id'],
                        'vehicle_id' => $data['vehicle_id'],
                        'zone_id' => $data['zone_id'],
                        'date' => $dateString,
                        'status' => $data['status'],
                        'notes' => $data['notes']
                    ]);
                    $createdCount++;
                }
                
                $currentDate->addDay();
            }
            
            Log::info("Massive scheduling created: {$createdCount} schedulings");
            
            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => "Se crearon {$createdCount} programaciones exitosamente.",
                ], 201);
            }

            return redirect()->route('schedulings.index')
                ->with('success', "Se crearon {$createdCount} programaciones exitosamente");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating massive scheduling: ' . $e->getMessage());
            
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar programaciones masivas: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar programaciones masivas: ' . $e->getMessage());
        }
    }
}
