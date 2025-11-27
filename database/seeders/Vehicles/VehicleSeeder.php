<?php

namespace Database\Seeders\Vehicles;

use App\Models\Vehicle;
use App\Models\Brand;
use App\Models\BrandModel;
use App\Models\VehicleType;
use App\Models\VehicleColor;
use App\Models\VehicleImage;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener referencias a datos existentes
        $hino = Brand::where('name', 'Hino')->first();
        $isuzu = Brand::where('name', 'Isuzu')->first();
        $hyundai = Brand::where('name', 'Hyundai')->first();
        $mitsubishi = Brand::where('name', 'Mitsubishi Fuso')->first();

        $hinoModel = BrandModel::where('code', 'HIN-500')->first(); // Hino 500 Series
        $isuzuModel = BrandModel::where('code', 'ISZ-FTR')->first(); // Isuzu FTR
        $hyundaiModel = BrandModel::where('code', 'HYU-HD78')->first(); // Hyundai HD78
        $mitsubishiModel = BrandModel::where('code', 'MIT-CANT')->first(); // Mitsubishi Canter

        $compactador = VehicleType::where('name', 'Camión Compactador')->first();
        $baranda = VehicleType::where('name', 'Camión Baranda')->first();
        $volquete = VehicleType::where('name', 'Volquete')->first();

        $verde = VehicleColor::where('name', 'Verde')->first();
        $blanco = VehicleColor::where('name', 'Blanco')->first();
        $amarillo = VehicleColor::where('name', 'Amarillo')->first();
        $naranja = VehicleColor::where('name', 'Naranja')->first();

        $vehicles = [
            // Vehículo 1: Hino Compactador
            [
                'name' => 'Compactador Hino 500',
                'code' => 'VEH-COMP-001',
                'plate' => 'M9X-584',
                'year' => 2020,
                'occupant_capacity' => 3,
                'load_capacity' => 8000, // 8 toneladas
                'compaction_capacity' => 16, // 16 m³
                'fuel_capacity' => 150, // 150 litros
                'description' => 'Camión compactador para recolección de residuos sólidos urbanos. Equipado con sistema de compactación hidráulica.',
                'status' => 'DISPONIBLE',
                'brand_id' => $hino->id,
                'model_id' => $hinoModel->id,
                'type_id' => $compactador->id,
                'color_id' => $verde->id,
            ],

            // Vehículo 2: Isuzu Compactador
            [
                'name' => 'Compactador Isuzu FTR',
                'code' => 'VEH-COMP-002',
                'plate' => 'HCV-976',
                'year' => 2019,
                'occupant_capacity' => 3,
                'load_capacity' => 7500, // 7.5 toneladas
                'compaction_capacity' => 14, // 14 m³
                'fuel_capacity' => 140, // 140 litros
                'description' => 'Camión compactador mediano para rutas urbanas. Sistema de compactación de carga trasera.',
                'status' => 'DISPONIBLE',
                'brand_id' => $isuzu->id,
                'model_id' => $isuzuModel->id,
                'type_id' => $compactador->id,
                'color_id' => $blanco->id,
            ],

            // Vehículo 3: Hyundai Baranda
            [
                'name' => 'Baranda Hyundai HD78',
                'code' => 'VEH-BAR-001',
                'plate' => 'AGD-T6',
                'year' => 2021,
                'occupant_capacity' => 3,
                'load_capacity' => 6000, // 6 toneladas
                'compaction_capacity' => 0, // Sin compactación
                'fuel_capacity' => 120, // 120 litros
                'description' => 'Camión con baranda para transporte de residuos voluminosos y material reciclable.',
                'status' => 'DISPONIBLE',
                'brand_id' => $hyundai->id,
                'model_id' => $hyundaiModel->id,
                'type_id' => $baranda->id,
                'color_id' => $amarillo->id,
            ],

            // Vehículo 4: Mitsubishi Volquete
            [
                'name' => 'Volquete Mitsubishi Canter',
                'code' => 'VEH-VOL-001',
                'plate' => 'C2X-891',
                'year' => 2018,
                'occupant_capacity' => 2,
                'load_capacity' => 5000, // 5 toneladas
                'compaction_capacity' => 0, // Sin compactación
                'fuel_capacity' => 100, // 100 litros
                'description' => 'Camión volquete para transporte y descarga rápida de residuos de construcción.',
                'status' => 'DISPONIBLE',
                'brand_id' => $mitsubishi->id,
                'model_id' => $mitsubishiModel->id,
                'type_id' => $volquete->id,
                'color_id' => $naranja->id,
            ],
        ];

        $imagePaths = [
            'vehicles/1764281417_1.jpg',
            'vehicles/1764281520_1.png',
        ];

        foreach ($vehicles as $index => $vehicleData) {
            // Crear el vehículo
            $vehicle = Vehicle::create($vehicleData);
            $vehicleNumber = $index + 1;
            $firstImageIsProfile = ($vehicleNumber % 2 !== 0); // true si es impar

            // Crear las 2 imágenes
            VehicleImage::create([
                'path' => $imagePaths[0],
                'is_profile' => $firstImageIsProfile,
                'vehicle_id' => $vehicle->id,
            ]);

            VehicleImage::create([
                'path' => $imagePaths[1],
                'is_profile' => !$firstImageIsProfile,
                'vehicle_id' => $vehicle->id,
            ]);

            $this->command->info("✅ Vehículo #{$vehicleNumber}: {$vehicle->plate} creado con 2 imágenes");
        }

        $this->command->info('✅ Se crearon ' . count($vehicles) . ' vehículos.');
        $this->command->info('   - DISPONIBLES: ' . collect($vehicles)->where('status', 'ACTIVO')->count());
        $this->command->info('   - EN MANTENIMIENTO: ' . collect($vehicles)->where('status', 'MANTENIMIENTO')->count());
    }
}
