<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendace;
use App\Models\Schedule;
use App\Models\Scheduling;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * 🎯 Dashboard principal con filtro de fecha y turno
     */
    public function index(Request $request)
    {
        // 📅 Fecha: por defecto HOY, no permite fechas anteriores
        $selectedDate = $request->input('date', now()->format('Y-m-d'));

        // Validar que no sea fecha pasada
        if (Carbon::parse($selectedDate)->isBefore(now()->startOfDay())) {
            $selectedDate = now()->format('Y-m-d');
        }

        // 🕐 Turno: por defecto TODOS
        $selectedScheduleId = $request->input('schedule_id');

        // 🔍 Obtener programaciones del día (o rango que incluya el día)
        $query = Scheduling::with([
            'zone',
            'vehicle',
            'schedule',
            'group',
            'details.user',
            'details.userType'
        ])
            ->where(function ($q) use ($selectedDate) {
                // Programaciones de un solo día
                $q->whereDate('date', $selectedDate)
                    // O programaciones de rango que incluyan esta fecha
                    ->orWhere(function ($q2) use ($selectedDate) {
                        $q2->whereNotNull('start_date')
                            ->whereNotNull('end_date')
                            ->whereDate('start_date', '<=', $selectedDate)
                            ->whereDate('end_date', '>=', $selectedDate);
                    });
            });

        // Filtrar por turno si se seleccionó uno
        if ($selectedScheduleId) {
            $query->where('schedule_id', $selectedScheduleId);
        }

        $schedulings = $query->get();

        $debugData = [];
        $zonesData = collect();

        // 🔍 Usar foreach en lugar de map
        foreach ($schedulings as $scheduling) {
            $analysis = $this->analyzeScheduling($scheduling, $selectedDate, $debugData);
            $zonesData->push($analysis);
        }
        // 🔍 MOSTRAR TODO AL FINAL
        // dd($debugData);

        // 📊 Estadísticas
        $stats = [
            'total_zones' => $zonesData->count(),
            'ready_zones' => $zonesData->where('status', 'ready')->count(),
            'not_ready_zones' => $zonesData->where('status', 'not_ready')->count(),
            'absent_personnel' => $zonesData->sum('absent_count'),
        ];

        // 📋 Obtener turnos para el filtro
        $schedules = Schedule::all();

        return view('welcome', compact(
            'zonesData',
            'stats',
            'selectedDate',
            'selectedScheduleId',
            'schedules'
        ));
    }

    /**
     * 🔍 Analizar una programación y verificar disponibilidad de personal
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
}
