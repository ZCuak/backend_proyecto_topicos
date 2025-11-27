<?php

namespace Database\Seeders\changes;

use App\Models\Motive;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MotiveSeeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $motives = [
            ['name' => 'Corrección de datos'],
            ['name' => 'Cambio de turno'],
            ['name' => 'Cambio de vehículo'],
            ['name' => 'Reemplazo de personal'],
            ['name' => 'Ajuste operativo'],
        ];

        foreach ($motives as $motive) {
            Motive::create($motive);
        }

        $this->command->info('✅ Se crearon ' . count($motives) . ' motivos.');
    }
}
