<?php

namespace Database\Seeders\Vehicles;

use App\Models\VehicleType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleTypes = [
            [
                'name' => 'Camión Compactador',
                'description' => 'Vehículo con sistema de compactación para residuos sólidos. Capacidad de 10-20 m³.'
            ],
            [
                'name' => 'Camión Baranda',
                'description' => 'Camión con barandas laterales para transporte de residuos voluminosos.'
            ],
            [
                'name' => 'Camioneta',
                'description' => 'Vehículo ligero para zonas de difícil acceso o recolección selectiva.'
            ],
            [
                'name' => 'Volquete',
                'description' => 'Camión con tolva basculante para descarga rápida de residuos.'
            ],
            [
                'name' => 'Triciclo Motorizado',
                'description' => 'Vehículo pequeño para recolección en zonas estrechas o residenciales.'
            ],
            [
                'name' => 'Camión Cisterna',
                'description' => 'Vehículo especializado para limpieza y lavado de áreas públicas.'
            ],
            [
                'name' => 'Camión Grúa',
                'description' => 'Vehículo equipado con grúa para recolección de contenedores grandes.'
            ],
            [
                'name' => 'Motocarro',
                'description' => 'Vehículo de tres ruedas para recolección domiciliaria en zonas pequeñas.'
            ],
        ];

        foreach ($vehicleTypes as $type) {
            VehicleType::create($type);
        }

        $this->command->info('✅ Se crearon ' . count($vehicleTypes) . ' tipos de vehículos.');
    }
}
