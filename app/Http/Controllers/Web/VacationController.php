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
                      ->orWhere('reason', 'LIKE', "%{$search}%")
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
            $users = User::select('id', 'firstname', 'lastname', 'dni')->get();
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
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_days' => 'required|integer',
            'reason' => 'nullable|string',
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
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $max_days = $request->max_days;
            $daysProgrammed = $startDate->diffInDays($endDate) + 1;

            if ($daysProgrammed > 30) {
                $resp = [
                    'success' => false,
                    'message' => 'El período de vacaciones no puede superar los 30 días.',
                    'days_calculated' => $daysProgrammed,
                    'max_allowed' => 30,
                ];
                if ($isTurbo) return response()->json($resp, 422);
                return back()->with('error', $resp['message'])->withInput();
            }

            $activeContract = Contract::where('user_id', $request->user_id)
                ->where('is_active', true)
                ->whereIn('type', ['nombrado', 'permanente'])
                ->first();

            if (! $activeContract) {
                $resp = ['success' => false, 'message' => 'El usuario no tiene un contrato activo de tipo nombrado o permanente. Solo estos tipos de contrato pueden solicitar vacaciones.'];
                if ($isTurbo) return response()->json($resp, 422);
                return back()->with('error', $resp['message'])->withInput();
            }

            $overlappingVacations = Vacation::where('user_id', $request->user_id)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                        ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                        ->orWhere(function ($q) use ($request) {
                            $q->where('start_date', '<=', $request->start_date)
                              ->where('end_date', '>=', $request->end_date);
                        });
                })
                ->whereIn('status', ['pendiente', 'aprobada'])
                ->exists();

            if ($overlappingVacations) {
                $resp = ['success' => false, 'message' => 'Las fechas de vacaciones se solapan con otra solicitud existente (pendiente o aprobada).'];
                if ($isTurbo) return response()->json($resp, 422);
                return back()->with('error', $resp['message'])->withInput();
            }

            $data = $request->only(['user_id','year','start_date','end_date','reason','status']);
            $data['days_programmed'] = $daysProgrammed;
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
            $users = User::select('id','firstname','lastname','dni')->get();
            return response()->view('vacations._modal_edit', compact('vacation','users'))
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
            'user_id' => 'sometimes|required|exists:users,id',
            'year' => 'sometimes|required|integer|min:1900',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'status' => 'sometimes|in:pendiente,aprobada,rechazada',
        ]);

        if ($validator->fails()) {
            if ($isTurbo) return response()->json(['success' => false, 'message' => 'Errores de validación.', 'errors' => $validator->errors()], 422);
            return back()->withErrors($validator)->withInput();
        }

        try {
            $vacation = Vacation::findOrFail($id);

            $userId = $request->has('user_id') ? $request->user_id : $vacation->user_id;

            if ($request->has('user_id')) {
                $activeContract = Contract::where('user_id', $userId)
                    ->where('is_active', true)
                    ->whereIn('type', ['nombrado', 'permanente'])
                    ->first();
                if (! $activeContract) {
                    $resp = ['success' => false, 'message' => 'El usuario no tiene un contrato activo de tipo nombrado o permanente.'];
                    if ($isTurbo) return response()->json($resp, 422);
                    return back()->with('error', $resp['message'])->withInput();
                }
            }

            if ($request->has('start_date') || $request->has('end_date')) {
                $startDate = Carbon::parse($request->has('start_date') ? $request->start_date : $vacation->start_date);
                $endDate = Carbon::parse($request->has('end_date') ? $request->end_date : $vacation->end_date);
                $daysProgrammed = $startDate->diffInDays($endDate) + 1;
                if ($daysProgrammed > 30) {
                    $resp = ['success' => false, 'message' => 'El período de vacaciones no puede superar los 30 días.', 'days_calculated' => $daysProgrammed, 'max_allowed' => 30];
                    if ($isTurbo) return response()->json($resp, 422);
                    return back()->with('error', $resp['message'])->withInput();
                }

                $overlappingVacations = Vacation::where('user_id', $userId)
                    ->where('id', '!=', $id)
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function ($q) use ($startDate, $endDate) {
                                $q->where('start_date', '<=', $startDate)
                                  ->where('end_date', '>=', $endDate);
                            });
                    })->whereIn('status', ['pendiente', 'aprobada'])->exists();

                if ($overlappingVacations) {
                    $resp = ['success' => false, 'message' => 'Las fechas de vacaciones se solapan con otra solicitud existente.'];
                    if ($isTurbo) return response()->json($resp, 422);
                    return back()->with('error', $resp['message'])->withInput();
                }
            }

            $data = $request->only(['user_id','year','start_date','end_date','reason','status']);
            if ($request->has('start_date') || $request->has('end_date')) {
                $data['days_programmed'] = $daysProgrammed;
                $data['days_pending'] = 30 - $daysProgrammed;
            }

            $vacation->update($data);

            if ($isTurbo) return response()->json(['success' => true, 'data' => $vacation->fresh(), 'message' => 'Vacación actualizada exitosamente.'], 200);
            return redirect()->route('vacations.index')->with('success', 'Vacación actualizada exitosamente.');
        } catch (\Exception $e) {
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
