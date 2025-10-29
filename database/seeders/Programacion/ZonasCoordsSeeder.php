<?php

namespace Database\Seeders\Programacion;

use App\Models\ZoneCoord;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ZonasCoordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coords = [
            // Zona N°01 - 4 puntos
            [
                'latitude' => -6.7613362,
                'longitude' => -79.8343205,
                'zone_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'latitude' => -6.7619754,
                'longitude' => -79.8293638,
                'zone_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'latitude' => -6.7569147,
                'longitude' => -79.8203087,
                'zone_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'latitude' => -6.7484444,
                'longitude' => -79.8329258,
                'zone_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Zona N°02 - 5 puntos
            [
                'latitude' => -6.7487427,
                'longitude' => -79.8498344,
                'zone_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'latitude' => -6.7543683,
                'longitude' => -79.8503923,
                'zone_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'latitude' => -6.7550928,
                'longitude' => -79.8436117,
                'zone_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'latitude' => -6.7461430,
                'longitude' => -79.8426676,
                'zone_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'latitude' => -6.7491263,
                'longitude' => -79.8458433,
                'zone_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Zona N°03 - 4 puntos
            [
                'latitude' => -6.7557747,
                'longitude' => -79.8379040,
                'zone_id' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'latitude' => -6.7460578,
                'longitude' => -79.8368740,
                'zone_id' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'latitude' => -6.7485723,
                'longitude' => -79.8328400,
                'zone_id' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'latitude' => -6.7562008,
                'longitude' => -79.8336124,
                'zone_id' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($coords as $coord) {
            ZoneCoord::create($coord);
        }

        $this->command->info('✅ Se crearon ' . count($coords) . ' coordenadas de zonas.');
        $this->command->info('   - Zona N°01: 4 puntos');
        $this->command->info('   - Zona N°02: 5 puntos');
        $this->command->info('   - Zona N°03: 4 puntos');
    }
}
