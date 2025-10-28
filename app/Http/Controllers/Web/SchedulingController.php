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
     * Listar programaciones (Web) - Vista de tabla
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
     * Mostrar calendario de programaciones (Web)
     */
    public function calendar(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        
        // Obtener programaciones del mes seleccionado
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $schedulings = Scheduling::with(['group', 'schedule', 'vehicle', 'zone', 'details.user'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('schedule_id')
            ->get()
            ->map(function($scheduling) {
                // Asegurar que date sea un objeto Carbon
                if (is_string($scheduling->date)) {
                    $scheduling->date = \Carbon\Carbon::parse($scheduling->date);
                }
                return $scheduling;
            });
        
        // Obtener horarios para identificar turnos
        $schedules = \App\Models\Schedule::orderBy('time_start')->get();
        
        // Organizar programaciones por fecha y turno
        $calendarData = [];
        foreach ($schedulings as $scheduling) {
            $date = $scheduling->date->format('Y-m-d');
            $scheduleId = $scheduling->schedule_id;
            
            if (!isset($calendarData[$date])) {
                $calendarData[$date] = [];
            }
            
            if (!isset($calendarData[$date][$scheduleId])) {
                $calendarData[$date][$scheduleId] = [];
            }
            
            $calendarData[$date][$scheduleId][] = $scheduling;
        }
        
        // Generar días del calendario
        $calendarDays = [];
        $currentDate = $startDate->copy();
        
        // Días del mes anterior (para completar la primera semana)
        $firstDayOfWeek = $startDate->dayOfWeek;
        $prevMonth = $startDate->copy()->subMonth();
        $daysInPrevMonth = $prevMonth->daysInMonth;
        
        for ($i = $firstDayOfWeek - 1; $i >= 0; $i--) {
            $day = $daysInPrevMonth - $i;
            $calendarDays[] = [
                'date' => $prevMonth->copy()->day($day),
                'isCurrentMonth' => false,
                'isToday' => false,
                'schedulings' => []
            ];
        }
        
        // Días del mes actual
        while ($currentDate->month == $month) {
            $dateString = $currentDate->format('Y-m-d');
            $calendarDays[] = [
                'date' => $currentDate->copy(),
                'isCurrentMonth' => true,
                'isToday' => $currentDate->isToday(),
                'schedulings' => $calendarData[$dateString] ?? []
            ];
            $currentDate->addDay();
        }
        
        // Días del mes siguiente (para completar la última semana)
        $lastDayOfWeek = $currentDate->subDay()->dayOfWeek;
        $nextMonth = $currentDate->copy()->addMonth();
        
        for ($i = 1; $i <= (6 - $lastDayOfWeek); $i++) {
            $calendarDays[] = [
                'date' => $nextMonth->copy()->day($i),
                'isCurrentMonth' => false,
                'isToday' => false,
                'schedulings' => []
            ];
        }
        
        return view('schedulings.calendar', compact('calendarDays', 'schedules', 'year', 'month'));
    }

    /**
     * Obtener detalles de programación para el modal del calendario
     */
    public function getSchedulingDetails($id)
    {
        $scheduling = Scheduling::with(['group', 'schedule', 'vehicle', 'zone', 'details.user.userType'])
            ->findOrFail($id);
        
        // Asegurar que date sea un objeto Carbon
        if (is_string($scheduling->date)) {
            $scheduling->date = \Carbon\Carbon::parse($scheduling->date);
        }
        
        return response()->json([
            'success' => true,
            'data' => $scheduling,
            'html' => view('schedulings._modal_details', compact('scheduling'))->render()
        ]);
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

        // Validación adicional: Verificar que el grupo tenga usuarios con contratos activos
        $validationError = $this->validateGroupHasActiveContracts($request->group_id, $isTurbo);
        if ($validationError) {
            return $validationError;
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
            
            // Verificar sobreposición antes de crear
            $existingScheduling = Scheduling::where('date', $data['date'])
                ->where('group_id', $data['group_id'])
                ->where('schedule_id', $data['schedule_id'])
                ->first();
            
            if ($existingScheduling) {
                $errorMessage = 'Ya existe una programación para este grupo, horario y fecha.';
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => ['date' => [$errorMessage]],
                    ], 422);
                }
                return back()->withErrors(['date' => $errorMessage])->withInput();
            }
            
            // Verificar conflictos con vehículo
            if ($data['vehicle_id']) {
                $vehicleConflict = Scheduling::where('date', $data['date'])
                    ->where('vehicle_id', $data['vehicle_id'])
                    ->where('schedule_id', $data['schedule_id'])
                    ->first();
                
                if ($vehicleConflict) {
                    $errorMessage = 'El vehículo seleccionado ya está programado para esta fecha y horario.';
                    if ($isTurbo) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMessage,
                            'errors' => ['vehicle_id' => [$errorMessage]],
                        ], 422);
                    }
                    return back()->withErrors(['vehicle_id' => $errorMessage])->withInput();
                }
            }
            
            // Verificar conflictos con zona
            if ($data['zone_id']) {
                $zoneConflict = Scheduling::where('date', $data['date'])
                    ->where('zone_id', $data['zone_id'])
                    ->where('schedule_id', $data['schedule_id'])
                    ->first();
                
                if ($zoneConflict) {
                    $errorMessage = 'La zona seleccionada ya está programada para esta fecha y horario.';
                    if ($isTurbo) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMessage,
                            'errors' => ['zone_id' => [$errorMessage]],
                        ], 422);
                    }
                    return back()->withErrors(['zone_id' => $errorMessage])->withInput();
                }
            }
            
            $scheduling = Scheduling::create($data);
            
            // Crear detalles de programación
            $this->createSchedulingDetails($scheduling->id, $data['group_id']);
            
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
            
            // Verificar sobreposición antes de actualizar (excluyendo el registro actual)
            $existingScheduling = Scheduling::where('date', $data['date'])
                ->where('group_id', $data['group_id'])
                ->where('schedule_id', $data['schedule_id'])
                ->where('id', '!=', $scheduling->id)
                ->first();
            
            if ($existingScheduling) {
                $errorMessage = 'Ya existe una programación para este grupo, horario y fecha.';
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => ['date' => [$errorMessage]],
                    ], 422);
                }
                return back()->withErrors(['date' => $errorMessage])->withInput();
            }
            
            // Verificar conflictos con vehículo (excluyendo el registro actual)
            if ($data['vehicle_id']) {
                $vehicleConflict = Scheduling::where('date', $data['date'])
                    ->where('vehicle_id', $data['vehicle_id'])
                    ->where('schedule_id', $data['schedule_id'])
                    ->where('id', '!=', $scheduling->id)
                    ->first();
                
                if ($vehicleConflict) {
                    $errorMessage = 'El vehículo seleccionado ya está programado para esta fecha y horario.';
                    if ($isTurbo) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMessage,
                            'errors' => ['vehicle_id' => [$errorMessage]],
                        ], 422);
                    }
                    return back()->withErrors(['vehicle_id' => $errorMessage])->withInput();
                }
            }
            
            // Verificar conflictos con zona (excluyendo el registro actual)
            if ($data['zone_id']) {
                $zoneConflict = Scheduling::where('date', $data['date'])
                    ->where('zone_id', $data['zone_id'])
                    ->where('schedule_id', $data['schedule_id'])
                    ->where('id', '!=', $scheduling->id)
                    ->first();
                
                if ($zoneConflict) {
                    $errorMessage = 'La zona seleccionada ya está programada para esta fecha y horario.';
                    if ($isTurbo) {
                        return response()->json([
                            'success' => false,
                            'message' => $errorMessage,
                            'errors' => ['zone_id' => [$errorMessage]],
                        ], 422);
                    }
                    return back()->withErrors(['zone_id' => $errorMessage])->withInput();
                }
            }
            
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
            'exclude_weekends' => 'nullable|boolean',
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

        // Validación adicional: Verificar contratos activos con position_id correcto y fechas
        $programmingDates = [
            'start' => $request->start_date,
            'end' => $request->end_date
        ];
        
        $contractValidation = $this->validateGroupContractsForProgramming($request->group_id, $programmingDates, $isTurbo);
        if ($contractValidation) {
            return $contractValidation;
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
            $excludeSpecificDates = isset($data['exclude_specific_dates']) && $data['exclude_specific_dates'] 
                ? explode(',', $data['exclude_specific_dates']) 
                : [];
            
            $createdCount = 0;
            $conflictDates = [];
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
                
                // Verificar también si hay conflictos con vehículo en la misma fecha y horario
                $vehicleConflict = null;
                if ($data['vehicle_id']) {
                    $vehicleConflict = Scheduling::where('date', $dateString)
                        ->where('vehicle_id', $data['vehicle_id'])
                        ->where('schedule_id', $data['schedule_id'])
                        ->where('id', '!=', $existingScheduling?->id)
                        ->first();
                }
                
                // Verificar conflictos con zona en la misma fecha y horario
                $zoneConflict = null;
                if ($data['zone_id']) {
                    $zoneConflict = Scheduling::where('date', $dateString)
                        ->where('zone_id', $data['zone_id'])
                        ->where('schedule_id', $data['schedule_id'])
                        ->where('id', '!=', $existingScheduling?->id)
                        ->first();
                }
                
                if ($existingScheduling) {
                    $conflictDates[] = "Ya existe una programación para el grupo, horario y fecha {$dateString}";
                } elseif ($vehicleConflict) {
                    $conflictDates[] = "El vehículo ya está programado para la fecha {$dateString} y horario";
                } elseif ($zoneConflict) {
                    $conflictDates[] = "La zona ya está programada para la fecha {$dateString} y horario";
                } else {
                    $scheduling = Scheduling::create([
                        'group_id' => $data['group_id'],
                        'schedule_id' => $data['schedule_id'],
                        'vehicle_id' => $data['vehicle_id'],
                        'zone_id' => $data['zone_id'],
                        'date' => $dateString,
                        'status' => $data['status'],
                        'notes' => $data['notes']
                    ]);
                    
                    // Crear detalles de programación
                    $this->createSchedulingDetails($scheduling->id, $data['group_id']);
                    
                    $createdCount++;
                }
                
                $currentDate->addDay();
            }
            
            // Si hay conflictos, mostrar error
            if (!empty($conflictDates)) {
                $errorMessage = 'Se encontraron conflictos en las siguientes fechas: ' . implode(', ', $conflictDates);
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => ['start_date' => [$errorMessage]],
                    ], 422);
                }
                return back()->withErrors(['start_date' => $errorMessage])->withInput();
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

    /**
     * Obtener usuarios con contratos activos de un grupo
     */
    private function getGroupUsersWithActiveContracts($groupId)
    {
        return \App\Models\ConfigGroup::where('group_id', $groupId)
            ->with(['user.contracts' => function($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->filter(function($configGroup) {
                return $configGroup->user->contracts->isNotEmpty();
            });
    }

    /**
     * Validar que un grupo tenga usuarios con contratos activos
     */
    private function validateGroupHasActiveContracts($groupId, $isTurbo = false)
    {
        $groupUsers = $this->getGroupUsersWithActiveContracts($groupId);

        if ($groupUsers->isEmpty()) {
            $errorMessage = 'El grupo seleccionado no tiene usuarios con contratos activos.';
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['group_id' => [$errorMessage]],
                ], 422);
            }
            return back()->withErrors(['group_id' => $errorMessage])->withInput();
        }

        return null; // No hay errores
    }

    /**
     * Validar contratos de usuarios del grupo para programación
     */
    private function validateGroupContractsForProgramming($groupId, $programmingDates, $isTurbo = false)
    {
        $groupUsers = \App\Models\ConfigGroup::where('group_id', $groupId)
            ->with(['user.contracts' => function($query) {
                $query->where('is_active', true);
            }])
            ->get();

        if ($groupUsers->isEmpty()) {
            $errorMessage = 'El grupo seleccionado no tiene usuarios asignados.';
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['group_id' => [$errorMessage]],
                ], 422);
            }
            return back()->withErrors(['group_id' => $errorMessage])->withInput();
        }

        foreach ($groupUsers as $configGroup) {
            $user = $configGroup->user;
            
            // Verificar si tiene contrato activo
            if ($user->contracts->isEmpty()) {
                $errorMessage = "El usuario {$user->firstname} {$user->lastname} no tiene un contrato activo.";
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => ['group_id' => [$errorMessage]],
                    ], 422);
                }
                return back()->withErrors(['group_id' => $errorMessage])->withInput();
            }

            $activeContract = $user->contracts->first();
            
            // Verificar fechas del contrato
            $contractStartDate = \Carbon\Carbon::parse($activeContract->date_start);
            $contractEndDate = $activeContract->date_end ? \Carbon\Carbon::parse($activeContract->date_end) : null;
            
            // Verificar si el contrato ya expiró
            if ($contractEndDate && $contractEndDate->isPast()) {
                $errorMessage = "El contrato del usuario {$user->firstname} {$user->lastname} expiró el {$contractEndDate->format('d/m/Y')}.";
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => ['group_id' => [$errorMessage]],
                    ], 422);
                }
                return back()->withErrors(['group_id' => $errorMessage])->withInput();
            }
            
            // Verificar si el contrato aún no ha iniciado
            if ($contractStartDate->isFuture()) {
                $errorMessage = "El contrato del usuario {$user->firstname} {$user->lastname} inicia el {$contractStartDate->format('d/m/Y')}.";
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => ['group_id' => [$errorMessage]],
                    ], 422);
                }
                return back()->withErrors(['group_id' => $errorMessage])->withInput();
            }
            
            // Verificar que el contrato cubra el rango de programación
            $programmingStart = \Carbon\Carbon::parse($programmingDates['start']);
            $programmingEnd = \Carbon\Carbon::parse($programmingDates['end']);
            
            // Verificar que el contrato cubra el inicio de la programación
            if ($programmingStart->lt($contractStartDate)) {
                $errorMessage = "El contrato del usuario {$user->firstname} {$user->lastname} inicia el {$contractStartDate->format('d/m/Y')} pero la programación comienza el {$programmingStart->format('d/m/Y')}.";
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => ['group_id' => [$errorMessage]],
                    ], 422);
                }
                return back()->withErrors(['group_id' => $errorMessage])->withInput();
            }
            
            // Verificar que el contrato cubra el final de la programación (si tiene fecha de fin)
            if ($contractEndDate && $programmingEnd->gt($contractEndDate)) {
                $errorMessage = "El contrato del usuario {$user->firstname} {$user->lastname} expira el {$contractEndDate->format('d/m/Y')} pero la programación termina el {$programmingEnd->format('d/m/Y')}.";
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => ['group_id' => [$errorMessage]],
                    ], 422);
                }
                return back()->withErrors(['group_id' => $errorMessage])->withInput();
            }
        }

        return null; // No hay errores
    }

    /**
     * Crear detalles de programación basados en el grupo
     */
    private function createSchedulingDetails($schedulingId, $groupId)
    {
        $scheduling = Scheduling::find($schedulingId);
        
        // Obtener usuarios del grupo
        $groupUsers = \App\Models\ConfigGroup::where('group_id', $groupId)
            ->with('user')
            ->orderBy('id')
            ->get();

        // Determinar fechas para crear detalles
        $dates = [];
        if ($scheduling->isRangeScheduling()) {
            // Programación de rango: crear detalles para cada fecha del rango
            $dates = $scheduling->getAllDates();
        } else {
            // Programación de un solo día: solo esa fecha
            $dateValue = is_string($scheduling->date) ? $scheduling->date : $scheduling->date->format('Y-m-d');
            $dates = [$dateValue];
        }

        // Crear detalles para cada usuario en cada fecha
        foreach ($dates as $date) {
            $positionOrder = 1;
            
            foreach ($groupUsers as $configGroup) {
                $user = $configGroup->user;
                
                // Obtener el usertype_id del contrato activo (que hace referencia a usertypes.id)
                // Posición 1 = Conductor (usertype_id = 1), Posiciones 2 y 3 = Ayudantes (usertype_id = 2)
                $activeContract = $user->contracts()->where('is_active', true)->with('position')->first();
                $usertype_id = $activeContract ? $activeContract->position_id : null;

                // Crear detalle de programación para esta fecha
                \App\Models\SchedulingDetail::create([
                    'scheduling_id' => $schedulingId,
                    'user_id' => $user->id,
                    'usertype_id' => $usertype_id,
                    'position_order' => $positionOrder,
                    'attendance_status' => 'pendiente',
                    'notes' => null,
                    'date' => $date,
                ]);

                $positionOrder++;
            }
        }
    }

    /**
     * Actualizar detalles de programación
     */
    private function updateSchedulingDetails($schedulingId, $groupId)
    {
        // Eliminar detalles existentes
        \App\Models\SchedulingDetail::where('scheduling_id', $schedulingId)->delete();
        
        // Crear nuevos detalles
        $this->createSchedulingDetails($schedulingId, $groupId);
    }
}
