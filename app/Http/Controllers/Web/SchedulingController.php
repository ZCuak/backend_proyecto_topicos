<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HistoryController;
use App\Models\Scheduling;
use App\Models\EmployeeGroup;
use App\Models\Schedule;
use App\Models\Vehicle;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ConfigGroup;

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
        $firstDayOfWeek = $startDate->dayOfWeekIso; // Usar ISO (1=lunes, 7=domingo)
        $prevMonth = $startDate->copy()->subMonth();
        $daysInPrevMonth = $prevMonth->daysInMonth;

        // Ajustar para que el calendario empiece en lunes
        $daysToShowFromPrevMonth = $firstDayOfWeek - 1; // Si es miércoles (3), mostrar 2 días del mes anterior

        for ($i = $daysToShowFromPrevMonth; $i > 0; $i--) {
            $day = $daysInPrevMonth - $i + 1;
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
        $lastDayOfWeek = $currentDate->subDay()->dayOfWeekIso; // Usar ISO (1=lunes, 7=domingo)
        $nextMonth = $currentDate->copy()->addMonth();

        // Calcular cuántos días del mes siguiente mostrar para completar la semana
        $daysToShowFromNextMonth = 7 - $lastDayOfWeek;

        for ($i = 1; $i <= $daysToShowFromNextMonth; $i++) {
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
            'notes' => 'nullable|string|max:500',
            'days' => 'nullable|array',
            'days.*' => 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'
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
            'status.in' => 'El estado seleccionado no es válido',
            'days.array' => 'Los días deben ser un array',
            'days.*.in' => 'Los días seleccionados no son válidos'
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

        // Validación adicional: Verificar conflictos de vacaciones
        $vacationValidation = $this->validateVacationConflicts($request->group_id, [$request->date], $isTurbo);
        if ($vacationValidation) {
            return $vacationValidation;
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

            // Verificar sobreposición antes de crear (mismo grupo, mismo turno)
            $existingScheduling = Scheduling::where('date', $data['date'])
                ->where('group_id', $data['group_id'])
                ->where('schedule_id', $data['schedule_id'])
                ->first();

            if ($existingScheduling) {
                $errorMessage = 'Ya existe una programación para este grupo y horario en esta fecha.';
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => ['date' => [$errorMessage]],
                    ], 422);
                }
                return back()->withErrors(['date' => $errorMessage])->withInput();
            }

            // Verificar conflictos con vehículo (no puede estar en ningún lugar el mismo día y turno)
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
        $audits = HistoryController::getHistory('PROGRAMACION', $scheduling->id);
        return view('schedulings.show', compact('scheduling','audits'));
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
            'notes' => 'nullable|string|max:500',
            'days' => 'nullable|array',
            'days.*' => 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'
        ], [
            'group_id.required' => 'El grupo de empleados es obligatorio',
            'group_id.exists' => 'El grupo seleccionado no es válido',
            'schedule_id.required' => 'El horario es obligatorio',
            'schedule_id.exists' => 'El horario seleccionado no es válido',
            'vehicle_id.exists' => 'El vehículo seleccionado no es válido',
            'zone_id.exists' => 'La zona seleccionada no es válida',
            'date.required' => 'La fecha es obligatoria',
            'date.date' => 'La fecha debe tener un formato válido',
            'status.in' => 'El estado seleccionado no es válido',
            'days.array' => 'Los días deben ser un array',
            'days.*.in' => 'Los días seleccionados no son válidos'
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

            // Validación adicional: Verificar conflictos de vacaciones
            $vacationValidation = $this->validateVacationConflicts($request->group_id, [$request->date], $isTurbo);
            if ($vacationValidation) {
                return $vacationValidation;
            }

            // Verificar sobreposición antes de actualizar (mismo grupo, mismo turno, excluyendo el registro actual)
            $existingScheduling = Scheduling::where('date', $data['date'])
                ->where('group_id', $data['group_id'])
                ->where('schedule_id', $data['schedule_id'])
                ->where('id', '!=', $scheduling->id)
                ->first();

            if ($existingScheduling) {
                $errorMessage = 'Ya existe una programación para este grupo y horario en esta fecha.';
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => ['date' => [$errorMessage]],
                    ], 422);
                }
                return back()->withErrors(['date' => $errorMessage])->withInput();
            }

            // Verificar conflictos con vehículo (no puede estar en ningún lugar el mismo día y turno, excluyendo el registro actual)
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
        $groups = EmployeeGroup::with(['vehicle','schedule','configgroups.user'])->orderBy('name')->get();
        $schedules = Schedule::orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'DISPONIBLE')->orderBy('name')->get();
        $zones = Zone::orderBy('name')->get();
        $users = User::orderBy('firstname')->get();

        // Normalize group data for the view: decode days and extract driver/ayudantes from configgroups
        foreach ($groups as $group) {
            $group->days_array = is_array($group->days) ? $group->days : (empty($group->days) ? [] : json_decode($group->days, true));

            // configgroups are saved in order: driver first, then ayudantes
            $config = $group->configgroups->sortBy('id')->values();
            $group->driver = $config->get(0)?->user ?? null;
            $group->helper1 = $config->get(1)?->user ?? null;
            $group->helper2 = $config->get(2)?->user ?? null;
        }

        return view('schedulings.massive._modal_create', compact('groups', 'schedules', 'vehicles', 'zones', 'users'));
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
            // group_id can be omitted when scheduling for all groups; keep exists when provided
            'group_id' => 'nullable|exists:employeegroups,id',
            'all_groups' => 'nullable|boolean',
            'filter_schedule' => 'nullable|exists:schedules,id',
            'validate_only' => 'nullable|boolean',
            'schedule_id' => 'required|exists:schedules,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'zone_id' => 'nullable|exists:zones,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|integer|in:0,1,2,3',
            'notes' => 'nullable|string|max:500',
            'exclude_weekends' => 'nullable|boolean',
            'exclude_specific_dates' => 'nullable|string',
            'days' => 'nullable|array',
            'days.*' => 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'
        ], [
            'group_id.exists' => 'El grupo seleccionado no es válido',
            'filter_schedule.exists' => 'El horario de filtro no es válido',
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
            'status.in' => 'El estado seleccionado no es válido',
            'days.array' => 'Los días deben ser un array',
            'days.*.in' => 'Los días seleccionados no son válidos'
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


        // If a single group is provided, we will validate that group prior to iterating below.
        $programmingDates = [
            'start' => $request->start_date,
            'end' => $request->end_date
        ];

        // Note: when scheduling for all groups, per-group validations will be executed inside the loop.
        if ($request->filled('group_id')) {
            $contractValidation = $this->validateGroupContractsForProgramming($request->group_id, $programmingDates, $isTurbo);
            if ($contractValidation) {
                return $contractValidation;
            }

            $vacationValidation = $this->validateVacationConflictsForRange($request->group_id, $request->start_date, $request->end_date, $request->days ?? [], $isTurbo);
            if ($vacationValidation) {
                return $vacationValidation;
            }
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
                ? array_map('trim', explode(',', $data['exclude_specific_dates']))
                : [];

            // Decide groups to process: by filter_schedule, single group, or all groups
            if (isset($data['filter_schedule']) && $data['filter_schedule'] !== '') {
                // Filter groups by schedule
                $groups = EmployeeGroup::where('schedule_id', $data['filter_schedule'])->orderBy('name')->get();
            } elseif (!empty($data['all_groups']) && $data['all_groups']) {
                $groups = EmployeeGroup::orderBy('name')->get();
            } elseif (!empty($data['group_id'])) {
                $groups = EmployeeGroup::where('id', $data['group_id'])->get();
            } else {
                // If no group specified and all_groups not set, return error
                DB::rollBack();
                $errorMessage = 'Debe especificar un grupo o marcar programación para todos los grupos.';
                if ($isTurbo) {
                    return response()->json(['success' => false, 'message' => $errorMessage], 422);
                }
                return back()->withErrors(['group_id' => $errorMessage])->withInput();
            }

            $results = [];
            $totalCreated = 0;

            foreach ($groups as $group) {
                $groupResult = [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'inconsistencies' => [],
                    'created' => 0
                ];

                // Per-group validations
                $contractValidation = $this->validateGroupContractsForProgramming($group->id, $programmingDates, true);
                if ($contractValidation) {
                    // Extract message
                    $groupResult['inconsistencies'][] = $contractValidation->original['message'] ?? 'Problema con contratos activos';
                    $groupResult['ok'] = false;
                    $results[] = $groupResult;
                    continue;
                }

                $vacationValidation = $this->validateVacationConflictsForRange($group->id, $data['start_date'], $data['end_date'], $data['days'] ?? [], true);
                if ($vacationValidation) {
                    $groupResult['inconsistencies'][] = $vacationValidation->original['message'] ?? 'Conflictos de vacaciones';
                    $groupResult['ok'] = false;
                    $results[] = $groupResult;
                    continue;
                }

                // If validate_only is requested, we will not create records but still compute inconsistencies
                $validateOnly = !empty($data['validate_only']);

                // Iterate dates and check scheduling/vehicle conflicts
                $currentDate = $startDate->copy();
                while ($currentDate->lte($endDate)) {
                    // Exclude weekends
                    if ($excludeWeekends && $currentDate->isWeekend()) {
                        $currentDate->addDay();
                        continue;
                    }

                    $dateString = $currentDate->format('Y-m-d');
                    if (in_array($dateString, $excludeSpecificDates)) {
                        $currentDate->addDay();
                        continue;
                    }

                    // Check selected days filter
                    $selectedDays = $data['days'] ?? [];
                    if (!empty($selectedDays)) {
                        $dayOfWeek = $currentDate->dayOfWeek; // 0=domingo
                        $dayMap = [1=>'lunes',2=>'martes',3=>'miercoles',4=>'jueves',5=>'viernes',6=>'sabado',0=>'domingo'];
                        $dayInSpanish = $dayMap[$dayOfWeek] ?? '';
                        if (!in_array($dayInSpanish, $selectedDays)) {
                            $currentDate->addDay();
                            continue;
                        }
                    }

                    // Existing scheduling conflict for this group/date/schedule
                    $existingScheduling = Scheduling::where('date', $dateString)
                        ->where('group_id', $group->id)
                        ->where('schedule_id', $data['schedule_id'])
                        ->first();

                    if ($existingScheduling) {
                        $groupResult['inconsistencies'][] = "Ya existe programación para {$dateString} (turno id {$data['schedule_id']}).";
                        $currentDate->addDay();
                        continue;
                    }

                    // Vehicle conflict
                    if (!empty($data['vehicle_id'])) {
                        $vehicleConflict = Scheduling::where('date', $dateString)
                            ->where('vehicle_id', $data['vehicle_id'])
                            ->where('schedule_id', $data['schedule_id'])
                            ->first();

                        if ($vehicleConflict) {
                            $groupResult['inconsistencies'][] = "El vehículo está ocupado el {$dateString} (turno id {$data['schedule_id']}).";
                            $currentDate->addDay();
                            continue;
                        }
                    }

                    // If no inconsistencies for this date, create scheduling (unless validate_only)
                    if (!$validateOnly) {
                        $scheduling = Scheduling::create([
                            'group_id' => $group->id,
                            'schedule_id' => $data['schedule_id'],
                            'vehicle_id' => $data['vehicle_id'] ?? null,
                            'zone_id' => $data['zone_id'] ?? null,
                            'date' => $dateString,
                            'status' => $data['status'] ?? 0,
                            'notes' => $data['notes'] ?? null,
                            'days' => $data['days'] ?? null
                        ]);

                        $this->createSchedulingDetails($scheduling->id, $group->id);
                        $groupResult['created']++;
                        $totalCreated++;
                    }

                    $currentDate->addDay();
                }

                $groupResult['ok'] = empty($groupResult['inconsistencies']);
                $results[] = $groupResult;
            }

            DB::commit();

            Log::info("Massive scheduling processed. Total created: {$totalCreated}");

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => "Procesamiento finalizado.",
                    'total_created' => $totalCreated,
                    'groups' => $results
                ], 200);
            }

            // For non-AJAX requests, redirect back with a flash summary
            return redirect()->route('schedulings.index')
                ->with('success', "Procesamiento finalizado. Se crearon {$totalCreated} programaciones.")
                ->with('massive_results', $results);
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
     * Validar programación masiva sin crear registros.
     * Retorna por grupo las inconsistencias encontradas y un flag ok.
     */
    public function validateMassive(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'group_id' => 'nullable|exists:employeegroups,id',
            'all_groups' => 'nullable|boolean',
            'filter_schedule' => 'nullable|exists:schedules,id',
            'schedule_id' => 'required|exists:schedules,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'exclude_weekends' => 'nullable|boolean',
            'exclude_specific_dates' => 'nullable|string',
            'days' => 'nullable|array',
            'days.*' => 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'
        ]);

        if ($validator->fails()) {
            if ($isTurbo) {
                return response()->json(['success' => false, 'message' => 'Errores de validación.', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $startDate = \Carbon\Carbon::parse($data['start_date']);
        $endDate = \Carbon\Carbon::parse($data['end_date']);
        $excludeWeekends = $data['exclude_weekends'] ?? false;
        $excludeSpecificDates = isset($data['exclude_specific_dates']) && $data['exclude_specific_dates']
            ? array_map('trim', explode(',', $data['exclude_specific_dates']))
            : [];

        // Allow filtering groups by schedule (filter_schedule) or by group_id / all_groups
        if (isset($data['filter_schedule']) && $data['filter_schedule'] !== '') {
            $groups = EmployeeGroup::where('schedule_id', $data['filter_schedule'])->orderBy('name')->get();
        } elseif (!empty($data['all_groups']) && $data['all_groups']) {
            $groups = EmployeeGroup::orderBy('name')->get();
        } elseif (!empty($data['group_id'])) {
            $groups = EmployeeGroup::where('id', $data['group_id'])->get();
        } else {
            $errorMessage = 'Debe especificar un grupo o marcar programación para todos los grupos.';
            if ($isTurbo) {
                return response()->json(['success' => false, 'message' => $errorMessage], 422);
            }
            return back()->withErrors(['group_id' => $errorMessage])->withInput();
        }

        $results = [];

        $programmingDates = ['start' => $data['start_date'], 'end' => $data['end_date']];

        foreach ($groups as $group) {
            $groupResult = ['group_id' => $group->id, 'group_name' => $group->name, 'inconsistencies' => [], 'created' => 0];

            $contractValidation = $this->validateGroupContractsForProgramming($group->id, $programmingDates, true);
            if ($contractValidation) {
                $groupResult['inconsistencies'][] = $contractValidation->original['message'] ?? 'Problema con contratos activos';
                $groupResult['ok'] = false;
                $results[] = $groupResult;
                continue;
            }

            $vacationValidation = $this->validateVacationConflictsForRange($group->id, $data['start_date'], $data['end_date'], $data['days'] ?? [], true);
            if ($vacationValidation) {
                $groupResult['inconsistencies'][] = $vacationValidation->original['message'] ?? 'Conflictos de vacaciones';
                $groupResult['ok'] = false;
                $results[] = $groupResult;
                continue;
            }

            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                if ($excludeWeekends && $currentDate->isWeekend()) { $currentDate->addDay(); continue; }
                $dateString = $currentDate->format('Y-m-d');
                if (in_array($dateString, $excludeSpecificDates)) { $currentDate->addDay(); continue; }

                $selectedDays = $data['days'] ?? [];
                if (!empty($selectedDays)) {
                    $dayOfWeek = $currentDate->dayOfWeek;
                    $dayMap = [1=>'lunes',2=>'martes',3=>'miercoles',4=>'jueves',5=>'viernes',6=>'sabado',0=>'domingo'];
                    $dayInSpanish = $dayMap[$dayOfWeek] ?? '';
                    if (!in_array($dayInSpanish, $selectedDays)) { $currentDate->addDay(); continue; }
                }

                $existingScheduling = Scheduling::where('date', $dateString)
                    ->where('group_id', $group->id)
                    ->where('schedule_id', $data['schedule_id'])
                    ->first();

                if ($existingScheduling) {
                    $groupResult['inconsistencies'][] = "Ya existe programación para {$dateString} (turno id {$data['schedule_id']}).";
                    $currentDate->addDay();
                    continue;
                }

                if (!empty($data['vehicle_id'])) {
                    $vehicleConflict = Scheduling::where('date', $dateString)
                        ->where('vehicle_id', $data['vehicle_id'])
                        ->where('schedule_id', $data['schedule_id'])
                        ->first();
                    if ($vehicleConflict) {
                        $groupResult['inconsistencies'][] = "El vehículo está ocupado el {$dateString} (turno id {$data['schedule_id']}).";
                        $currentDate->addDay();
                        continue;
                    }
                }

                $currentDate->addDay();
            }

            $groupResult['ok'] = empty($groupResult['inconsistencies']);
            $results[] = $groupResult;
        }

        return response()->json(['success' => true, 'groups' => $results], 200);
    }

    /**
     * Actualizar configuración de un grupo desde la UI de validación masiva.
     * Campos: group_id, driver_id, user1_id, user2_id, vehicle_id, days[]
     */
    public function updateGroupFromValidation(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:employeegroups,id',
            'driver_id' => 'required|exists:users,id',
            'user1_id' => 'nullable|exists:users,id|different:driver_id',
            'user2_id' => 'nullable|exists:users,id|different:driver_id|different:user1_id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'days' => 'nullable|array',
            'days.*' => 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Errores de validación', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        DB::beginTransaction();
        try {
            $group = EmployeeGroup::findOrFail($data['group_id']);

            // Update simple fields
            $group->vehicle_id = $data['vehicle_id'] ?? null;
            if (isset($data['days'])) {
                $group->days = json_encode($data['days']);
            }
            $group->save();

            // Replace configgroups (driver + helpers) in order
            ConfigGroup::where('group_id', $group->id)->delete();

            $order = [];
            $order[] = $data['driver_id'];
            if (!empty($data['user1_id'])) $order[] = $data['user1_id'];
            if (!empty($data['user2_id'])) $order[] = $data['user2_id'];

            foreach ($order as $userId) {
                ConfigGroup::create([ 'group_id' => $group->id, 'user_id' => $userId ]);
            }

            DB::commit();

            // Return updated info
            $group->load(['vehicle','configgroups.user']);
            $config = $group->configgroups->sortBy('id')->values();
            $driver = $config->get(0)?->user ?? null;
            $helper1 = $config->get(1)?->user ?? null;
            $helper2 = $config->get(2)?->user ?? null;

            return response()->json([
                'success' => true,
                'message' => 'Grupo actualizado',
                'group' => [
                    'id' => $group->id,
                    'vehicle' => $group->vehicle ? $group->vehicle->name : null,
                    'driver' => $driver ? ($driver->firstname . ' ' . $driver->lastname) : null,
                    'helper1' => $helper1 ? ($helper1->firstname . ' ' . $helper1->lastname) : null,
                    'helper2' => $helper2 ? ($helper2->firstname . ' ' . $helper2->lastname) : null,
                    'days' => is_array($group->days) ? $group->days : (empty($group->days) ? [] : json_decode($group->days, true))
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating group from validation: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar grupo: ' . $e->getMessage()], 500);
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

    /**
     * Formatear fechas de conflicto para mostrar en mensajes de error
     */
    private function formatConflictDates($dates)
    {
        if (empty($dates)) {
            return '';
        }

        // Ordenar fechas
        sort($dates);

        // Si son pocas fechas, mostrarlas todas
        if (count($dates) <= 3) {
            return implode(', ', $dates);
        }

        // Si son muchas fechas, mostrar las primeras y las últimas
        $firstDate = $dates[0];
        $lastDate = end($dates);

        if ($firstDate === $lastDate) {
            return $firstDate;
        }

        return "{$firstDate} hasta {$lastDate} (" . count($dates) . " fechas)";
    }

    /**
     * Validar conflictos de vacaciones para un grupo en fechas específicas
     */
    private function validateVacationConflicts($groupId, $programmingDates, $isTurbo = false)
    {
        Log::info('=== VACATION VALIDATION DEBUG ===');
        Log::info('Group ID: ' . $groupId);
        Log::info('Programming dates: ', $programmingDates);

        // Obtener usuarios del grupo
        $groupUsers = \App\Models\ConfigGroup::where('group_id', $groupId)
            ->with('user')
            ->get();

        Log::info('Group users found: ' . $groupUsers->count());

        if ($groupUsers->isEmpty()) {
            Log::info('No users found in group');
            return null; // No hay usuarios en el grupo
        }

        $vacationConflicts = [];

        foreach ($groupUsers as $configGroup) {
            $user = $configGroup->user;
            Log::info('Checking user: ' . $user->firstname . ' ' . $user->lastname . ' (ID: ' . $user->id . ')');

            // Obtener vacaciones activas del usuario
            $vacations = \App\Models\Vacation::where('user_id', $user->id)
                ->where('status', 'aprobada') // Solo vacaciones aprobadas
                ->get();

            Log::info('Vacations found for user ' . $user->firstname . ': ' . $vacations->count());

            foreach ($vacations as $vacation) {
                Log::info('Vacation: ' . $vacation->start_date . ' to ' . $vacation->end_date . ' (Status: ' . $vacation->status . ')');

                $vacationStart = \Carbon\Carbon::parse($vacation->start_date);
                $vacationEnd = \Carbon\Carbon::parse($vacation->end_date);

                // Verificar si alguna fecha de programación se superpone con las vacaciones
                foreach ($programmingDates as $programmingDate) {
                    $programmingDateCarbon = \Carbon\Carbon::parse($programmingDate);

                    Log::info('Checking programming date: ' . $programmingDate . ' against vacation: ' . $vacationStart->format('Y-m-d') . ' to ' . $vacationEnd->format('Y-m-d'));

                    // Verificar si la fecha de programación está dentro del rango de vacaciones
                    if ($programmingDateCarbon->between($vacationStart, $vacationEnd)) {
                        Log::info('CONFLICT FOUND! Programming date ' . $programmingDate . ' overlaps with vacation');
                        $vacationConflicts[] = [
                            'user' => $user,
                            'vacation_start' => $vacationStart->format('d/m/Y'),
                            'vacation_end' => $vacationEnd->format('d/m/Y'),
                            'conflict_date' => $programmingDateCarbon->format('d/m/Y')
                        ];
                    } else {
                        Log::info('No conflict for date: ' . $programmingDate);
                    }
                }
            }
        }

        // Si hay conflictos, generar mensaje de error agrupado
        if (!empty($vacationConflicts)) {
            $errorMessage = $this->formatVacationConflictMessages($vacationConflicts);

            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['group_id' => [$errorMessage]],
                ], 422);
            }
            return back()->withErrors(['group_id' => $errorMessage])->withInput();
        }

        return null; // No hay conflictos
    }

    /**
     * Validar conflictos de vacaciones para programación masiva (rango de fechas)
     */
    private function validateVacationConflictsForRange($groupId, $startDate, $endDate, $selectedDays, $isTurbo = false)
    {
        Log::info('=== MASSIVE VACATION VALIDATION DEBUG ===');
        Log::info('Group ID: ' . $groupId);
        Log::info('Start Date: ' . $startDate);
        Log::info('End Date: ' . $endDate);
        Log::info('Selected Days: ', $selectedDays);

        // Obtener usuarios del grupo
        $groupUsers = \App\Models\ConfigGroup::where('group_id', $groupId)
            ->with('user')
            ->get();

        Log::info('Group users found: ' . $groupUsers->count());

        if ($groupUsers->isEmpty()) {
            Log::info('No users found in group');
            return null; // No hay usuarios en el grupo
        }

        $vacationConflicts = [];
        $startDateCarbon = \Carbon\Carbon::parse($startDate);
        $endDateCarbon = \Carbon\Carbon::parse($endDate);

        foreach ($groupUsers as $configGroup) {
            $user = $configGroup->user;
            Log::info('Checking user: ' . $user->firstname . ' ' . $user->lastname . ' (ID: ' . $user->id . ')');

            // Obtener vacaciones activas del usuario
            $vacations = \App\Models\Vacation::where('user_id', $user->id)
                ->where('status', 'aprobada') // Solo vacaciones aprobadas
                ->get();

            Log::info('Vacations found for user ' . $user->firstname . ': ' . $vacations->count());

            foreach ($vacations as $vacation) {
                Log::info('Vacation: ' . $vacation->start_date . ' to ' . $vacation->end_date . ' (Status: ' . $vacation->status . ')');

                $vacationStart = \Carbon\Carbon::parse($vacation->start_date);
                $vacationEnd = \Carbon\Carbon::parse($vacation->end_date);

                // Generar todas las fechas del rango de programación
                $programmingDates = [];
                $currentDate = $startDateCarbon->copy();

                while ($currentDate->lte($endDateCarbon)) {
                    // Si hay días específicos seleccionados, verificar si el día actual está incluido
                    if (!empty($selectedDays)) {
                        $dayOfWeek = $currentDate->dayOfWeek; // 0=domingo, 1=lunes, ..., 6=sábado
                        $dayMap = [
                            1 => 'lunes',    // Monday
                            2 => 'martes',   // Tuesday
                            3 => 'miercoles', // Wednesday
                            4 => 'jueves',   // Thursday
                            5 => 'viernes',  // Friday
                            6 => 'sabado',   // Saturday
                            0 => 'domingo'   // Sunday
                        ];

                        $dayInSpanish = $dayMap[$dayOfWeek] ?? '';
                        if (in_array($dayInSpanish, $selectedDays)) {
                            $programmingDates[] = $currentDate->format('Y-m-d');
                        }
                    } else {
                        // Si no hay días específicos, incluir todos los días
                        $programmingDates[] = $currentDate->format('Y-m-d');
                    }

                    $currentDate->addDay();
                }

                Log::info('Generated programming dates: ', $programmingDates);

                // Verificar conflictos con cada fecha de programación
                foreach ($programmingDates as $programmingDate) {
                    $programmingDateCarbon = \Carbon\Carbon::parse($programmingDate);

                    Log::info('Checking programming date: ' . $programmingDate . ' against vacation: ' . $vacationStart->format('Y-m-d') . ' to ' . $vacationEnd->format('Y-m-d'));

                    // Verificar si la fecha de programación está dentro del rango de vacaciones
                    if ($programmingDateCarbon->between($vacationStart, $vacationEnd)) {
                        Log::info('CONFLICT FOUND! Programming date ' . $programmingDate . ' overlaps with vacation');
                        $vacationConflicts[] = [
                            'user' => $user,
                            'vacation_start' => $vacationStart->format('d/m/Y'),
                            'vacation_end' => $vacationEnd->format('d/m/Y'),
                            'conflict_date' => $programmingDateCarbon->format('d/m/Y')
                        ];
                    } else {
                        Log::info('No conflict for date: ' . $programmingDate);
                    }
                }
            }
        }

        // Si hay conflictos, generar mensaje de error agrupado
        if (!empty($vacationConflicts)) {
            $errorMessage = $this->formatVacationConflictMessages($vacationConflicts);

            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['group_id' => [$errorMessage]],
                ], 422);
            }
            return back()->withErrors(['group_id' => $errorMessage])->withInput();
        }

        return null; // No hay conflictos
    }
/**
 * Mostrar formulario de edición masiva.
 */
public function editMassive(Request $request)
{
    $schedules = Schedule::orderBy('name')->get();
    $groups = EmployeeGroup::with(['vehicle','schedule','configgroups.user','zone'])
        ->orderBy('name')->get();
    $users = User::orderBy('firstname')->get();
    $vehicles = Vehicle::orderBy('name')->get();
    $zones = Zone::orderBy('name')->get();

    $programaciones = collect();

    if ($request->filled(['schedule_id','start_date','end_date'])) {
        $programaciones = Scheduling::with(['group','schedule','vehicle','zone'])
            ->where('schedule_id', $request->schedule_id)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->orderBy('date')
            ->get();
    }

    return view('schedulings.massive._modal_edit', compact(
        'schedules','groups','users','vehicles','zones','programaciones'
    ));
}


/**
 * Endpoint AJAX: cargar programaciones existentes según rango y turno
 */
public function fetchMassive(Request $request)
{
    $request->validate([
        'schedule_id' => 'required|exists:schedules,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    $scheduleId = $request->schedule_id;
    $start = \Carbon\Carbon::parse($request->start_date);
    $end = \Carbon\Carbon::parse($request->end_date);

    $schedulings = Scheduling::with(['group.vehicle','group.zone','group.configgroups.user','schedule'])
        ->where('schedule_id', $scheduleId)
        ->whereBetween('date', [$start, $end])
        ->get()
        ->groupBy('group_id');

    $data = $schedulings->map(function($groupSchedulings, $groupId) {
        $group = $groupSchedulings->first()->group;

        // normalizar usuarios
        $config = $group->configgroups->sortBy('id')->values();
        $driver = $config->get(0)?->user;
        $helper1 = $config->get(1)?->user;
        $helper2 = $config->get(2)?->user;

        return [
            'group_id' => $group->id,
            'group_name' => $group->name,
            'zone' => optional($group->zone)->name,
            'vehicle' => optional($group->vehicle)->name,
            'driver' => $driver ? $driver->firstname.' '.$driver->lastname : null,
            'helper1' => $helper1 ? $helper1->firstname.' '.$helper1->lastname : null,
            'helper2' => $helper2 ? $helper2->firstname.' '.$helper2->lastname : null,
            'days' => is_array($group->days) ? $group->days : (empty($group->days)?[]:json_decode($group->days,true)),
            'schedulings' => $groupSchedulings->map(fn($s) => [
                'id' => $s->id,
                'date' => $s->date,
                'status' => $s->status,
                'notes' => $s->notes
            ])->values()
        ];
    })->values();

    return response()->json(['success' => true, 'groups' => $data]);
}

/**
 * Actualizar en lote los registros editados.
 */
public function updateMassive(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'status' => 'nullable|integer|in:0,1,2,3',
        'notes' => 'nullable|string|max:500',
        'zone_id' => 'nullable|exists:zones,id',
        'vehicle_id' => 'nullable|exists:vehicles,id',
    ]);

    $changes = array_filter([
        'status' => $request->status,
        'notes'  => $request->notes,
        'zone_id'    => $request->zone_id,
        'vehicle_id' => $request->vehicle_id,
    ], fn($v) => !is_null($v) && $v !== '');

    Scheduling::whereIn('id', $request->ids)->update($changes);

    return response()->json([
        'success' => true,
        'message' => 'Programaciones actualizadas correctamente'
    ]);
}


    /**
     * Formatear mensajes de error de conflictos de vacaciones agrupados
     */
    private function formatVacationConflictMessages($vacationConflicts)
    {
        if (empty($vacationConflicts)) {
            return '';
        }

        $errorMessages = [];

        // Agrupar conflictos por usuario y vacación
        $groupedConflicts = [];
        foreach ($vacationConflicts as $conflict) {
            $userName = $conflict['user']->firstname . ' ' . $conflict['user']->lastname;
            $key = $userName . '|' . $conflict['vacation_start'] . '|' . $conflict['vacation_end'];

            if (!isset($groupedConflicts[$key])) {
                $groupedConflicts[$key] = [
                    'user_name' => $userName,
                    'vacation_start' => $conflict['vacation_start'],
                    'vacation_end' => $conflict['vacation_end'],
                    'conflict_dates' => []
                ];
            }

            $groupedConflicts[$key]['conflict_dates'][] = $conflict['conflict_date'];
        }

        // Generar mensajes agrupados
        foreach ($groupedConflicts as $groupedConflict) {
            $datesString = implode(', ', $groupedConflict['conflict_dates']);
            $errorMessages[] = "El empleado {$groupedConflict['user_name']} tiene vacaciones del {$groupedConflict['vacation_start']} al {$groupedConflict['vacation_end']} y está intentando crear una programación para el {$datesString}.";
        }

        return implode(' ', $errorMessages);
    }
}
