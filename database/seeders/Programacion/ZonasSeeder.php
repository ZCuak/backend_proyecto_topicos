<?php

namespace Database\Seeders\Programacion;

use App\Models\Zone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ZonasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Zona N°01',
                'area' => 1260656.00,
                'description' => null,
                'district_id' => 1259, // Chiclayo
                'sector_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Zona N°02',
                'area' => 534800.00,
                'description' => null,
                'district_id' => 1259, // Chiclayo
                'sector_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Zona N°03',
                'area' => 459559.00,
                'description' => null,
                'district_id' => 1259, // Chiclayo
                'sector_id' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($zones as $zone) {
            Zone::create($zone);
        }

        $this->command->info('✅ Se crearon ' . count($zones) . ' zonas de recolección.');
    }
}
