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
            // Conductor 2
            [
                'username' => 'jperez',
                'dni' => '45678901',
                'firstname' => 'Juan',
                'lastname' => 'Pérez González',
                'birthdate' => '1985-05-15',
                'license' => 'A2B',
                'address' => 'Av. Salaverry 1234',
                'email' => 'jperez@usatexp.pe',
                'email_verified_at' => null,
                'password' => Hash::make('password123'),
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'profile_photo_path' => NULL,
                'usertype_id' => 1, // Conductor
                'status' => 'ACTIVO',
                'phone' => '987654321',
            ],

            // Conductor 3
            [
                'username' => 'rlopez',
                'dni' => '23456789',
                'firstname' => 'Roberto',
                'lastname' => 'López Mendoza',
                'birthdate' => '1990-08-20',
                'license' => 'A3C',
                'address' => 'Jr. Los Pinos 456',
                'email' => 'rlopez@usatexp.pe',
                'email_verified_at' => null,
                'password' => Hash::make('password123'),
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'profile_photo_path' => NULL,
                'usertype_id' => 1, // Conductor
                'status' => 'ACTIVO',
                'phone' => '965432187',
            ],

            // Ayudante 1
            [
                'username' => 'mramirez',
                'dni' => '34567890',
                'firstname' => 'Miguel',
                'lastname' => 'Ramírez Torres',
                'birthdate' => '1995-03-10',
                'license' => NULL, // Ayudante no necesita licencia
                'address' => 'Calle Las Flores 789',
                'email' => 'mramirez@usatexp.pe',
                'email_verified_at' => null,
                'password' => Hash::make('password123'),
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'profile_photo_path' => NULL,
                'usertype_id' => 2, // Ayudante
                'status' => 'ACTIVO',
                'phone' => '945678123',
            ],

            // Ayudante 2
            [
                'username' => 'cgarcia',
                'dni' => '56789012',
                'firstname' => 'Carlos',
                'lastname' => 'García Sánchez',
                'birthdate' => '1998-11-25',
                'license' => NULL,
                'address' => 'Av. Industrial 234',
                'email' => 'cgarcia@usatexp.pe',
                'email_verified_at' => null,
                'password' => Hash::make('password123'),
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'profile_photo_path' => NULL,
                'usertype_id' => 2, // Ayudante
                'status' => 'ACTIVO',
                'phone' => '923456789',
            ],

            // Ayudante 3
            [
                'username' => 'lmartinez',
                'dni' => '67890123',
                'firstname' => 'Luis',
                'lastname' => 'Martínez Rojas',
                'birthdate' => '1997-07-14',
                'license' => NULL,
                'address' => 'Psje. Los Jardines 567',
                'email' => 'lmartinez@usatexp.pe',
                'email_verified_at' => null,
                'password' => Hash::make('password123'),
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'profile_photo_path' => NULL,
                'usertype_id' => 2, // Ayudante
                'status' => 'ACTIVO',
                'phone' => '912345678',
            ],

            // Supervisor
            [
                'username' => 'adiaz',
                'dni' => '78901234',
                'firstname' => 'Ana',
                'lastname' => 'Díaz Vargas',
                'birthdate' => '1982-09-05',
                'license' => 'A2B',
                'address' => 'Urb. Los Sauces 890',
                'email' => 'adiaz@usatexp.pe',
                'email_verified_at' => null,
                'password' => Hash::make('password123'),
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'profile_photo_path' => NULL,
                'usertype_id' => 3, // Supervisor
                'status' => 'ACTIVO',
                'phone' => '998765432',
            ],

            // Coordinador de Zona
            [
                'username' => 'pmorales',
                'dni' => '89012345',
                'firstname' => 'Pedro',
                'lastname' => 'Morales Castro',
                'birthdate' => '1988-02-18',
                'license' => NULL,
                'address' => 'Av. Prolongación 123',
                'email' => 'pmorales@usatexp.pe',
                'email_verified_at' => null,
                'password' => Hash::make('password123'),
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'profile_photo_path' => NULL,
                'usertype_id' => 4, // Coordinador de Zona
                'status' => 'ACTIVO',
                'phone' => '976543210',
            ],

            // Mecánico
            [
                'username' => 'fvega',
                'dni' => '90123456',
                'firstname' => 'Fernando',
                'lastname' => 'Vega Núñez',
                'birthdate' => '1993-12-08',
                'license' => 'A1',
                'address' => 'Calle Comercio 345',
                'email' => 'fvega@usatexp.pe',
                'email_verified_at' => null,
                'password' => Hash::make('password123'),
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'profile_photo_path' => NULL,
                'usertype_id' => 6, // Mecánico
                'status' => 'ACTIVO',
                'phone' => '954321098',
            ],

            // Usuario inactivo (ejemplo)
            [
                'username' => 'jcastro',
                'dni' => '12345678',
                'firstname' => 'José',
                'lastname' => 'Castro Ruiz',
                'birthdate' => '1991-04-22',
                'license' => NULL,
                'address' => 'Jr. Progreso 678',
                'email' => 'jcastro@usatexp.pe',
                'email_verified_at' => null,
                'password' => Hash::make('password123'),
                'two_factor_secret' => NULL,
                'two_factor_recovery_codes' => NULL,
                'two_factor_confirmed_at' => NULL,
                'remember_token' => NULL,
                'current_team_id' => NULL,
                'profile_photo_path' => NULL,
                'usertype_id' => 2, // Ayudante
                'status' => 'INACTIVO', // Usuario inactivo
                'phone' => '932109876',
            ],
        ];

        foreach ($userTypes as $type) {
            User::create($type);
        }
    }
}
