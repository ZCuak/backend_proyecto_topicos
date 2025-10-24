<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login (web)
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Procesar login web (sesión)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'], // o 'username' si lo usas así
            'password' => ['required', 'string'],
        ]);

        // Intentar autenticar
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/'); // redirige al dashboard
        }

        // Si falla
        return back()->withErrors([
            'email' => 'Las credenciales son incorrectas.',
        ])->onlyInput('email');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /* ============================================
       OPCIONAL: Métodos API (pueden convivir)
       ============================================ */

    public function apiLogin(Request $request)
    {
        $request->validate([
            'username'  => 'required|string',
            'password'  => 'required|string',
        ]);

        $usuario = User::where('username', $request->username)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $token = $usuario->createToken('api-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'usuario'      => $usuario,
        ]);
    }

    public function apiLogout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
