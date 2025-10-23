<?php

namespace Database\Seeders\Users;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userTypes = [
            // 🔒 FUNCIONES PREDEFINIDAS DEL SISTEMA (NO SE PUEDEN ELIMINAR)
            // Usuario de ejemplo del comando SQL
            [
                'username' => 'atest2',
                'dni' => '70314454',
                'firstname' => 'William',
                'lastname' => 'Salazar',
                'birthdate' => '2000-12-26',
                'license' => 'A1',
                'address' => 'El polo 756',
                'email' => 'awilliams@usatex2p.pe',
                'email_verified_at' => null,
                'password' => Hash::make('password123'), // Contraseña hasheada
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'current_team_id' => NULL,
                'profile_photo_path' => NULL,
                'usertype_id' => 3, // supervisor
                'status' => 'ACTIVO',
                'phone' => NULL,
            ],
        ];

        foreach ($userTypes as $type) {
            User::create($type);
        }
    }
}
