<?php

namespace Database\Seeders\Programacion;

use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TurnoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = [
            // Turnos diurnos
            [
                'name' => 'Mañana',
                'time_start' => '06:00',
                'time_end' => '12:00',
                'description' => 'Turno matutino - Inicio temprano para recolección residencial',
            ],
            [
                'name' => 'Tarde',
                'time_start' => '12:00',
                'time_end' => '18:00',
                'description' => 'Turno vespertino - Recolección en zonas comerciales y residenciales',
            ],
            [
                'name' => 'Noche',
                'time_start' => '18:00',
                'time_end' => '23:00',
                'description' => 'Turno nocturno - Recolección en zonas nocturnas',
            ],
        ];

        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }
    }
}
