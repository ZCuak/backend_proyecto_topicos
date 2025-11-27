<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HistoryController;
use App\Models\Scheduling;
use App\Models\EmployeeGroup;
use App\Models\Schedule;
use App\Models\Vehicle;
use App\Models\Zone;
use App\Models\SchedulingDetail;
use App\Models\Audit;
use App\Models\Attendace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ConfigGroup;
use App\Traits\HistoryChanges;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SchedulingController extends Controller
{
    use HistoryChanges;
    /**
     * Listar programaciones (Web) - Vista de tabla
     */
 public function index(Request $request)
{
    $search = $request->input('search');
    $perPage = $request->input('perPage', 10);
    $dateFrom = $request->input('date_from');
    $dateTo = $request->input('date_to');
    $zoneFilter = $request->input('zone_filter'); // FILTRO POR ZONA REAL
    $hasCustomDateFilters = $request->filled('date_from') || $request->filled('date_to');

    // Por defecto mostrar solo las programaciones de hoy
    if (!$hasCustomDateFilters) {
        $today = Carbon::today()->toDateString();
        $dateFrom = $today;
        $dateTo = $today;
    }

    $query = Scheduling::with(['group', 'schedule', 'vehicle', 'zone', 'details.user']);

    // Busqueda general
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

    // Filtro por rango de fechas
    if ($dateFrom && $dateTo) {
        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate = Carbon::parse($dateTo)->endOfDay();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $query->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
    } elseif ($dateFrom) {
        $query->whereDate('date', '>=', $dateFrom);
    } elseif ($dateTo) {
        $query->whereDate('date', '<=', $dateTo);
    }

    // FILTRO POR ZONA REAL
    if ($zoneFilter) {
        $query->where('zone_id', $zoneFilter);
    }

    $appends = [
        'search' => $search,
        'perPage' => $perPage,
        'zone_filter' => $zoneFilter, // FILTRO POR ZONA REAL
    ];

    if ($hasCustomDateFilters) {
        $appends['date_from'] = $dateFrom;
        $appends['date_to'] = $dateTo;
    }

    $schedulings = $query->orderBy('date', 'asc')->orderBy('id', 'desc')
        ->paginate($perPage)
        ->appends($appends);

    // Enviar zonas a la vista
    $zones = \App\Models\Zone::orderBy('name')->get();

    return view('schedulings.index', compact('schedulings', 'search', 'dateFrom', 'dateTo', 'zoneFilter', 'zones', 'hasCustomDateFilters'));
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
            ->map(function ($scheduling) {
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
        $groups = EmployeeGroup::with(['configgroups.user'])->orderBy('name')->get();
        $schedules = Schedule::orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'DISPONIBLE')->orderBy('name')->get();
        $zones = Zone::orderBy('name')->get();
        $employees = User::orderBy('firstname')->orderBy('lastname')->get();

        return view('schedulings._modal_create', compact('groups', 'schedules', 'vehicles', 'zones', 'employees'));
    }

    /**
     * Almacenar nueva programación (Web)
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        Log::info('=== CREATE SCHEDULING RANGE DEBUG ===');
        Log::info('Request data:', $request->all());

        // VALIDACION BASE
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:employeegroups,id',
            'schedule_id' => 'required|exists:schedules,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'zone_id' => 'nullable|exists:zones,id',
            'date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:date',
            'status' => 'nullable|integer|in:0,1,2,3',
            'notes' => 'nullable|string|max:500',
            'days' => 'nullable|array',
            'days.*' => 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'
        ]);

        if ($validator->fails()) {
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $start = Carbon::parse($data['date']);
        $end = $request->end_date ? Carbon::parse($request->end_date) : null;

        // ESTADO POR DEFECTO
        $data['status'] = 0;


        // Validaciones extras
        if ($err = $this->validateGroupHasActiveContracts($data['group_id'], $isTurbo))
            return $err;
        if ($err = $this->validateVacationConflicts($data['group_id'], [$data['date']], $isTurbo))
            return $err;

        DB::beginTransaction();

        try {

            // ╔══════════════════════════════════╗
            // ║   A) UNA SOLA PROGRAMACIÓN       ║
            // ╚══════════════════════════════════╝
            if (!$end) {

                $conflict = Scheduling::where('date', $data['date'])
                    ->where('group_id', $data['group_id'])
                    ->where('schedule_id', $data['schedule_id'])
                    ->where(function ($q) use ($data) {
                        if ($data['zone_id'])
                            $q->where('zone_id', $data['zone_id']);
                        else
                            $q->whereNull('zone_id');
                    })
                    ->where(function ($q) use ($data) {
                        if ($data['vehicle_id'])
                            $q->where('vehicle_id', $data['vehicle_id']);
                        else
                            $q->whereNull('vehicle_id');
                    })
                    ->first();

                if ($conflict)
                    throw new \Exception("Ya existe una programación con este grupo, horario, zona y vehículo para esta fecha.");

                // Crear
                $s = Scheduling::create($data);
                $this->createSchedulingDetails($s->id, $data['group_id']);

                DB::commit();

                if ($isTurbo) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Programación creada correctamente.'
                    ]);
                }

                return redirect()->route('schedulings.index')
                    ->with('success', 'Programación creada correctamente.');
            }



            // ╔══════════════════════════════════╗
            // ║   B) RANGO DE FECHAS             ║
            // ╚══════════════════════════════════╝

            $daysSelected = $data['days'] ?? null;
            $datesToCreate = [];

            // MAPEO DE DÍAS
            $map = [
                'monday' => 'lunes',
                'tuesday' => 'martes',
                'wednesday' => 'miercoles',
                'thursday' => 'jueves',
                'friday' => 'viernes',
                'saturday' => 'sabado',
                'sunday' => 'domingo',
                'lunes' => 'lunes',
                'martes' => 'martes',
                'miercoles' => 'miercoles',
                'jueves' => 'jueves',
                'viernes' => 'viernes',
                'sabado' => 'sabado',
                'domingo' => 'domingo'
            ];

            // GENERAR FECHAS VÁLIDAS
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {

                $dayName = strtolower(Str::ascii($date->dayName));
                $dayKey = $map[$dayName] ?? $dayName;

                if ($daysSelected && !in_array($dayKey, $daysSelected))
                    continue;

                $datesToCreate[] = $date->format('Y-m-d');
            }

            Log::info("Fechas generadas para creación:", $datesToCreate);

            // CONTADORES
            $created = 0;
            $conflicted = 0;

            // CREAR POR CADA FECHA
            foreach ($datesToCreate as $fecha) {

                $data['date'] = $fecha;

                $conflict = Scheduling::where('date', $fecha)
                    ->where('group_id', $data['group_id'])
                    ->where('schedule_id', $data['schedule_id'])
                    ->where(function ($q) use ($data) {
                        if ($data['zone_id'])
                            $q->where('zone_id', $data['zone_id']);
                        else
                            $q->whereNull('zone_id');
                    })
                    ->where(function ($q) use ($data) {
                        if ($data['vehicle_id'])
                            $q->where('vehicle_id', $data['vehicle_id']);
                        else
                            $q->whereNull('vehicle_id');
                    })
                    ->first();

                if ($conflict) {
                    $conflicted++;
                    continue;
                }

                // Crear programación
                $s = Scheduling::create($data);
                $this->createSchedulingDetails($s->id, $data['group_id']);
                $created++;
            }

            // ❗ SI NO SE CREÓ NADA → ERROR
            if ($created === 0) {
                throw new \Exception("No se creó ninguna programación. Todas las fechas seleccionadas ya tienen programación.");
            }

            DB::commit();

            // RESPUESTA FINAL
            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'created' => $created,
                    'conflicts' => $conflicted,
                    'message' => 'Programaciones creadas correctamente.'
                ]);
            }

            return redirect()->route('schedulings.index')
                ->with('success', 'Programaciones creadas correctamente.');

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Error creating scheduling: ' . $e->getMessage());

            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => "Error: " . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', "Error: " . $e->getMessage());
        }
    }



    /**
     * Mostrar programación específica (Web)
     */
    public function show(Scheduling $scheduling)
    {
        // Cargamos relaciones principales
        $scheduling->load(['group', 'schedule', 'vehicle', 'zone']);

        // ? Cargamos los detalles de los empleados asignados a esta programaci�n
        $details = \App\Models\SchedulingDetail::with(['user', 'userType'])
            ->where('scheduling_id', $scheduling->id)
            ->orderBy('position_order')
            ->get();

        // Asistencias registradas para la fecha de la programaci�n
        $dateString = is_string($scheduling->date) ? $scheduling->date : $scheduling->date->format('Y-m-d');
        $attendancesByUser = Attendace::whereDate('date', $dateString)->get()->keyBy('user_id');

        // ? Historial de cambios
        $audits = HistoryController::getHistory('PROGRAMACION', $scheduling->id);

        return view('schedulings.show', compact('scheduling', 'details', 'audits', 'attendancesByUser'));
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

        // 🔹 Empleados asignados
        $assigned = \App\Models\SchedulingDetail::with('user')
            ->where('scheduling_id', $scheduling->id)
            ->orderBy('position_order')
            ->get();

        // 🔹 Todos los empleados que existen
        $allEmployees = \App\Models\User::orderBy('firstname')->get();

        return view('schedulings._modal_edit', compact(
            'scheduling',
            'groups',
            'schedules',
            'vehicles',
            'zones',
            'assigned',
            'allEmployees'
        ));
    }


    /**
     * Actualizar programación (Web)
     */
    public function update(Request $request, Scheduling $scheduling)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:employeegroups,id',
            'schedule_id' => 'required|exists:schedules,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'zone_id' => 'nullable|exists:zones,id',
            'date' => 'required|date',
            'status' => 'nullable|integer|in:0,1,2,3',
            'add_notes' => 'nullable|string|max:2000',
            'assigned_json' => 'nullable|string',
            'days' => 'nullable|array',
            'days.*' => 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo'
        ]);

        if ($validator->fails()) {
            return $isTurbo
                ? response()->json(['success' => false, 'message' => 'Errores de validación.', 'errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            // =============================
            // 🔹 Defaults
            // =============================
            if (!isset($data['date']) || empty($data['date'])) {
                $data['date'] = now()->format('Y-m-d');
            }
            if (!isset($data['status']) || $data['status'] === '') {
                $data['status'] = 0;
            }

            // =============================
            // 🔹 Validar conflicto de vacaciones
            // =============================
            $vacationValidation = $this->validateVacationConflicts($data['group_id'], [$data['date']], $isTurbo);
            if ($vacationValidation)
                return $vacationValidation;

            // =============================
            // 🔹 Validar duplicados
            // =============================
            $exists = Scheduling::where('date', $data['date'])
                ->where('group_id', $data['group_id'])
                ->where('schedule_id', $data['schedule_id'])
                ->where('id', '!=', $scheduling->id)
                ->first();

            if ($exists) {
                $msg = 'Ya existe una programación para este grupo y horario en esta fecha.';
                return $isTurbo
                    ? response()->json(['success' => false, 'message' => $msg, 'errors' => ['date' => [$msg]]], 422)
                    : back()->withErrors(['date' => $msg])->withInput();
            }

            // =============================
            // 🔹 Validar conflicto vehículo
            // =============================
            if ($data['vehicle_id']) {

                $vehicleConflict = Scheduling::where('date', $data['date'])
                    ->where('vehicle_id', $data['vehicle_id'])
                    ->where('schedule_id', $data['schedule_id'])
                    ->where('id', '!=', $scheduling->id)
                    ->first();

                if ($vehicleConflict) {
                    $msg = 'El vehículo seleccionado ya está programado en esta fecha y horario.';
                    return $isTurbo
                        ? response()->json(['success' => false, 'message' => $msg, 'errors' => ['vehicle_id' => [$msg]]], 422)
                        : back()->withErrors(['vehicle_id' => $msg])->withInput();
                }
            }

            // =============================
            // 🔹 GUARDAR CAMBIOS PRINCIPALES
            // =============================
            $originalData = $scheduling->getOriginal();

            // Mapear notas enviadas (add_notes o notes) hacia campos concretos para la auditoría
            $notesForAudit = [];
            $rawNotes = $request->input('add_notes', $request->input('notes'));

            if ($rawNotes) {
                $parsedNotes = is_string($rawNotes) ? json_decode($rawNotes, true) : $rawNotes;

                if (is_array($parsedNotes)) {
                    foreach ($parsedNotes as $noteRow) {
                        $tipo = strtolower($noteRow['tipo'] ?? '');
                        $nota = $noteRow['notas'] ?? null;
                        if (!$nota) {
                            continue;
                        }

                        switch ($tipo) {
                            case 'turno':
                                $notesForAudit['schedule_id'] = $nota;
                                break;
                            case 'vehículo':
                            case 'vehiculo':
                                $notesForAudit['vehicle_id'] = $nota;
                                break;
                            case 'reemplazo personal':
                                $notesForAudit['group_id'] = $nota;
                                break;
                            default:
                                $notesForAudit['notes'] = $nota;
                                break;
                        }
                    }
                } elseif (is_string($rawNotes)) {
                    // Si llega texto plano, asociarlo al campo notes genérico
                    $notesForAudit['notes'] = $rawNotes;
                }
            }

            $scheduling->update($data);

            // =============================
            // 🔥 🔥 🔥 ACTUALIZAR PERSONAL (scheduling_details)
            // =============================
            if ($request->filled('assigned_json')) {

                $assigned = json_decode($request->assigned_json, true);

                if (is_array($assigned)) {

                    foreach ($assigned as $detail) {

                        $detailId = $detail['detail_id'] ?? null;
                        $newUserId = $detail['user_id'] ?? null;

                        if ($detailId && $newUserId) {
                            $detailModel = SchedulingDetail::where('id', $detailId)->first();
                            $oldUserId = $detailModel?->user_id;

                            SchedulingDetail::where('id', $detailId)
                                ->update([
                                    'user_id' => $newUserId
                                ]);

                            if ((string) $oldUserId !== (string) $newUserId) {
                                $roleLabel = $detailModel->role_name ?? 'Personal';
                                $this->registrarCambioDetalle(
                                    $scheduling,
                                    $roleLabel,
                                    $oldUserId,
                                    $newUserId,
                                    $request->add_notes ?? null
                                );
                            }
                        }
                    }
                }
            }

            // =============================
            // 🔹 Registrar historial
            // =============================
            $exceptFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'type'];
            $this->registrarCambios($scheduling, $originalData, $notesForAudit, $exceptFields);

            return $isTurbo
                ? response()->json(['success' => true, 'message' => 'Programación actualizada exitosamente.'], 200)
                : redirect()->route('schedulings.index')->with('success', 'Programación actualizada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error updating scheduling: ' . $e->getMessage());
            return $isTurbo
                ? response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500)
                : back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
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
        $groups = EmployeeGroup::with(['vehicle', 'schedule', 'configgroups.user'])->orderBy('name')->get();
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
                        $dayMap = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado', 0 => 'domingo'];
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
                            'vehicle_id' => $group->vehicle_id ?? null,
                            'zone_id' => $group->zone_id ?? null,
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
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:employeegroups,id',
            'all_groups' => 'nullable|boolean',
            'filter_schedule' => 'nullable|exists:schedules,id',
            'schedule_id' => 'nullable|exists:schedules,id',
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
        
        // Debug: Log para ver qué parámetros llegan
        \Log::info('=== VALIDATE MASSIVE - REQUEST DATA ===');
        \Log::info('group_ids recibido:', ['group_ids' => $data['group_ids'] ?? 'NO ENVIADO']);
        \Log::info('filter_schedule:', ['filter_schedule' => $data['filter_schedule'] ?? 'NO ENVIADO']);
        \Log::info('all_groups:', ['all_groups' => $data['all_groups'] ?? 'NO ENVIADO']);
        \Log::info('group_id:', ['group_id' => $data['group_id'] ?? 'NO ENVIADO']);
        
        $startDate = \Carbon\Carbon::parse($data['start_date']);
        $endDate = \Carbon\Carbon::parse($data['end_date']);
        $excludeWeekends = $data['exclude_weekends'] ?? false;
        $excludeSpecificDates = isset($data['exclude_specific_dates']) && $data['exclude_specific_dates']
            ? array_map('trim', explode(',', $data['exclude_specific_dates']))
            : [];

        // Allow filtering groups by specific IDs (group_ids), schedule (filter_schedule), group_id, or all_groups
        if (!empty($data['group_ids']) && is_array($data['group_ids'])) {
            // Si se envían IDs específicos, solo validar esos grupos
            \Log::info('Usando group_ids, cantidad:', ['count' => count($data['group_ids']), 'ids' => $data['group_ids']]);
            $groups = EmployeeGroup::whereIn('id', $data['group_ids'])->orderBy('name')->get();
        } elseif (isset($data['filter_schedule']) && $data['filter_schedule'] !== '') {
            \Log::info('Usando filter_schedule');
            $groups = EmployeeGroup::where('schedule_id', $data['filter_schedule'])->orderBy('name')->get();
        } elseif (!empty($data['all_groups']) && $data['all_groups']) {
            \Log::info('Usando all_groups');
            $groups = EmployeeGroup::orderBy('name')->get();
        } elseif (!empty($data['group_id'])) {
            \Log::info('Usando group_id único');
            $groups = EmployeeGroup::where('id', $data['group_id'])->get();
        } else {
            $errorMessage = 'Debe especificar un grupo o marcar programación para todos los grupos.';
            if ($isTurbo) {
                return response()->json(['success' => false, 'message' => $errorMessage], 422);
            }
            return back()->withErrors(['group_id' => $errorMessage])->withInput();
        }
        
        \Log::info('Grupos obtenidos:', ['count' => $groups->count(), 'ids' => $groups->pluck('id')->toArray()]);

        $results = [];

        $programmingDates = ['start' => $data['start_date'], 'end' => $data['end_date']];

        foreach ($groups as $group) {
            $groupResult = ['group_id' => $group->id, 'group_name' => $group->name, 'inconsistencies' => [], 'created' => 0];

            // Validación 0: Verificar que el grupo esté activo
            if (isset($group->status) && strtoupper($group->status) !== 'ACTIVO') {
                $groupResult['inconsistencies'][] = 'El grupo no está activo (estado: ' . $group->status . ').';
                $groupResult['ok'] = false;
                $results[] = $groupResult;
                continue;
            }

            // Validación 1: Verificar que el grupo tenga usuarios asignados
            $groupUsers = ConfigGroup::where('group_id', $group->id)->with('user')->get();
            if ($groupUsers->isEmpty()) {
                $groupResult['inconsistencies'][] = 'El grupo no tiene usuarios asignados.';
                $groupResult['ok'] = false;
                $results[] = $groupResult;
                continue;
            }

            // Validación 2: Verificar que el grupo tenga un conductor (primer usuario) y que esté activo
            $driver = $groupUsers->first();
            if (!$driver || !$driver->user) {
                $groupResult['inconsistencies'][] = 'El grupo no tiene un conductor asignado.';
                $groupResult['ok'] = false;
                $results[] = $groupResult;
                continue;
            }

            // Verificar que todos los usuarios del grupo estén activos
            foreach ($groupUsers as $configGroup) {
                $user = $configGroup->user;
                if (isset($user->status) && strtoupper($user->status) !== 'ACTIVO') {
                    $groupResult['inconsistencies'][] = "El usuario '{$user->firstname} {$user->lastname}' no está activo (estado: {$user->status}).";
                }
            }

            if (!empty($groupResult['inconsistencies'])) {
                $groupResult['ok'] = false;
                $results[] = $groupResult;
                continue;
            }

            // Validación 3: Verificar que el vehículo del grupo esté operativo (si tiene asignado)
            if (!empty($group->vehicle_id)) {
                $vehicle = Vehicle::find($group->vehicle_id);
                if ($vehicle && isset($vehicle->status) && strtoupper($vehicle->status) !== 'DISPONIBLE') {
                    $groupResult['inconsistencies'][] = "El vehículo '{$vehicle->name}' del grupo no está disponible (estado: {$vehicle->status}).";
                    $groupResult['ok'] = false;
                    $results[] = $groupResult;
                    continue;
                }
            }

            // Validación 4: Verificar que la zona del grupo esté activa (si tiene asignada)
            if (!empty($group->zone_id)) {
                $zone = Zone::find($group->zone_id);
                if ($zone && isset($zone->status) && strtoupper($zone->status) !== 'ACTIVO') {
                    $groupResult['inconsistencies'][] = "La zona '{$zone->name}' del grupo no está activa (estado: {$zone->status}).";
                    $groupResult['ok'] = false;
                    $results[] = $groupResult;
                    continue;
                }
            }

            // Validación 5: Verificar que el horario del grupo coincida con el de la programación
            if ($group->schedule_id && $data['schedule_id'] && $group->schedule_id != $data['schedule_id']) {
                $groupSchedule = Schedule::find($group->schedule_id);
                $programmingSchedule = Schedule::find($data['schedule_id']);
                $groupResult['inconsistencies'][] = "El grupo está configurado para el horario '{$groupSchedule->name}' pero se intenta programar para '{$programmingSchedule->name}'.";
                $groupResult['ok'] = false;
                $results[] = $groupResult;
                continue;
            }

            // Validación 6: Verificar que el grupo tenga días configurados
            $groupDays = is_array($group->days) ? $group->days : (empty($group->days) ? [] : json_decode($group->days, true));
            if (empty($groupDays)) {
                $groupResult['inconsistencies'][] = 'El grupo no tiene días de trabajo configurados.';
                $groupResult['ok'] = false;
                $results[] = $groupResult;
                continue;
            }

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
                if ($excludeWeekends && $currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }
                $dateString = $currentDate->format('Y-m-d');
                if (in_array($dateString, $excludeSpecificDates)) {
                    $currentDate->addDay();
                    continue;
                }

                $selectedDays = $data['days'] ?? [];
                if (!empty($selectedDays)) {
                    $dayOfWeek = $currentDate->dayOfWeek;
                    $dayMap = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado', 0 => 'domingo'];
                    $dayInSpanish = $dayMap[$dayOfWeek] ?? '';
                    if (!in_array($dayInSpanish, $selectedDays)) {
                        $currentDate->addDay();
                        continue;
                    }
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
                        $vehicle = Vehicle::find($data['vehicle_id']);
                        $conflictGroup = $vehicleConflict->group;
                        $groupResult['inconsistencies'][] = "El vehículo '{$vehicle->name}' está ocupado el {$dateString} por el grupo '{$conflictGroup->name}'.";
                        $currentDate->addDay();
                        continue;
                    }
                }

                // Validar que el vehículo del grupo esté disponible (si tiene asignado)
                if (empty($data['vehicle_id']) && !empty($group->vehicle_id)) {
                    $vehicleConflict = Scheduling::where('date', $dateString)
                        ->where('vehicle_id', $group->vehicle_id)
                        ->where('schedule_id', $data['schedule_id'])
                        ->first();
                    if ($vehicleConflict) {
                        $vehicle = $group->vehicle;
                        $conflictGroup = $vehicleConflict->group;
                        $groupResult['inconsistencies'][] = "El vehículo '{$vehicle->name}' del grupo está ocupado el {$dateString} por el grupo '{$conflictGroup->name}'.";
                        $currentDate->addDay();
                        continue;
                    }
                }

                // Validar que los usuarios del grupo no estén programados en otro grupo al mismo tiempo
                foreach ($groupUsers as $configGroup) {
                    $userId = $configGroup->user_id;
                    $userConflict = SchedulingDetail::whereHas('scheduling', function($q) use ($dateString, $data) {
                        $q->where('date', $dateString)
                          ->where('schedule_id', $data['schedule_id']);
                    })->where('user_id', $userId)->first();

                    if ($userConflict) {
                        $conflictScheduling = $userConflict->scheduling;
                        $conflictGroup = $conflictScheduling->group;
                        $userName = $configGroup->user->firstname . ' ' . $configGroup->user->lastname;
                        $groupResult['inconsistencies'][] = "El usuario '{$userName}' ya está programado el {$dateString} en el grupo '{$conflictGroup->name}'.";
                        $currentDate->addDay();
                        continue 2; // Saltar al siguiente día
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
            if (!empty($data['user1_id']))
                $order[] = $data['user1_id'];
            if (!empty($data['user2_id']))
                $order[] = $data['user2_id'];

            foreach ($order as $userId) {
                ConfigGroup::create(['group_id' => $group->id, 'user_id' => $userId]);
            }

            DB::commit();

            // Return updated info
            $group->load(['vehicle', 'configgroups.user']);
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
            ->with([
                'user.contracts' => function ($query) {
                    $query->where('is_active', true);
                }
            ])
            ->get()
            ->filter(function ($configGroup) {
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
            ->with([
                'user.contracts' => function ($query) {
                    $query->where('is_active', true);
                }
            ])
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
     * Registrar cambio de personal en historial
     */
    private function registrarCambioDetalle(Scheduling $scheduling, string $rol, $oldUserId, $newUserId, ?string $nota = null): void
    {
        $auditTypeName = $this->getAuditTypeName($scheduling);
        $userName = auth()->check() ? auth()->user()->username : 'Sistema/Invitado';

        Audit::create([
            'auditable_type' => $auditTypeName,
            'auditable_id' => $scheduling->id,
            'campo_modificado' => $rol,
            'valor_anterior' => $this->nombreUsuario($oldUserId),
            'valor_nuevo' => $this->nombreUsuario($newUserId),
            'user_name' => $userName,
            'nota_adicional' => $nota,
        ]);
    }

    private function nombreUsuario($userId): string
    {
        if (!$userId) {
            return 'SIN ASIGNAR';
        }

        $user = User::find($userId);
        return $user ? trim($user->firstname . ' ' . $user->lastname) : 'ID ' . $userId;
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
    /**
     * ==========================================================
     * 🔹 EDITAR MASIVO — MOSTRAR MODAL
     * ==========================================================
     */
    public function editMassive(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'zone_id' => 'nullable|exists:zones,id',
            'schedule_id' => 'nullable|exists:schedules,id',
            'change_type' => 'nullable|string|in:driver,occupant,turn,vehicle',
        ]);

        $schedules = Schedule::orderBy('name')->get();
        $groups = EmployeeGroup::orderBy('name')->get();
        $users = User::orderBy('firstname')->get();
        $vehicles = Vehicle::orderBy('name')->get();
        $zones = Zone::orderBy('name')->get();

        $programaciones = collect();

        if ($request->filled(['start_date', 'end_date'])) {
            $programaciones = Scheduling::with(['group', 'schedule', 'vehicle', 'zone'])
                ->whereBetween('date', [$request->start_date, $request->end_date]);

            if ($request->filled('zone_id')) {
                $programaciones->where('zone_id', $request->zone_id);
            }

            if ($request->filled('schedule_id')) {
                $programaciones->where('schedule_id', $request->schedule_id);
            }

            $programaciones = $programaciones->orderBy('date')->get();
        }

        return view('schedulings.massive._modal_edit', compact(
            'schedules',
            'groups',
            'users',
            'vehicles',
            'zones',
            'programaciones'
        ));
    }



    /**
     * ==========================================================
     * 🔹 BUSCAR PROGRAMACIONES MASIVAS (AJAX)
     * ==========================================================
     */
    public function fetchMassive(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'zone_id' => 'nullable|exists:zones,id',
            'schedule_id' => 'nullable|exists:schedules,id',
        ]);

        $start = \Carbon\Carbon::parse($request->start_date);
        $end = \Carbon\Carbon::parse($request->end_date);

        $query = Scheduling::with([
            'group.vehicle',
            'group.zone',
            'group.configgroups.user',
            'schedule',
            'vehicle',
            'details.user',
        ])->whereBetween('date', [$start, $end]);

        if ($request->filled('schedule_id')) {
            $query->where('schedule_id', $request->schedule_id);
        }

        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }

        $schedulings = $query->get()->groupBy('group_id');

        $data = $schedulings->map(function ($groupSchedulings) {
            $group = $groupSchedulings->first()->group;

            $config = $group->configgroups->sortBy('id')->values();
            $driver = $config->get(0)?->user;
            $helper1 = $config->get(1)?->user;
            $helper2 = $config->get(2)?->user;

            return [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'zone' => optional($group->zone)->name,
                'vehicle' => optional($group->vehicle)->name,
                'driver' => $driver ? $driver->firstname . ' ' . $driver->lastname : null,
                'helper1' => $helper1 ? $helper1->firstname . ' ' . $helper1->lastname : null,
                'helper2' => $helper2 ? $helper2->firstname . ' ' . $helper2->lastname : null,
                'schedulings' => $groupSchedulings->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'date' => $s->date,
                        'status' => $s->status,
                        'notes' => $s->notes,
                        'vehicle_id' => $s->vehicle_id,
                        'vehicle_label' => $s->vehicle ? $s->vehicle->plate . ' - ' . $s->vehicle->name : null,
                        'schedule_id' => $s->schedule_id,
                        'schedule_name' => $s->schedule?->name,
                        'assigned' => $s->details->map(function ($d) {
                            return [
                                'detail_id' => $d->id,
                                'user_id' => $d->user_id,
                                'usertype' => $d->usertype_id,
                                'name' => $d->user ? $d->user->firstname . ' ' . $d->user->lastname : 'Sin asignar',
                            ];
                        })->values(),
                    ];
                })->values()
            ];
        })->values();

        return response()->json([
            'success' => true,
            'groups' => $data
        ]);
    }



    /**
     * ==========================================================
     * 🔹 APLICAR CAMBIOS MASIVOS
     * ==========================================================
     * El payload esperado:
     * {
     *    updates: [
     *        {
     *           id: 17,
     *           schedule_id: 1,
     *           vehicle_id: 5,
     *           notes: "...",
     *           changes: [ registro para historial ],
     *           assigned_json: [
     *               {detail_id: 5, user_id: 22, usertype: 1},
     *               {detail_id: 6, user_id: 30, usertype: 2}
     *           ]
     *        },
     *        ...
     *    ]
     * }
     */
    public function updateMassive(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:schedulings,id',
            'updates.*.schedule_id' => 'nullable|exists:schedules,id',
            'updates.*.vehicle_id' => 'nullable|exists:vehicles,id',
            'updates.*.notes' => 'nullable|string|max:500',
            'updates.*.assigned_json' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {

            foreach ($request->updates as $item) {

                $scheduling = Scheduling::find($item['id']);
                $original = $scheduling->getOriginal();

                // ======================================
                // 🔹 ACTUALIZAR CAMPOS PRINCIPALES
                // ======================================
                $updateData = [];

                if (!empty($item['schedule_id'])) {
                    $updateData['schedule_id'] = $item['schedule_id'];
                }

                if (array_key_exists('vehicle_id', $item)) {
                    $updateData['vehicle_id'] = $item['vehicle_id'];
                }

                if (!empty($item['notes'])) {
                    $updateData['notes'] = $item['notes'];
                }

                if ($updateData) {
                    $scheduling->update($updateData);
                }

                // ======================================
                // 🔹 ACTUALIZAR PERSONAL (DETAILS)
                // ======================================
                if (!empty($item['assigned_json'])) {

                    foreach ($item['assigned_json'] as $row) {

                        $detail = SchedulingDetail::find($row['detail_id']);
                        if (!$detail)
                            continue;

                        $oldUserId = $detail->user_id;

                        $detail->update([
                            'user_id' => $row['user_id'],
                            'usertype_id' => $row['usertype'],
                        ]);

                        if ((string) $oldUserId !== (string) $row['user_id']) {
                            $roleLabel = $detail->role_name ?? 'Personal';
                            $notaCambio = null;
                            if (!empty($item['changes']) && is_array($item['changes'])) {
                                $notaCambio = $item['changes'][0]['notas'] ?? null;
                            }
                            $this->registrarCambioDetalle(
                                $scheduling,
                                $roleLabel,
                                $oldUserId,
                                $row['user_id'],
                                $notaCambio
                            );
                        }
                    }
                }

                // ======================================
                // 🔹 REGISTRAR HISTORIAL (MISMA FUNCIÓN)
                // ======================================
                if (!empty($item['changes'])) {
                    $this->registrarCambios(
                        $scheduling,
                        $original,
                        [],
                        ['id', 'created_at', 'updated_at', 'deleted_at']
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cambios aplicados correctamente.'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error("Error updateMassive: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar cambios masivos: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Finalizar una programaci�n (estado completado)
     */
    public function finalize(Request $request, Scheduling $scheduling)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        if (in_array((int) $scheduling->status, [2, 3])) {
            $msg = $scheduling->status == 2
                ? 'Esta programaci�n ya est� finalizada.'
                : 'No se puede finalizar una programaci�n cancelada.';

            return $isTurbo
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $scheduling->update(['status' => 2]);

        return $isTurbo
            ? response()->json(['success' => true, 'message' => 'Programaci�n finalizada.'])
            : back()->with('success', 'Programaci�n finalizada correctamente.');
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
