<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\User;
use App\Models\Department;
use App\Models\UserType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try {
            $contracts = Contract::with('user')->orderBy('id', 'desc')->paginate(15);
            return view('contracts.index', compact('contracts'));
        } catch (\Exception $e) {
            // Manejo de errores, por ejemplo, registrar el error y mostrar una vista de error
            Log::error('Error loading contracts index: ' . $e->getMessage());
            return back()->with('error', 'Error al listar contratos: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        try {
            $contractTypes = ['nombrado', 'permanente', 'eventual'];
            $users = User::select('id', 'firstname', 'lastname', 'dni')->get();
            $departments = Department::select('id', 'name')->get();
            $positions = UserType::select('id', 'name')->get();
            $contract = new Contract();

            return response()->view('contracts._modal_create', compact('users', 'contract', 'contractTypes', 'departments', 'positions'))
                ->header('Turbo-Frame', 'modal-frame');
        } catch (\Exception $e) {
            // Manejo de errores, por ejemplo, registrar el error y mostrar una vista de error
            Log::error('Error loading contracts create form: ' . $e->getMessage());
            return view('errors.general', ['message' => 'No se pudo cargar el formulario de creación de contratos.']);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();


        $validator = Validator::make($request->all(), [
            'type' => 'required|in:nombrado,permanente,eventual',
            'date_start' => 'required|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'vacation_days_per_year' => 'required|integer',
            'salary' => 'required|numeric',
            'position_id' => 'required|exists:usertypes,id',
            'department_id' => 'required|exists:departments,id',
            'probation_period_months' => 'required|integer',
            'termination_reason' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            if ($isTurbo) {
                // Return JSON so the global JS shows a SweetAlert with field errors
                return response()->json([
                    'success' => false,
                    'message' => 'Hay errores en los datos enviados.',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {


            // Validación 1: Verificar si el usuario ya tiene un contrato activo
            $activeContract = Contract::where('user_id', $request->user_id)
                ->where('is_active', true)
                ->first();

            if ($activeContract) {
                $resp = [
                    'success' => false,
                    'message' => 'El usuario ya tiene un contrato activo. No se puede agregar un nuevo contrato.',
                    'active_contract_id' => $activeContract->id
                ];
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => $resp['message'],
                        'errors' => ['general' => [$resp['message']]],
                        'active_contract_id' => $activeContract->id,
                    ], 422);
                }
                return back()->with('error', $resp['message'])->withInput();
            }


            // Validación 2: Si es nombrado o permanente, no debe tener fecha de fin
            if (in_array($request->type, ['nombrado', 'permanente'])) {
                if ($request->date_end) {
                    $resp = [
                        'success' => false,
                        'message' => 'Los contratos de tipo nombrado o permanente no deben tener fecha de fin.'
                    ];
                    if ($isTurbo) {
                        return response()->json([
                            'success' => false,
                            'message' => $resp['message'],
                            'errors' => ['general' => [$resp['message']]],
                        ], 422);
                    }
                    return back()->with('error', $resp['message'])->withInput();
                }
                // Asegurarse que date_end sea null
                $request->merge(['date_end' => null]);
            }

            // Validación 3: Para contratos eventuales, verificar que hayan pasado mínimo 2 meses
            if ($request->type === 'eventual') {
                $lastEventualContract = Contract::where('user_id', $request->user_id)
                    ->where('type', 'eventual')
                    ->whereNotNull('date_end')
                    ->orderBy('date_end', 'desc')
                    ->first();

                if ($lastEventualContract && $lastEventualContract->date_end) {
                    // Calcular los meses entre la fecha de fin del último contrato y la fecha de inicio del nuevo
                    $dateStart = Carbon::parse($request->date_start);
                    $monthsSinceLastContract = $lastEventualContract->date_end->diffInMonths($dateStart);

                    if ($monthsSinceLastContract < 2) {
                        $resp = [
                            'success' => false,
                            'message' => 'Para contratos eventuales, deben pasar mínimo 2 meses desde el último contrato.',
                            'last_contract_end_date' => $lastEventualContract->date_end->format('Y-m-d'),
                            'months_since_last_contract' => $monthsSinceLastContract,
                            'months_remaining' => 2 - $monthsSinceLastContract
                        ];
                        if ($isTurbo) {
                            return response()->json([
                                'success' => false,
                                'message' => $resp['message'],
                                'errors' => ['general' => [$resp['message']]],
                                'last_contract_end_date' => $resp['last_contract_end_date'],
                                'months_remaining' => $resp['months_remaining'],
                            ], 422);
                        }
                        return back()->with('error', $resp['message'])->withInput();
                    }
                }

                // Para eventuales, la fecha de fin es requerida
                if (!$request->date_end) {
                    $resp = [
                        'success' => false,
                        'message' => 'Los contratos eventuales deben tener una fecha de fin.'
                    ];
                    if ($isTurbo) {
                        return response()->json([
                            'success' => false,
                            'message' => $resp['message'],
                            'errors' => ['general' => [$resp['message']]],
                        ], 422);
                    }
                    return back()->with('error', $resp['message'])->withInput();
                }

                if ($request->vacation_days_per_year) {
                    $resp = [
                        'success' => false,
                        'message' => 'Los contratos eventuales no pueden tener dias de vacaciones.'
                    ];
                    if ($isTurbo) {
                        return response()->json([
                            'success' => false,
                            'message' => $resp['message'],
                            'errors' => ['general' => [$resp['message']]],
                        ], 422);
                    }
                    return back()->with('error', $resp['message'])->withInput();
                }
            }

            Contract::create($request->all());
            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contrato registrado exitosamente.',
                ], 201);
            }

            return redirect()->route('contracts.index')
                ->with('success', 'Contrato creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear contrato: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Error al crear contrato: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        try {
            $contract = Contract::findOrFail($id);
            $contractTypes = ['nombrado', 'permanente', 'eventual'];
            $users = User::select('id', 'firstname', 'lastname', 'dni')->get();
            $departments = Department::select('id', 'name')->get();
            $positions = UserType::select('id', 'name')->get();

            return response()->view('contracts._modal_edit', compact('contract', 'users', 'contractTypes', 'departments', 'positions'))
                ->header('Turbo-Frame', 'modal-frame');
        } catch (\Exception $e) {
            Log::error('Error loading contracts edit form: ' . $e->getMessage());
            return view('errors.general', ['message' => 'No se pudo cargar el formulario de edición de contratos.']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:nombrado,permanente,eventual',
            'date_start' => 'required|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'vacation_days_per_year' => 'required|integer',
            'salary' => 'required|numeric',
            'position_id' => 'required|exists:usertypes,id',
            'department_id' => 'required|exists:departments,id',
            'probation_period_months' => 'required|integer',
            'termination_reason' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hay errores en los datos enviados.',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $contract = Contract::findOrFail($id);

            // Validación 1: Verificar si el usuario ya tiene un contrato activo (excluir el contrato actual)
            $activeContract = Contract::where('user_id', $request->user_id)
                ->where('is_active', true)
                ->where('id', '!=', $contract->id)
                ->first();

            if ($activeContract) {
                $resp = [
                    'success' => false,
                    'message' => 'El usuario ya tiene un contrato activo. No se puede asignar este contrato.',
                    'active_contract_id' => $activeContract->id
                ];
                if ($isTurbo) {
                    return response()->json([
                        'success' => false,
                        'message' => $resp['message'],
                        'errors' => ['general' => [$resp['message']]],
                        'active_contract_id' => $activeContract->id,
                    ], 422);
                }
                return back()->with('error', $resp['message'])->withInput();
            }

            // Si es nombrado o permanente, no debe tener fecha de fin
            if (in_array($request->type, ['nombrado', 'permanente'])) {
                if ($request->date_end) {
                    $resp = [
                        'success' => false,
                        'message' => 'Los contratos de tipo nombrado o permanente no deben tener fecha de fin.'
                    ];
                    if ($isTurbo) {
                        return response()->json([
                            'success' => false,
                            'message' => $resp['message'],
                            'errors' => ['general' => [$resp['message']]],
                        ], 422);
                    }
                    return back()->with('error', $resp['message'])->withInput();
                }
                $request->merge(['date_end' => null]);
            }

            // Validación para contratos eventuales
            if ($request->type === 'eventual') {
                $lastEventualContract = Contract::where('user_id', $request->user_id)
                    ->where('type', 'eventual')
                    ->whereNotNull('date_end')
                    ->where('id', '!=', $contract->id)
                    ->orderBy('date_end', 'desc')
                    ->first();

                if ($lastEventualContract && $lastEventualContract->date_end) {
                    $dateStart = Carbon::parse($request->date_start);
                    $monthsSinceLastContract = $lastEventualContract->date_end->diffInMonths($dateStart);

                    if ($monthsSinceLastContract < 2) {
                        $resp = [
                            'success' => false,
                            'message' => 'Para contratos eventuales, deben pasar mínimo 2 meses desde el último contrato.',
                            'last_contract_end_date' => $lastEventualContract->date_end->format('Y-m-d'),
                            'months_since_last_contract' => $monthsSinceLastContract,
                            'months_remaining' => 2 - $monthsSinceLastContract
                        ];
                        if ($isTurbo) {
                            return response()->json([
                                'success' => false,
                                'message' => $resp['message'],
                                'errors' => ['general' => [$resp['message']]],
                                'last_contract_end_date' => $resp['last_contract_end_date'],
                                'months_remaining' => $resp['months_remaining'],
                            ], 422);
                        }
                        return back()->with('error', $resp['message'])->withInput();
                    }
                }

                // Para eventuales, la fecha de fin es requerida
                if (!$request->date_end) {
                    $resp = [
                        'success' => false,
                        'message' => 'Los contratos eventuales deben tener una fecha de fin.'
                    ];
                    if ($isTurbo) {
                        return response()->json([
                            'success' => false,
                            'message' => $resp['message'],
                            'errors' => ['general' => [$resp['message']]],
                        ], 422);
                    }
                    return back()->with('error', $resp['message'])->withInput();
                }

                // Para eventuales no se permiten días de vacaciones
                if ($request->vacation_days_per_year) {
                    $resp = [
                        'success' => false,
                        'message' => 'Los contratos eventuales no pueden tener dias de vacaciones.'
                    ];
                    if ($isTurbo) {
                        return response()->json([
                            'success' => false,
                            'message' => $resp['message'],
                            'errors' => ['general' => [$resp['message']]],
                        ], 422);
                    }
                    return back()->with('error', $resp['message'])->withInput();
                }
            }

            $contract->update($request->all());
            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contrato actualizado exitosamente.',
                ], 201);
            }

            return redirect()->route('contracts.index')
                ->with('success', 'Contrato actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar contrato: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Error al actualizar contrato: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            $contract = Contract::findOrFail($id);
            $contract->delete();
            return redirect()->route('contracts.index')->with('success', 'Contrato eliminado.');
        } catch (\Exception $e) {
            Log::error('Error deleting contract: ' . $e->getMessage());
            return back()->with('error', 'No se pudo eliminar el contrato: ' . $e->getMessage());
        }
    }
}
