<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Attendace;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AttendaceController extends Controller
{
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
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $sortBy = $request->input('sortBy', 'date');
            $sortOrder = $request->input('sortOrder', 'desc');

            $query = Attendace::with('user:id,firstname,lastname,dni,usertype_id');

            if (!$request->has('start_date') && !$request->has('end_date') && !$request->has('date')) {
                $query->today();
            }

            if ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('firstname', 'ILIKE', "%{$search}%")
                        ->orWhere('lastname', 'ILIKE', "%{$search}%")
                        ->orWhere('username', 'ILIKE', "%{$search}%")
                        ->orWhere('dni', 'ILIKE', "%{$search}%");
                });
            }

            // Filtro por rango de fechas
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->dateRange($request->start_date, $request->end_date);
            }

            // Filtro por fecha específica
            if ($request->has('date')) {
                $query->whereDate('date', $request->date);
            }

            // Filtro por usuario
            if ($request->has('user_id')) {
                $query->byUser($request->user_id);
            }

            // Filtro por tipo (ENTRADA/SALIDA)
            if ($request->has('type')) {
                $query->byType($request->type);
            }

            // Filtro por estado
            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            // foreach ($request->all() as $key => $value) {
            //     if (
            //         Schema::hasColumn('attendances', $key) &&
            //         !in_array($key, ['search', 'sortBy', 'sortOrder', 'per_page', 'all', 'start_date', 'end_date', 'user_id', 'status', 'date'])
            //     ) {
            //         $query->where($key, $value);
            //     }
            // }

            // Ordenamiento
            if (Schema::hasColumn('attendances', $sortBy)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // 📄 Paginación o todos los registros
            $all = $request->input('all', false);
            $pagination = [];
            if ($all) {
                $attendances = $query->get();
            } else {
                $attendances = $query->paginate($perPage)->appends([
                    'search' => $search,
                    'perPage' => $perPage
                ]);
                $pagination = [
                    'current_page' => $attendances->currentPage(),
                    'last_page' => $attendances->lastPage(),
                    'per_page' => $attendances->perPage(),
                    'total' => $attendances->total()
                ];
                $attendances = $attendances->items();
            }

            return response()->json([
                'success' => true,
                'data' => $attendances,
                'message' => 'Asistencias obtenidas exitosamente',
                'pagination' => $pagination
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las asistencias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'check_in' => 'nullable|date_format:H:i:s',
                'check_out' => 'nullable|date_format:H:i:s',
                'type' => 'required|in:ENTRADA,SALIDA',
                'status' => 'required|in:PRESENTE,AUSENTE,TARDANZA',
                'notes' => 'nullable|string|max:255',
            ], [
                'user_id.required' => 'El ID del usuario es obligatorio',
                'user_id.exists' => 'El usuario no existe',
                'date.required' => 'La fecha es obligatoria',
                'date.date' => 'La fecha no tiene un formato válido',
                'type.required' => 'El tipo es obligatorio',
                'type.in' => 'El tipo debe ser ENTRADA o SALIDA',
                'status.required' => 'El estado es obligatorio',
                'status.in' => 'El estado debe ser PRESENTE, AUSENTE o TARDANZA',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar si ya existe asistencia para ese usuario en esa fecha
            $attendanceExistente = Attendace::withTrashed()
                ->where('user_id', $request->user_id)
                ->whereDate('date', $request->date)
                ->first();

            if ($attendanceExistente) {
                if ($attendanceExistente->trashed()) {
                    $attendanceExistente->restore();
                    $attendanceExistente->update($request->all());

                    return response()->json([
                        'success' => true,
                        'data' => $attendanceExistente,
                        'message' => 'Asistencia restaurada y actualizada exitosamente'
                    ], 200);
                } else {
                    // Ya existe asistencia activa para ese día
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe una asistencia registrada para este usuario en esta fecha',
                        'errors' => [
                            'date' => ['El usuario tiene asistencia sin salida registrada para esta fecha']
                        ]
                    ], 422);
                }
            }

            // Crear nueva asistencia
            $attendance = Attendace::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $attendance, //, //->load('user:id,name,dni'),
                'message' => 'Asistencia registrada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $attendance = Attendace::with('user:id,name,dni')->find($id);

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asistencia no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $attendance,
                'message' => 'Asistencia obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la asistencia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $attendance = Attendace::find($id);

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asistencia no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|required|exists:users,id',
                'date' => 'sometimes|required|date',
                'check_in' => 'nullable|date_format:H:i:s',
                'check_out' => 'nullable|date_format:H:i:s',
                'type' => 'sometimes|required|in:ENTRADA,SALIDA',
                'status' => 'sometimes|required|in:PRESENTE,AUSENTE,TARDANZA',
                'notes' => 'nullable|string|max:255',
            ], [
                'user_id.exists' => 'El usuario no existe',
                'date.date' => 'La fecha no tiene un formato válido',
                'type.in' => 'El tipo debe ser ENTRADA o SALIDA',
                'status.in' => 'El estado debe ser PRESENTE, AUSENTE o TARDANZA',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            // Verificar duplicado si se cambia user_id o date
            if ($request->has('user_id') || $request->has('date')) {
                $userId = $request->user_id ?? $attendance->user_id;
                $date = $request->date ?? $attendance->date;

                $duplicado = Attendace::where('user_id', $userId)
                    ->whereDate('date', $date)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($duplicado) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe una asistencia para este usuario en esta fecha',
                        'errors' => ['date' => ['Conflicto con asistencia existente']]
                    ], 422);
                }
            }

            $attendance->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $attendance, //->load('user:id,name,dni'),
                'message' => 'Asistencia actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la asistencia',
                'error' => $e->getMessage()
            ], 500);
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
    public function markAttendance(Request $request): JsonResponse
    {
        try {
            // Validar DNI y contraseña
            $validator = Validator::make($request->all(), [
                'username' => 'required_without:dni|string',
                // 'dni' => 'required_without:username|string|size:8',
                'password' => 'required|string',
                'notes' => 'nullable|string|max:255',
            ], [
                'username.required_without' => 'El username o DNI es obligatorio',
                // 'dni.required_without' => 'El DNI o username es obligatorio',
                // 'dni.size' => 'El DNI debe tener 8 dígitos',
                'password.required' => 'La contraseña es obligatoria',
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

            $today = Carbon::now()->toDateString();
            $now = Carbon::now()->toTimeString();

            // Buscar la última asistencia del día (ordenada por ID desc)
            $lastAttendance = Attendace::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->orderBy('id', 'desc')
                ->first();

            // CASO 1: No tiene asistencia HOY o la última ya tiene SALIDA → Nueva ENTRADA
            if (!$lastAttendance || $lastAttendance->check_out !== null) {
                $attendance = Attendace::create([
                    'user_id' => $user->id,
                    'date' => $today,
                    'check_in' => $now,
                    'check_out' => null,
                    'type' => 'ENTRADA',
                    'status' => 'PRESENTE',
                    'notes' => $request->notes,
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $attendance,
                    'message' => "ENTRADA registrada exitosamente",
                ], 201);
            }

            // CASO 2: La última asistencia es ENTRADA sin SALIDA → Registrar SALIDA
            if ($lastAttendance->check_out === null) {
                $lastAttendance->update([
                    'check_out' => $now,
                    'type' => 'SALIDA',
                    'notes' => $request->notes ?? $lastAttendance->notes,
                    // NO SE ACTUALIZA EL STATUS
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $lastAttendance,
                    'message' => "SALIDA registrada exitosamente",
                ], 200);
            }

            // Ya marcó ENTRADA y SALIDA Evaluar cantidades
            return response()->json([
                'success' => false,
                'message' => 'No se pudo procesar la marcación',
                'data' => $lastAttendance
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
