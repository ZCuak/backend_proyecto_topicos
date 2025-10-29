<?php

namespace Database\Seeders\Vehicles;

use App\Models\Brand;
use App\Models\BrandModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener IDs de marcas
        $toyota = Brand::where('name', 'Toyota')->first();
        $hino = Brand::where('name', 'Hino')->first();
        $isuzu = Brand::where('name', 'Isuzu')->first();
        $mitsubishi = Brand::where('name', 'Mitsubishi Fuso')->first();
        $hyundai = Brand::where('name', 'Hyundai')->first();
        $mercedes = Brand::where('name', 'Mercedes-Benz')->first();
        $volvo = Brand::where('name', 'Volvo')->first();
        $ford = Brand::where('name', 'Ford')->first();
        $chevrolet = Brand::where('name', 'Chevrolet')->first();
        $jac = Brand::where('name', 'JAC')->first();

        $models = [
            // Toyota
            [
                'name' => 'Dyna',
                'code' => 'TYT-DYNA',
                'description' => 'Camión ligero ideal para recolección urbana',
                'brand_id' => $toyota->id,
            ],
            [
                'name' => 'Hilux',
                'code' => 'TYT-HLX',
                'description' => 'Camioneta pick-up para transporte ligero',
                'brand_id' => $toyota->id,
            ],
            
            // Hino
            [
                'name' => '300 Series',
                'code' => 'HIN-300',
                'description' => 'Camión ligero para distribución urbana',
                'brand_id' => $hino->id,
            ],
            [
                'name' => '500 Series',
                'code' => 'HIN-500',
                'description' => 'Camión mediano para recolección de residuos',
                'brand_id' => $hino->id,
            ],
            [
                'name' => '700 Series',
                'code' => 'HIN-700',
                'description' => 'Camión pesado para transporte de carga',
                'brand_id' => $hino->id,
            ],
            
            // Isuzu
            [
                'name' => 'ELF',
                'code' => 'ISZ-ELF',
                'description' => 'Camión ligero versátil para múltiples aplicaciones',
                'brand_id' => $isuzu->id,
            ],
            [
                'name' => 'FTR',
                'code' => 'ISZ-FTR',
                'description' => 'Camión mediano para transporte de residuos',
                'brand_id' => $isuzu->id,
            ],
            [
                'name' => 'FVR',
                'code' => 'ISZ-FVR',
                'description' => 'Camión pesado para grandes volúmenes',
                'brand_id' => $isuzu->id,
            ],
            
            // Mitsubishi Fuso
            [
                'name' => 'Canter',
                'code' => 'MIT-CANT',
                'description' => 'Camión ligero compacto y eficiente',
                'brand_id' => $mitsubishi->id,
            ],
            [
                'name' => 'Fighter',
                'code' => 'MIT-FIGH',
                'description' => 'Camión mediano robusto',
                'brand_id' => $mitsubishi->id,
            ],
            
            // Hyundai
            [
                'name' => 'HD65',
                'code' => 'HYU-HD65',
                'description' => 'Camión ligero económico',
                'brand_id' => $hyundai->id,
            ],
            [
                'name' => 'HD78',
                'code' => 'HYU-HD78',
                'description' => 'Camión mediano de alta capacidad',
                'brand_id' => $hyundai->id,
            ],
            [
                'name' => 'Mighty',
                'code' => 'HYU-MIGH',
                'description' => 'Camión ligero para distribución',
                'brand_id' => $hyundai->id,
            ],
            
            // Mercedes-Benz
            [
                'name' => 'Atego',
                'code' => 'MER-ATEG',
                'description' => 'Camión mediano premium',
                'brand_id' => $mercedes->id,
            ],
            [
                'name' => 'Accelo',
                'code' => 'MER-ACCE',
                'description' => 'Camión ligero urbano',
                'brand_id' => $mercedes->id,
            ],
            
            // Volvo
            [
                'name' => 'FL',
                'code' => 'VOL-FL',
                'description' => 'Camión mediano para distribución urbana',
                'brand_id' => $volvo->id,
            ],
            [
                'name' => 'FE',
                'code' => 'VOL-FE',
                'description' => 'Camión mediano eléctrico',
                'brand_id' => $volvo->id,
            ],
            
            // Ford
            [
                'name' => 'Cargo 816',
                'code' => 'FOR-C816',
                'description' => 'Camión ligero para carga general',
                'brand_id' => $ford->id,
            ],
            [
                'name' => 'Cargo 1722',
                'code' => 'FOR-C1722',
                'description' => 'Camión mediano robusto',
                'brand_id' => $ford->id,
            ],
            
            // Chevrolet
            [
                'name' => 'NPR',
                'code' => 'CHV-NPR',
                'description' => 'Camión ligero eficiente',
                'brand_id' => $chevrolet->id,
            ],
            [
                'name' => 'FTR',
                'code' => 'CHV-FTR',
                'description' => 'Camión mediano para carga pesada',
                'brand_id' => $chevrolet->id,
            ],
            
            // JAC
            [
                'name' => 'N56',
                'code' => 'JAC-N56',
                'description' => 'Camión ligero económico',
                'brand_id' => $jac->id,
            ],
            [
                'name' => 'N75',
                'code' => 'JAC-N75',
                'description' => 'Camión mediano de buena relación precio-calidad',
                'brand_id' => $jac->id,
            ],
        ];

        foreach ($models as $model) {
            BrandModel::create($model);
        }

        $this->command->info('✅ Se crearon ' . count($models) . ' modelos de vehículos.');
    }
}
