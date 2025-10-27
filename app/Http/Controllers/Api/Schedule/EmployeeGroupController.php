<?php

namespace App\Http\Controllers\Api\Schedule;

use App\Http\Controllers\Controller;
use App\Models\EmployeeGroup;
use App\Models\ConfigGroup;
use App\Models\Schedule;
use App\Models\Vehicle;
use App\Models\Zone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class EmployeeGroupController extends Controller
{
    /**
     * Mostrar listado de groups
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $query = EmployeeGroup::with(['configgroups','zone','schedule','vehicle']);
            $columns = Schema::getColumnListing('employeegroups');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            $groups = $query->paginate(10);
            return view('groups.index', compact('groups', 'search'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar grupos: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de creación (Turbo modal)
     */
    public function create(Request $request)
    {
        $zones = Zone::all();
        $schedules = Schedule::all();
        $vehicles = Vehicle::all();
        $users = User::all();
        $group = new EmployeeGroup();

        return response()
            ->view('groups._modal_create', compact('zones','schedules','vehicles', 'group','users'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Formulario de edición (Turbo modal)
     */
    public function edit($id)
    {
        $zones = Zone::all();
        $schedules = Schedule::all();
        $vehicles = Vehicle::all();
        $users = User::all();
        $group = EmployeeGroup::with('configgroups')->findOrFail($id);

        // Decodifica los días
        $group->days = is_array($group->days) ? $group->days : json_decode($group->days, true);

        // Obtén los IDs de los empleados en orden
        $configGroups = $group->configgroups->sortBy('id')->values();
        $driver_id = $configGroups[0]->user_id ?? null;
        $user1_id  = $configGroups[1]->user_id ?? null;
        $user2_id  = $configGroups[2]->user_id ?? null;

        return view('groups._modal_edit', compact(
            'group', 'zones', 'schedules', 'vehicles', 'users',
            'driver_id', 'user1_id', 'user2_id'
        ));
    }

    /**
     * Guardar nuevo registro
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'zone_id' => 'required|exists:zones,id',
            'schedule_id' => 'required|exists:schedules,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:users,id',
            'user1_id' => 'required|exists:users,id|different:driver_id',
            'user2_id' => 'nullable|exists:users,id|different:driver_id|different:user1_id',
            'days' => 'required|array|min:1',
            'days.*' => 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
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
            $data = $validator->validated();

            $group = EmployeeGroup::create([
                'name' => $data['name'],
                'zone_id' => $data['zone_id'],
                'schedule_id' => $data['schedule_id'],
                'vehicle_id' => $data['vehicle_id'],
                'days' => json_encode($data['days']), // o implode(',', $validated['days'])
            ]);

            $users = [
                $data['driver_id'],
                $data['user1_id'],
                $data['user2_id'],
            ];

            foreach ($users as $userId) {
                ConfigGroup::create([
                    'group_id' => $group->id,
                    'user_id' => $userId,
                ]);
            }

            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Grupo registrado exitosamente.',
                ], 201);
            }

            return redirect()->route('groups.index')
                ->with('success', 'Grupo registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear grupos: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al crear groups: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar datos
     */
    public function update(Request $request, $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'zone_id' => 'required|exists:zones,id',
            'schedule_id' => 'required|exists:schedules,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:users,id',
            'user1_id' => 'required|exists:users,id|different:driver_id',
            'user2_id' => 'nullable|exists:users,id|different:driver_id|different:user1_id',
            'days' => 'required|array|min:1',
            'days.*' => 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
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
            $data = $validator->validated();

            $group = EmployeeGroup::findOrFail($id);

            $group->update([
                'name' => $data['name'],
                'zone_id' => $data['zone_id'],
                'schedule_id' => $data['schedule_id'],
                'vehicle_id' => $data['vehicle_id'],
                'days' => json_encode($data['days']),
            ]);

            // Elimina los empleados anteriores del grupo
            ConfigGroup::where('group_id', $group->id)->delete();

            // Inserta los nuevos empleados
            $users = [
                $data['driver_id'],
                $data['user1_id'],
                $data['user2_id'],
            ];

            foreach (array_filter($users) as $userId) {
                ConfigGroup::create([
                    'group_id' => $group->id,
                    'user_id' => $userId,
                ]);
            }

            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Grupo actualizado exitosamente.',
                ], 200);
            }

            return redirect()->route('groups.index')
                ->with('success', 'Grupo actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar grupo: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al actualizar grupo: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar (Soft Delete)
     */
    public function destroy($id)
    {
        try {
            $group = EmployeeGroup::find($id);
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grupo no encontrado.',
                ], 404);
            }

            $group->delete();

            return response()->json([
                'success' => true,
                'message' => 'Grupo eliminado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar grupo: ' . $e->getMessage(),
            ], 500);
        }
    }
}
