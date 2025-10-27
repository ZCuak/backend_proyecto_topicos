<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vacation;
use App\Models\User;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VacationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);

            $query = Vacation::with(['user']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('year', 'LIKE', "%{$search}%")
                        ->orWhere('status', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('firstname', 'LIKE', "%{$search}%")
                                ->orWhere('lastname', 'LIKE', "%{$search}%");
                        });
                });
            }

            $vacations = $query->paginate($perPage);

            return view('vacations.index', compact('vacations', 'search'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar vacaciones: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            // Solo traer usuarios que tengan un contrato activo de tipo nombrado o permanente
            $userIds = Contract::where('is_active', true)
                ->whereIn('type', ['nombrado', 'permanente'])
                ->pluck('user_id')
                ->unique()
                ->toArray();

            $users = User::select('id', 'firstname', 'lastname', 'dni')
                ->whereIn('id', $userIds)
                ->get();

            // Añadir el campo max_days desde el contrato activo (si existe)
            $users = $users->map(function ($user) {
                $activeContract = Contract::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->whereIn('type', ['nombrado', 'permanente'])
                    ->orderBy('id', 'desc')
                    ->first();
                $user->max_days = $activeContract ? (int) $activeContract->vacation_days_per_year : null;
                return $user;
            });
            $vacation = new Vacation();
            return response()->view('vacations._modal_create', compact('users', 'vacation'))
                ->header('Turbo-Frame', 'modal-frame');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al abrir formulario: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer|min:1900',
            'start_date' => 'required|date',
            'days_requested' => 'required|integer|min:1',
            'max_days' => 'required|integer|min:1',
            'status' => 'sometimes|in:pendiente,aprobada,rechazada,cancelada,completada',
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
            $startDate = Carbon::parse($request->start_date);
            $daysRequested = (int) $request->days_requested;

            $daysProgrammed = $daysRequested;

            // Obtener contrato activo y usar su vacation_days_per_year como autoridad
            $activeContract = Contract::where('user_id', $request->user_id)
                ->where('is_active', true)
                ->whereIn('type', ['nombrado', 'permanente'])
                ->first();

            if (! $activeContract) {
                $resp = ['success' => false, 'message' => 'El usuario no tiene un contrato activo de tipo nombrado o permanente. Solo estos tipos de contrato pueden solicitar vacaciones.'];
                DB::rollBack();
                if ($isTurbo) return response()->json($resp, 422);
                return back()->with('error', $resp['message'])->withInput();
            }

            $max_days = (int) ($activeContract->vacation_days_per_year ?? 0);

            // Validar que no exceda los días del contrato
            if ($daysProgrammed > $max_days) {
                $resp = [
                    'success' => false,
                    'message' => 'Los días solicitados exceden los días máximos disponibles para este empleado.',
                    'days_requested' => $daysProgrammed,
                    'max_allowed' => $max_days,
                ];
                DB::rollBack();
                if ($isTurbo) return response()->json($resp, 422);
                return back()->with('error', $resp['message'])->withInput();
            }

            $endDate = $startDate->copy()->addDays($daysProgrammed - 1)->startOfDay();

            $overlappingVacations = Vacation::where('user_id', $request->user_id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate->format('Y-m-d'))
                                ->where('end_date', '>=', $endDate->format('Y-m-d'));
                        });
                })
                ->whereIn('status', ['pendiente', 'aprobada'])
                ->exists();

            if ($overlappingVacations) {
                $resp = ['success' => false, 'message' => 'Las fechas de vacaciones se solapan con otra solicitud existente (pendiente o aprobada).'];
                DB::rollBack();
                if ($isTurbo) return response()->json($resp, 422);
                return back()->with('error', $resp['message'])->withInput();
            }

            $data = $request->only(['user_id', 'year', 'start_date', 'status']);
            $data['end_date'] = $endDate->format('Y-m-d');
            $data['days_programmed'] = $daysProgrammed;
            $data['max_days'] = $max_days;
            $data['days_pending'] = $max_days - $daysProgrammed;
            if (! isset($data['status'])) $data['status'] = 'pendiente';

            $vacation = Vacation::create($data);
            DB::commit();

            if ($isTurbo) {
                return response()->json(['success' => true, 'data' => $vacation, 'message' => 'Solicitud creada exitosamente.'], 201);
            }

            return redirect()->route('vacations.index')->with('success', 'Solicitud de vacación creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json(['success' => false, 'message' => 'Error al crear vacación: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al crear vacación: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $vacation = Vacation::with('user')->find($id);
            if (! $vacation) return back()->with('error', 'Vacación no encontrada.');
            return view('vacations.show', compact('vacation'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al obtener vacación: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            // Solo traer usuarios que tengan un contrato activo de tipo nombrado o permanente
            $userIds = Contract::where('is_active', true)
                ->whereIn('type', ['nombrado', 'permanente'])
                ->pluck('user_id')
                ->unique()
                ->toArray();

            $users = User::select('id', 'firstname', 'lastname', 'dni')
                ->whereIn('id', $userIds)
                ->get();

            // Añadir max_days a cada usuario para mostrar en el modal
            $users = $users->map(function ($user) {
                $activeContract = Contract::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->whereIn('type', ['nombrado', 'permanente'])
                    ->orderBy('id', 'desc')
                    ->first();
                $user->max_days = $activeContract ? (int) $activeContract->vacation_days_per_year : null;
                return $user;
            });
            return response()->view('vacations._modal_edit', compact('vacation', 'users'))
                ->header('Turbo-Frame', 'modal-frame');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al abrir edición: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer|min:1900',
            'start_date' => 'required|date',
            'days_requested' => 'required|integer|min:1',
            'status' => 'sometimes|in:pendiente,aprobada,rechazada',
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
            $vacation = Vacation::findOrFail($id);

            $startDate = Carbon::parse($request->start_date);
            $daysRequested = (int) $request->days_requested;

            $daysProgrammed = $daysRequested;

            // Obtener contrato activo y usar su vacation_days_per_year como autoridad
            $activeContract = Contract::where('user_id', $request->user_id)
                ->where('is_active', true)
                ->whereIn('type', ['nombrado', 'permanente'])
                ->first();

            if (! $activeContract) {
                $resp = ['success' => false, 'message' => 'El usuario no tiene un contrato activo de tipo nombrado o permanente. Solo estos tipos de contrato pueden solicitar vacaciones.'];
                if ($isTurbo) {
                    DB::rollBack();
                    return response()->json($resp, 422);
                }
                DB::rollBack();
                return back()->with('error', $resp['message'])->withInput();
            }

            $max_days = (int) ($activeContract->vacation_days_per_year ?? 0);

            // Validar que no exceda los días del contrato
            if ($daysProgrammed > $max_days) {
                $resp = [
                    'success' => false,
                    'message' => 'Los días solicitados exceden los días máximos disponibles para este empleado.',
                    'days_requested' => $daysProgrammed,
                    'max_allowed' => $max_days,
                ];
                if ($isTurbo) {
                    DB::rollBack();
                    return response()->json($resp, 422);
                }
                DB::rollBack();
                return back()->with('error', $resp['message'])->withInput();
            }

            $endDate = $startDate->copy()->addDays($daysProgrammed - 1)->startOfDay();

            $overlappingVacations = Vacation::where('user_id', $request->user_id)
                ->where('id', '!=', $id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate->format('Y-m-d'))
                                ->where('end_date', '>=', $endDate->format('Y-m-d'));
                        });
                })
                ->whereIn('status', ['pendiente', 'aprobada'])
                ->exists();

            if ($overlappingVacations) {
                $resp = ['success' => false, 'message' => 'Las fechas de vacaciones se solapan con otra solicitud existente (pendiente o aprobada).'];
                if ($isTurbo) {
                    DB::rollBack();
                    return response()->json($resp, 422);
                }
                DB::rollBack();
                return back()->with('error', $resp['message'])->withInput();
            }

            $data = $request->only(['user_id', 'year', 'start_date', 'status']);
            $data['end_date'] = $endDate->format('Y-m-d');
            $data['days_programmed'] = $daysProgrammed;
            $data['days_pending'] = $max_days - $daysProgrammed;
            if (! isset($data['status'])) $data['status'] = 'pendiente';

            $vacation->update($data);
            DB::commit();

            if ($isTurbo) {
                return response()->json(['success' => true, 'data' => $vacation->fresh(), 'message' => 'Vacación actualizada exitosamente.'], 200);
            }

            return redirect()->route('vacations.index')->with('success', 'Vacación actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) return response()->json(['success' => false, 'message' => 'Error al actualizar vacación: ' . $e->getMessage()], 500);
            return back()->with('error', 'Error al actualizar vacación: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $isTurbo = request()->header('Turbo-Frame') || request()->expectsJson();
        try {
            $vacation = Vacation::findOrFail($id);
            $vacation->delete();
            if ($isTurbo) return response()->json(['success' => true, 'message' => 'Vacación eliminada exitosamente.'], 200);
            return redirect()->route('vacations.index')->with('success', 'Vacación eliminada exitosamente.');
        } catch (\Exception $e) {
            if ($isTurbo) return response()->json(['success' => false, 'message' => 'Error al eliminar vacación: ' . $e->getMessage()], 500);
            return back()->with('error', 'Error al eliminar vacación: ' . $e->getMessage());
        }
    }
}
