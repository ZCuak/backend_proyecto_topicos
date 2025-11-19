<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendace;
use App\Models\Schedule;
use App\Models\Scheduling;
use App\Models\SchedulingDetail;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    /**
     * Dashboard principal con filtro de fecha y turno
     */
    public function index(Request $request)
    {
        // Fecha: por defecto HOY, no permite fechas anteriores
        $selectedDate = $request->input('date', now()->format('Y-m-d'));

        // Validar que no sea fecha pasada
        if (Carbon::parse($selectedDate)->isBefore(now()->startOfDay())) {
            $selectedDate = now()->format('Y-m-d');
        }

        // Turno: por defecto TODOS
        $selectedScheduleId = $request->input('schedule_id');

        // Obtener programaciones del día (o rango que incluya el día)
        $schedulings = $this->getSchedulings($selectedDate, $selectedScheduleId);

        $debugData = [];
        $zonesData = collect();
        // Procesar zonas usando helpers
        $pendingZones = $this->processPendingZones($schedulings, $selectedDate);
        $groupedBySchedule = $this->processActiveCompletedZones($schedulings, $selectedDate);
        // MOSTRAR TODO AL FINAL
        // dd($debugData);

        //Estadísticas
        $stats = $this->calculateStats($pendingZones, $groupedBySchedule);

        // Obtener turnos para el filtro
        $schedules = Schedule::all();

        return view('welcome', compact(
            'pendingZones',
            'groupedBySchedule',
            'schedules',
            'stats',
            'selectedDate',
            'selectedScheduleId'
        ));
    }

    /**
     * Analizar una programación y verificar disponibilidad de personal
     */
    private function analyzeScheduling(Scheduling $scheduling, $date, &$debugData)
    {
        $details = $scheduling->details;

        if ($details->isEmpty()) {
            return [
                'scheduling' => $scheduling,
                'status' => 'not_ready',
                'reason' => 'Sin personal asignado',
                'absent_personnel' => [],
                'present_personnel' => [],
                'absent_count' => 0,
                'date' => $date,
            ];
        }

        $schedule = $scheduling->schedule;
        $shiftStartTime = Carbon::parse($schedule->time_start)->format('H:i');
        $shiftEndTime = Carbon::parse($schedule->time_end)->format('H:i');

        // 🔍 Inicializar debug para esta programación
        $currentDebug = [
            '🏢 ZONA' => $scheduling->zone->name,
            '🕐 TURNO' => $schedule->name,
            '⏰ HORARIO' => "$shiftStartTime - $shiftEndTime",
            '📍 TOLERANCIA_DESDE' => Carbon::createFromFormat('H:i', $shiftStartTime)->subHours(2)->format('H:i'),
            '👥 PERSONAL' => [],
        ];

        $absentPersonnel = [];
        $presentPersonnel = [];

        foreach ($details as $detail) {
            $userName = $detail->user->firstname . ' ' . $detail->user->lastname;

            // 🔍 Inicializar debug del trabajador
            $workerDebug = [
                '👤 NOMBRE' => $userName,
                '🎭 ROL' => $detail->getRoleNameAttribute(),
                '📋 ASISTENCIAS_ENCONTRADAS' => [],
                '🔍 VALIDACIONES' => [],
                '✅ ASISTENCIA_SELECCIONADA' => null,
                '🚪 TIENE_SALIDA' => null,
                '📊 RESULTADO_FINAL' => null,
            ];

            $attendances = Attendace::where('user_id', $detail->user_id)
                ->whereDate('date', $date)
                ->whereNotNull('check_in')
                ->orderBy('check_in', 'asc')
                ->get();

            // 🔍 Registrar asistencias encontradas
            $workerDebug['📋 ASISTENCIAS_ENCONTRADAS'] = $attendances->map(function ($att) {
                return [
                    'entrada' => Carbon::parse($att->check_in)->format('H:i:s'),
                    'salida' => $att->check_out ? Carbon::parse($att->check_out)->format('H:i:s') : 'Pendiente',
                ];
            })->toArray();

            $relevantAttendance = null;

            foreach ($attendances as $attendance) {
                $checkInTime = Carbon::parse($attendance->check_in)->format('H:i');

                $isWithinShift = $this->isTimeWithinShift(
                    $checkInTime,
                    $shiftStartTime,
                    $shiftEndTime
                );

                // 🔍 Registrar cada validación
                $workerDebug['🔍 VALIDACIONES'][] = [
                    'entrada' => $checkInTime,
                    'es_valida_para_turno' => $isWithinShift ? '✅ SÍ' : '❌ NO',
                ];

                if ($isWithinShift) {
                    $relevantAttendance = $attendance;
                }
            }

            $isAvailable = false;
            $absenceReason = 'Sin asistencia registrada en el turno ' . $schedule->name;

            if ($relevantAttendance) {
                // 🔍 Registrar asistencia seleccionada
                $workerDebug['✅ ASISTENCIA_SELECCIONADA'] = Carbon::parse($relevantAttendance->check_in)->format('H:i:s');
                $workerDebug['🚪 TIENE_SALIDA'] = $relevantAttendance->check_out
                    ? '✅ SÍ - ' . Carbon::parse($relevantAttendance->check_out)->format('H:i:s')
                    : '❌ NO (aún trabajando)';

                if ($relevantAttendance->check_in && !$relevantAttendance->check_out) {
                    $isAvailable = true;
                } else if ($relevantAttendance->check_out) {
                    $absenceReason = 'Ya marcó salida a las ' .
                        Carbon::parse($relevantAttendance->check_out)->format('H:i');
                }
            } else {
                $workerDebug['✅ ASISTENCIA_SELECCIONADA'] = '❌ Ninguna asistencia válida';
                $workerDebug['🚪 TIENE_SALIDA'] = 'N/A';
            }

            if (!$isAvailable) {
                $absentPersonnel[] = [
                    'detail_id' => $detail->id,
                    'user' => $detail->user,
                    'role' => $detail->getRoleNameAttribute(),
                    'position_order' => $detail->position_order,
                    'reason' => $absenceReason,
                ];

                $workerDebug['📊 RESULTADO_FINAL'] = '❌ FALTANTE - ' . $absenceReason;
            } else {
                $presentPersonnel[] = [
                    'detail_id' => $detail->id,
                    'user' => $detail->user,
                    'role' => $detail->getRoleNameAttribute(),
                    'check_in' => Carbon::parse($relevantAttendance->check_in)->format('H:i'),
                ];

                $workerDebug['📊 RESULTADO_FINAL'] = '✅ PRESENTE';
            }

            // 🔍 Agregar trabajador al debug de la programación
            $currentDebug['👥 PERSONAL'][] = $workerDebug;
        }

        $status = empty($absentPersonnel) ? 'ready' : 'not_ready';
        $reason = empty($absentPersonnel)
            ? 'Grupo completo y listo para operar'
            : 'Faltan ' . count($absentPersonnel) . ' integrante(s) por confirmar asistencia';

        // 🔍 Agregar resumen final
        $currentDebug['📊 RESUMEN'] = [
            'status' => $status === 'ready' ? '✅ LISTO PARA OPERAR' : '❌ NO PUEDE INICIAR',
            'presentes' => count($presentPersonnel),
            'faltantes' => count($absentPersonnel),
        ];

        // 🔍 Agregar esta programación al array global de debug
        $debugData[] = $currentDebug;

        return [
            'scheduling' => $scheduling,
            'status' => $status,
            'reason' => $reason,
            'absent_personnel' => $absentPersonnel,
            'present_personnel' => $presentPersonnel,
            'absent_count' => count($absentPersonnel),
            'date' => $date,
        ];
    }

    private function isTimeWithinShift($checkInTime, $shiftStart, $shiftEnd)
    {
        $checkIn = Carbon::createFromFormat('H:i', $checkInTime);
        $start = Carbon::createFromFormat('H:i', $shiftStart);
        $end = Carbon::createFromFormat('H:i', $shiftEnd);

        $toleranceStart = $start->copy()->subHours(2);

        $result = $checkIn->between($toleranceStart, $end);

        return $result;
    }

    public function changeStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|integer|in:0,1,2,3'
            ]);

            $scheduling = Scheduling::findOrFail($id);
            $newStatus = $request->input('status');
            $oldStatus = $scheduling->status;

            // Actualizar estado
            $scheduling->status = $newStatus;
            $scheduling->save();

            // Mensajes según el estado
            $messages = [
                0 => 'Programación marcada como pendiente',
                1 => 'Programación iniciada correctamente',
                2 => 'Programación completada exitosamente',
                3 => 'Programación cancelada',
            ];
            return response()->json([
                'success' => true,
                'message' => $messages[$newStatus] ?? 'Estado actualizado',
                'data' => [
                    'scheduling_id' => $scheduling->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ], 500);
        }

        // return redirect()->back()->with('success', $messages[$newStatus] ?? 'Estado actualizado');
    }

    /**
     * Obtener solo las programaciones pendientes (AJAX)
     */
    public function getPrgPendientes(Request $request)
    {
        $selectedDate = $request->input('date', now()->format('Y-m-d'));
        $selectedScheduleId = $request->input('schedule_id');

        $schedulings = $this->getSchedulings($selectedDate, $selectedScheduleId);
        $pendingZones = $this->processPendingZones($schedulings, $selectedDate);

        return view('Dashboard._ProgramacionesPendientes', compact('pendingZones'))->render();
    }

    /**
     * Obtener solo las programaciones activas/completadas (AJAX)
     */
    public function getPrgOtras(Request $request)
    {
        $selectedDate = $request->input('date', now()->format('Y-m-d'));
        $selectedScheduleId = $request->input('schedule_id');

        $schedulings = $this->getSchedulings($selectedDate, $selectedScheduleId);
        $groupedBySchedule = $this->processActiveCompletedZones($schedulings, $selectedDate);

        return view('Dashboard._ProgramacionesOtras', compact('groupedBySchedule'))->render();
    }

    /**
     * Obtener solo las estadísticas (AJAX)
     */
    public function getStats(Request $request)
    {
        $selectedDate = $request->input('date', now()->format('Y-m-d'));
        $selectedScheduleId = $request->input('schedule_id');

        $schedulings = $this->getSchedulings($selectedDate, $selectedScheduleId);
        $pendingZones = $this->processPendingZones($schedulings, $selectedDate);
        $groupedBySchedule = $this->processActiveCompletedZones($schedulings, $selectedDate);

        $stats = $this->calculateStats($pendingZones, $groupedBySchedule);

        return response()->json($stats);
    }

    /**
     *  Helper: Obtener programaciones
     */
    private function getSchedulings($selectedDate, $selectedScheduleId = null)
    {
        $query = Scheduling::with([
            'zone',
            'vehicle',
            'schedule',
            'group',
            'details.user',
            'details.userType'
        ])->where(function ($q) use ($selectedDate) {
            $q->whereDate('date', $selectedDate)
                ->orWhere(function ($q2) use ($selectedDate) {
                    $q2->whereNotNull('start_date')
                        ->whereNotNull('end_date')
                        ->whereDate('start_date', '<=', $selectedDate)
                        ->whereDate('end_date', '>=', $selectedDate);
                });
        });

        if ($selectedScheduleId) {
            $query->where('schedule_id', $selectedScheduleId);
        }

        return $query->get();
    }

    private function calculateStats($pendingZones, $groupedBySchedule)
    {
        return [
            'total_zones' => $pendingZones->count() + $groupedBySchedule->flatten(1)->count(),
            'ready_zones' => $pendingZones->where('status', 'ready')->count(),
            'not_ready_zones' => $pendingZones->where('status', 'not_ready')->count(),
            'absent_personnel' => $pendingZones->sum('absent_count'),
            'in_process' => $groupedBySchedule->flatten(1)->filter(fn($z) => $z['scheduling']->status == 1)->count(),
        ];
    }

    /**
     * Helper: Procesar zonas pendientes
     */
    private function processPendingZones($schedulings, $date)
    {
        $pendingZones = collect();
        $debugData = [];

        foreach ($schedulings as $scheduling) {
            if ($scheduling->status == 0) {
                $analysis = $this->analyzeScheduling($scheduling, $date, $debugData);
                $pendingZones->push($analysis);
            }
        }

        return $pendingZones;
    }
    /**
     * 🔧 Helper: Procesar zonas activas/completadas
     */
    private function processActiveCompletedZones($schedulings, $date)
    {
        $activeCompletedZones = collect();
        $debugData = [];

        foreach ($schedulings as $scheduling) {
            if ($scheduling->status != 0) {
                $analysis = $this->analyzeScheduling($scheduling, $date, $debugData);
                $activeCompletedZones->push($analysis);
            }
        }

        // Agrupar por turno
        $groupedBySchedule = $activeCompletedZones->groupBy(function ($zoneData) {
            return $zoneData['scheduling']->schedule->id;
        });

        // Ordenar dentro de cada turno: EN PROCESO primero, luego COMPLETADAS/CANCELADAS
        $groupedBySchedule = $groupedBySchedule->map(function ($zones) {
            return $zones->sortBy(function ($zoneData) {
                return $zoneData['scheduling']->status;
            })->values();
        });

        return $groupedBySchedule;
    }

    /**
     * 🔄 Editar programación con personal disponible para reemplazo
     */
    public function editWithReplacements(Request $request, Scheduling $scheduling)
    {
        $selectedDate = $request->input('date', now()->format('Y-m-d'));

        $schedules = Schedule::orderBy('name')
            ->where('id', '!=', $scheduling->schedule_id)
            ->get();

        $vehicles = Vehicle::orderBy('name')
            ->where('id', '!=', $scheduling->vehicle_id)
            ->get();

        $assigned = \App\Models\SchedulingDetail::with('user')
            ->where('scheduling_id', $scheduling->id)
            ->orderBy('position_order')
            ->get();

        $assignedUserIds = $assigned->pluck('user_id')->toArray();

        $allEmployees = $this->getAvailableEmployeesForReplacement(
            $selectedDate,
            $scheduling->schedule_id,
            $assignedUserIds
        );

        return view('schedulings._modal_edit', compact(
            'scheduling',
            'schedules',
            'vehicles',
            'allEmployees',
            'assigned'
        ));
    }

    /**
     * 🔍 Obtener empleados disponibles para reemplazo
     * 
     * @param string $date - Fecha de la programación
     * @param int $scheduleId - ID del turno
     * @param array $excludeUserIds - IDs de usuarios a excluir (ya asignados)
     * @return \Illuminate\Support\Collection
     */
    private function getAvailableEmployeesForReplacement($date, $scheduleId, $excludeUserIds = [])
    {
        // Obtener el turno
        $schedule = Schedule::findOrFail($scheduleId);
        $shiftStartTime = Carbon::parse($schedule->time_start)->format('H:i');
        $shiftEndTime = Carbon::parse($schedule->time_end)->format('H:i');

        // 🎯 Obtener todas las asistencias del día que cumplan los criterios
        $validAttendances = Attendace::with('user')
            ->whereDate('date', $date)
            ->whereNotNull('check_in')
            ->whereNull('check_out') // ✅ NO tienen salida (aún están trabajando)
            ->whereNotIn('user_id', $excludeUserIds) // ✅ NO están ya asignados
            ->get()
            ->filter(function ($attendance) use ($shiftStartTime, $shiftEndTime) {
                $checkInTime = Carbon::parse($attendance->check_in)->format('H:i');

                // ✅ Verificar si está dentro del turno (con 2h de tolerancia)
                return $this->isTimeWithinShift($checkInTime, $shiftStartTime, $shiftEndTime);
            });

        // 📋 Extraer usuarios únicos disponibles
        $availableUsers = collect();

        foreach ($validAttendances as $attendance) {
            if ($attendance->user) {
                // Evitar duplicados
                if (!$availableUsers->contains('id', $attendance->user->id)) {
                    $availableUsers->push($attendance->user);
                }
            }
        }

        return $availableUsers->sortBy('firstname')->values();
    }
}
