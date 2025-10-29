<?php

namespace Database\Seeders\Vehicles;

use App\Models\VehicleColor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            [
                'name' => 'Blanco',
                'rgb_code' => '#FFFFFF',
            ],
            [
                'name' => 'Verde',
                'rgb_code' => '#00FF00',
            ],
            [
                'name' => 'Amarillo',
                'rgb_code' => '#FFFF00',
            ],
            [
                'name' => 'Naranja',
                'rgb_code' => '#FFA500',
            ],
            [
                'name' => 'Azul',
                'rgb_code' => '#0000FF',
            ],
            [
                'name' => 'Rojo',
                'rgb_code' => '#FF0000',
            ],
            [
                'name' => 'Gris',
                'rgb_code' => '#808080',
            ],
            [
                'name' => 'Negro',
                'rgb_code' => '#000000',
            ],
            [
                'name' => 'Verde Lima',
                'rgb_code' => '#32CD32',
            ],
            [
                'name' => 'Verde Olivo',
                'rgb_code' => '#808000',
            ],
            [
                'name' => 'Plateado',
                'rgb_code' => '#C0C0C0',
            ],
            [
                'name' => 'Beige',
                'rgb_code' => '#F5F5DC',
            ],
        ];

        foreach ($colors as $color) {
            VehicleColor::create($color);
        }

        $this->command->info('✅ Se crearon ' . count($colors) . ' colores de vehículos.');
    }
}
