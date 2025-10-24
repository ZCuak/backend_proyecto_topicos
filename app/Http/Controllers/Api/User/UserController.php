<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Mostrar listado de personal (index)
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('perPage', 10);
            $all = $request->boolean('all', false);

            $query = User::with(['usertype']);
            $columns = Schema::getColumnListing('users');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            if ($all) {
                $personales = $query->get();
            } else {
                $personales = $query->paginate($perPage);
            }

            return view('personal.index', compact('personales', 'search'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar personal: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('personal.create');
    }

    /**
     * Registrar nuevo personal
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:users,username',
            'dni' => 'required|digits:8|unique:users,dni',
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'birthdate' => 'nullable|date',
            'license' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'profile_photo_path' => 'nullable|string|max:255',
            'usertype_id' => 'required|exists:usertypes,id',
            'status' => 'in:ACTIVO,INACTIVO'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $data = $validator->validated();
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);
            DB::commit();

            return redirect()
                ->route('personal.index')
                ->with('success', 'Personal registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear personal: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar perfil detallado
     */
    public function show($id)
    {
        try {
            $user = User::with(['usertype'])->find($id);
            if (!$user) {
                return back()->with('error', 'Personal no encontrado.');
            }

            return view('personal.show', compact('user'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al obtener personal: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        try {
            $user = User::with(['usertype'])->find($id);
            if (!$user) {
                return redirect()
                    ->route('personal.index')
                    ->with('error', 'Personal no encontrado.');
            }

            return view('personal.edit', compact('user'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar el formulario de edición: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar datos del personal
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return back()->with('error', 'Personal no encontrado.');
            }

            $validator = Validator::make($request->all(), [
                'username' => 'sometimes|required|string|max:100|unique:users,username,' . $id,
                'dni' => 'sometimes|required|digits:8|unique:users,dni,' . $id,
                'firstname' => 'sometimes|required|string|max:100',
                'lastname' => 'sometimes|required|string|max:100',
                'birthdate' => 'nullable|date',
                'license' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8',
                'profile_photo_path' => 'nullable|string|max:255',
                'usertype_id' => 'exists:usertypes,id',
                'status' => 'in:ACTIVO,INACTIVO'
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $data = $validator->validated();
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            return redirect()
                ->route('personal.index')
                ->with('success', 'Datos del personal actualizados correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar personal: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar (Soft Delete)
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return back()->with('error', 'Personal no encontrado.');
            }

            $user->delete();

            return redirect()
                ->route('personal.index')
                ->with('success', 'Personal eliminado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar personal: ' . $e->getMessage());
        }
    }
}
