<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TestController extends Controller
{
    /**
     * Endpoint de prueba para verificar la conexión
     */
    public function test(): JsonResponse
    {
        return response()->json([
            'message' => '¡Conexión exitosa entre Angular y Laravel!',
            'status' => 'success',
            'timestamp' => now(),
            'data' => [
                'backend' => 'Laravel',
                'frontend' => 'Angular',
                'database' => 'PostgreSQL'
            ]
        ]);
    }

    /**
     * Endpoint para obtener datos de ejemplo
     */
    public function getData(): JsonResponse
    {
        $data = [
            'users' => [
                ['id' => 1, 'name' => 'Juan Pérez', 'email' => 'juan@example.com'],
                ['id' => 2, 'name' => 'María García', 'email' => 'maria@example.com'],
                ['id' => 3, 'name' => 'Carlos López', 'email' => 'carlos@example.com']
            ],
            'total' => 3
        ];

        return response()->json([
            'message' => 'Datos obtenidos correctamente',
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Endpoint para crear un nuevo registro
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255'
        ]);

        // Simular creación de registro
        $newUser = [
            'id' => rand(100, 999),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'created_at' => now()
        ];

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'status' => 'success',
            'data' => $newUser
        ], 201);
    }
}
