<?php

namespace Database\Seeders\Users;

use App\Models\UserType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userTypes = [
            // 🔒 FUNCIONES PREDEFINIDAS DEL SISTEMA (NO SE PUEDEN ELIMINAR)
            [
                'name' => 'Conductor',
                'description' => 'Personal encargado de conducir los vehículos de recolección',
                'is_system' => true, // ← Protegida
            ],
            [
                'name' => 'Ayudante',
                'description' => 'Personal de apoyo en la recolección de residuos',
                'is_system' => true, // ← Protegida
            ],

            // 📝 FUNCIONES ADICIONALES (SE PUEDEN CREAR/ELIMINAR)
            [
                'name' => 'Supervisor',
                'description' => 'Personal que supervisa las operaciones de recolección',
                'is_system' => false,
            ],
            [
                'name' => 'Coordinador de Zona',
                'description' => 'Personal encargado de coordinar las rutas por zona',
                'is_system' => false,
            ],
            [
                'name' => 'Auxiliar de Limpieza',
                'description' => 'Personal de apoyo para limpieza de áreas específicas',
                'is_system' => false,
            ],
            [
                'name' => 'Mecánico',
                'description' => 'Personal de mantenimiento de vehículos',
                'is_system' => false,
            ],
        ];

        foreach ($userTypes as $type) {
            UserType::create($type);
        }
    }
}
