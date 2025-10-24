<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Usertype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Mostrar listado de personal
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $query = User::with(['usertype']);
            $columns = Schema::getColumnListing('users');

            if ($search) {
                $query->where(function ($q) use ($columns, $search) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'ILIKE', "%{$search}%");
                    }
                });
            }

            $personales = $query->paginate(10);
            return view('personal.index', compact('personales', 'search'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al listar personal: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de creación (Turbo modal)
     */
    public function create(Request $request)
    {
        $usertypes = Usertype::all();
        $personal = new User();

        return response()
            ->view('personal._modal_create', compact('usertypes', 'personal'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Formulario de edición (Turbo modal)
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $usertypes = Usertype::all();

        return response()
            ->view('personal._modal_edit', compact('user', 'usertypes'))
            ->header('Turbo-Frame', 'modal-frame');
    }

    /**
     * Guardar nuevo registro
     */
    public function store(Request $request)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:users,username',
            'dni' => 'required|digits:8|unique:users,dni',
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'birthdate' => 'nullable|date',
            'license' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'usertype_id' => 'required|exists:usertypes,id',
            'status' => 'in:ACTIVO,INACTIVO',
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
            $data['password'] = Hash::make($data['password']);

            // Manejo opcional de foto de perfil (si llega como file)
            if ($request->hasFile('profile_photo_path')) {
                $data['profile_photo_path'] = $request->file('profile_photo_path')->store('profiles', 'public');
            }

            User::create($data);
            DB::commit();

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Personal registrado exitosamente.',
                ], 201);
            }

            return redirect()->route('personal.index')
                ->with('success', 'Personal registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear personal: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al crear personal: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar datos
     */
    public function update(Request $request, $id)
    {
        $isTurbo = $request->header('Turbo-Frame') || $request->expectsJson();

        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Personal no encontrado.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:100|unique:users,username,' . $id,
                'dni' => 'required|digits:8|unique:users,dni,' . $id,
                'firstname' => 'required|string|max:100',
                'lastname' => 'required|string|max:100',
                'birthdate' => 'nullable|date',
                'license' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'required|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8',
                'usertype_id' => 'required|exists:usertypes,id',
                'status' => 'in:ACTIVO,INACTIVO',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // Foto nueva reemplaza la anterior
            if ($request->hasFile('profile_photo_path')) {
                $data['profile_photo_path'] = $request->file('profile_photo_path')->store('profiles', 'public');
            }

            $user->update($data);

            if ($isTurbo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Datos del personal actualizados correctamente.',
                ], 200);
            }

            return redirect()->route('personal.index')
                ->with('success', 'Datos del personal actualizados correctamente.');
        } catch (\Exception $e) {
            if ($isTurbo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
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
                return response()->json([
                    'success' => false,
                    'message' => 'Personal no encontrado.',
                ], 404);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Personal eliminado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar personal: ' . $e->getMessage(),
            ], 500);
        }
    }
}
