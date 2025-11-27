<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HistoryController;
use App\Models\Attendace;
use App\Models\Contract;
use App\Models\User;
use App\Traits\HistoryChanges;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AttendaceController extends Controller
{

    use HistoryChanges;

    /**
     * Lista las asistencias (por defecto del día actual).
     * 
     * Filtros disponibles:
     * - date: Fecha específica (default: hoy)
     * - start_date y end_date: Rango de fechas
     * - user_id: Filtrar por usuario
     * - type: ENTRADA o SALIDA
     * - status: PRESENTE, AUSENTE, TARDANZA
     */
    public function index(Request $request)
    {
        $request->validate([
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_date' => 'nullable|date',
        ], [
            'end_date.after_or_equal' => 'La fecha final no puede ser anterior a la fecha inicial.',
        ]);


        try {
            $search = $request->input('search');
            $today = Carbon::today();

            $query = Attendace::with('user:id,firstname,lastname,dni,usertype_id')
                ->orderBy('date', 'desc')
                ->orderBy('check_in', 'desc');

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if ($request->filled('start_date')) {
                if ($request->filled('end_date') && $endDate > $startDate) {
                    $query->dateRange($startDate, $endDate);
                } else {
                    $query->whereDate('date', $startDate);
                }
            } elseif (!$request->hasAny(['start_date', 'end_date'])) {
                $query->today();
            }

            if ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('firstname', 'ILIKE', "%{$search}%")
                        ->orWhere('lastname', 'ILIKE', "%{$search}%")
                        ->orWhere('dni', 'ILIKE', "%{$search}%");
                });
            }

            // Filtro por tipo (ENTRADA/SALIDA)
            if ($request->filled('type')) {
                $query->byType($request->type);
            }

            // Filtro por estado
            if ($request->filled('status')) {
                $query->byStatus($request->status);
            }
            // dd($query->toSql(), $query->getBindings());

            $attendances = $query->paginate(15);
            return view('attendances.index', compact('attendances', 'search', 'today'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar asistencias: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $selectedDate = request('date', Carbon::today()->format('Y-m-d'));
        $presentUserIds = Attendace::whereDate('date', $selectedDate)
            ->where('status', Attendace::STATUS_PRESENTE)
            ->pluck('user_id');

        $usuarios = User::where('status', 'ACTIVO')
            ->whereHas('contracts', function ($query) {
                $query->where('is_active', true)
                    ->whereDate('date_start', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('date_end')
                            ->orWhereDate('date_end', '>=', now());
                    });
            })
            ->whereNotIn('id', $presentUserIds)
            ->orderBy('firstname')
            ->get();

        $attendance = new Attendace(['date' => $selectedDate]);

        return response()
            ->view('attendances._modal_create', compact('attendance', 'usuarios', 'selectedDate'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date_format:Y-m-d',
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'status' => 'required|in:PRESENTE,AUSENTE,TARDANZA',
            'notes' => 'nullable|string|max:255',
        ], [
            'user_id.required' => 'El usuario es obligatorio',
            'user_id.exists' => 'El usuario no existe',
            'date.required' => 'La fecha es obligatoria',
            'check_in.required' => 'La Hora de entrada es obligatoria',
            'check_out.after'   => 'La hora de salida debe ser posterior a la hora de entrada',
            'status.required' => 'El estado es obligatorio',
            'status.in' => 'El estado debe ser PRESENTE, AUSENTE o TARDANZA',
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
        $request->merge([
            'type' => !$request->filled('check_out')
                ? Attendace::TYPE_ENTRADA
                : Attendace::TYPE_SALIDA
        ]);

        DB::beginTransaction();

        try {
            $user = User::find($request->user_id);

            $activeContract = Contract::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereDate('date_start', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('date_end')
                        ->orWhereDate('date_end', '>=', now());
                })
                ->first();

            if (!$user || !$activeContract) {
                DB::rollBack();

                $mensaje = 'Solo el personal EXISITENTE con contrato ACTIVO puede marcar asistencia.';

                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => $mensaje,
                        'errors' => ['user_id' => [$mensaje]]
                    ], 422);
                }
                return back()->withErrors(['user_id' => $mensaje])->withInput();
            }

            $attendanceEliminada = Attendace::onlyTrashed()
                ->where('user_id', $request->user_id)
                ->whereDate('date', $request->date)
                ->first();

            if ($attendanceEliminada) {
                $checkInExistente = Carbon::parse($attendanceEliminada->check_in);
                if ($checkInExistente->format('H:i') === $request->check_in && !$attendanceEliminada->check_out) {

                    $attendanceEliminada->restore();
                    $attendanceEliminada->update($request->all());

                    DB::commit();

                    $mensaje = 'Asistencia restaurada y actualizada exitosamente.';

                    if ($isTurbo) {
                        return response()->json([
                            'success' => true,
                            'message' => $mensaje,
                        ], 200);
                    }

                    return redirect()->route('attendances.index')->with('success', $mensaje);
                }
            }

            // Crear nueva asistencia
            $attendance = Attendace::create($request->all());

            DB::commit();

            $mensaje = 'Asistencia registrada exitosamente como ' . $request->type . '.';

            if ($attendance->date->lt(Carbon::today()) && $attendance->check_out === null) {
                $formattedDate = $attendance->date->format('d/m/Y');
                $mensaje = "¡Asistencia registrada! PERO la marcación del {$formattedDate} no tiene salida registrada.";
            }

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'data' => $attendance->load(['user:firstname,lastname']), //, //->load('user:id,name,dni'),
                    'message' => $mensaje,
                ], 201);
            }
            return redirect()->route('attendances.index')->with("success", $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar asistencia: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al registrar asistencia: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $attendance = Attendace::findOrFail($id);
            $audits = HistoryController::getHistory('ASISTENCIA DE PERSONAL', $id);
            return view('attendances.show', compact('attendance', 'audits'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al mostrar detalle de asistencia: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $attendance = Attendace::with('user')->findOrFail($id);

        $usuarios = User::where('status', 'ACTIVO')
            ->whereHas('contracts', function ($query) {
                $query->where('is_active', true)
                    ->whereDate('date_start', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('date_end')
                            ->orWhereDate('date_end', '>=', now());
                    });
            })
            ->orderBy('firstname')
            ->get();

        return response()
            ->view('attendances._modal_edit', compact('attendance', 'usuarios'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        try {
            $attendance = Attendace::find($id);

            if (!$attendance) {
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Asistencia no encontrada.',
                    ], 404);
                }
                return back()->with('error', 'Asistencia no encontrada.');
            }

            $originalData = $attendance->getOriginal();

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'date' => 'required|date_format:Y-m-d',
                'check_in' => 'required|date_format:H:i',
                'check_out' => 'nullable|date_format:H:i|after:check_in',
                'status' => 'required|in:PRESENTE,AUSENTE,TARDANZA',
                'notes' => 'nullable|string|max:255',
            ], [
                'user_id.required' => 'El usuario es obligatorio',
                'user_id.exists' => 'El usuario no existe',
                'date.required' => 'La fecha es obligatoria',
                'check_in.required' => 'La Hora de entrada es obligatoria',
                'check_out.after'   => 'La hora de salida debe ser posterior a la hora de entrada',
                'status.required' => 'El estado es obligatorio',
                'status.in' => 'El estado debe ser PRESENTE, AUSENTE o TARDANZA',
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

            $request->merge([
                'type' => !$request->filled('check_out')
                    ? Attendace::TYPE_ENTRADA
                    : Attendace::TYPE_SALIDA
            ]);

            $attendance->update($request->all());
            $exceptFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'type'];

            $this->registrarCambios($attendance,  $originalData, $request->input('notes'), $exceptFields);

            $mensaje = 'Asistencia actualizada exitosamente como ' . $attendance->type . '.';

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'data' => $attendance,
                    'message' => $mensaje,
                ], 200);
            }

            return redirect()->route('attendances.index')->with('success', $mensaje);
        } catch (\Exception $e) {
            DB::rollBack();

            $mensaje = 'Error al actualizar asistencia: ' . $e->getMessage();

            if ($isTurbo) {
                return response()->json(['success' => false, 'message' => $mensaje], 500);
            }

            return back()->with('error', $mensaje);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $attendance = Attendace::find($id);

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asistencia no encontrada'
                ], 404);
            }

            $attendance->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'Asistencia eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marca asistencia automática del personal (similar a login).
     * 
     * Reglas de negocio:
     * 1. Verifica DNI y contraseña del usuario
     * 2. Valida que el usuario esté ACTIVO
     * 3. Primera marcación del día: Registra ENTRADA con check_in y status PRESENTE
     * 4. Segunda marcación del día: Actualiza a SALIDA con check_out (mantiene status)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function markAttendance(Request $request)
    {
        try {
            // Validar DNI y contraseña
            $validator = Validator::make($request->all(), [
                'username' => 'required_without:dni|string',
                'password' => 'required|string',
                'notes' => 'nullable|string|max:255',
            ], [
                'username.required_without' => '- El nombre de usuario o DNI es obligatorio',
                'password.required' => '- La contraseña es obligatoria',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar usuario por DNI o username
            $user = User::where('username', $request->username)->orWhere('dni', $request->username)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'errors' => ['dni' => ['El DNI o Nombre de usuario no está registrado en el sistema']]
                ], 404);
            }

            $activeContract = Contract::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereDate('date_start', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('date_end')
                        ->orWhereDate('date_end', '>=', now());
                })
                ->first();

            if (!$activeContract) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene contrato activo vigente',
                    'errors' => [
                        'contract' => ['El usuario no tiene un contrato activo o ha expirado. Contacte a RRHH.']
                    ]
                ], 403);
            }

            // Verificar contraseña
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contraseña incorrecta',
                    'errors' => ['password' => ['La contraseña es incorrecta']]
                ], 401);
            }

            // Verificar que el usuario esté ACTIVO
            if ($user->status !== 'ACTIVO') {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario inactivo. No puede marcar asistencia',
                    'errors' => [
                        'status' => ['Su cuenta está INACTIVA. Contacte al administrador.']
                    ]
                ], 403);
            }

            $today = Carbon::now()->format('d-m-Y');
            $now = Carbon::now()->format('H:i');

            // Buscar la última asistencia del día (ordenada por ID desc)
            $lastAttendance = Attendace::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->orderBy('updated_at', 'desc')
                ->first();

            // CASO 1: La última asistencia es ENTRADA sin SALIDA → Registrar SALIDA
            if ($lastAttendance && $lastAttendance->check_out === null) {
                $lastAttendance->update([
                    'check_out' => $now,
                    'type' => Attendace::TYPE_SALIDA,
                    'notes' => $request->notes ?? $lastAttendance->notes,
                ]);

                $lastAttendance->formatted_date = $lastAttendance->date->format('d-m-Y');
                return response()->json([
                    'success' => true,
                    'data' => $lastAttendance->load(['user:id,username,firstname,lastname']),
                    'message' => "SALIDA registrada exitosamente",
                ], 200);
            }

            // CASO 2: No tiene asistencia HOY o la última ya tiene SALIDA → Nueva ENTRADA
            if (!$lastAttendance || $lastAttendance->check_out !== null) {

                $attendance = Attendace::create([
                    'user_id' => $user->id,
                    'date' => $today,
                    'check_in' => $now,
                    'check_out' => null,
                    'type' => Attendace::TYPE_ENTRADA,
                    'status' => Attendace::STATUS_PRESENTE,
                    'notes' => $request->notes,
                ]);

                $attendance->formatted_date = $attendance->date->format('d-m-Y');

                return response()->json([
                    'success' => true,
                    'data' => $attendance->load(['user:id,username,firstname,lastname']),
                    'message' => "ENTRADA registrada exitosamente",
                ], 201);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo procesar la marcación',
                'data' => $lastAttendance->load(['user:id,username,firstname,lastname'])
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar marcación de asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
