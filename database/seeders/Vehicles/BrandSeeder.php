<?php

namespace Database\Seeders\Vehicles;

use App\Models\Brand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            // Marcas asiáticas
            [
                'name' => 'Toyota',
                'description' => 'Fabricante japonés líder en vehículos comerciales y camiones',
                'logo' => null,
            ],
            [
                'name' => 'Hino',
                'description' => 'Marca japonesa especializada en camiones medianos y pesados',
                'logo' => null,
            ],
            [
                'name' => 'Isuzu',
                'description' => 'Fabricante japonés de camiones y vehículos comerciales',
                'logo' => null,
            ],
            [
                'name' => 'Mitsubishi Fuso',
                'description' => 'División de camiones de Mitsubishi Motors',
                'logo' => null,
            ],
            [
                'name' => 'Hyundai',
                'description' => 'Fabricante surcoreano de vehículos comerciales y camiones',
                'logo' => null,
            ],
            
            // Marcas europeas
            [
                'name' => 'Mercedes-Benz',
                'description' => 'Fabricante alemán premium de camiones y vehículos comerciales',
                'logo' => null,
            ],
            [
                'name' => 'Volvo',
                'description' => 'Marca sueca especializada en camiones pesados',
                'logo' => null,
            ],
            [
                'name' => 'MAN',
                'description' => 'Fabricante alemán de camiones y vehículos comerciales',
                'logo' => null,
            ],
            [
                'name' => 'Iveco',
                'description' => 'Marca italiana de vehículos comerciales e industriales',
                'logo' => null,
            ],
            [
                'name' => 'Scania',
                'description' => 'Fabricante sueco de camiones pesados',
                'logo' => null,
            ],
            
            // Marcas americanas
            [
                'name' => 'Ford',
                'description' => 'Fabricante estadounidense de vehículos comerciales y camiones',
                'logo' => null,
            ],
            [
                'name' => 'Chevrolet',
                'description' => 'Marca estadounidense de camiones y vehículos comerciales',
                'logo' => null,
            ],
            [
                'name' => 'International',
                'description' => 'Fabricante estadounidense de camiones comerciales',
                'logo' => null,
            ],
            [
                'name' => 'Freightliner',
                'description' => 'Marca estadounidense de camiones pesados',
                'logo' => null,
            ],
            
            // Marcas chinas
            [
                'name' => 'JAC',
                'description' => 'Fabricante chino de camiones y vehículos comerciales',
                'logo' => null,
            ],
            [
                'name' => 'Foton',
                'description' => 'Marca china de vehículos comerciales y camiones',
                'logo' => null,
            ],
            [
                'name' => 'Dongfeng',
                'description' => 'Fabricante chino de camiones y vehículos comerciales',
                'logo' => null,
            ],
            
            // Otras marcas
            [
                'name' => 'Nissan',
                'description' => 'Fabricante japonés con línea de vehículos comerciales',
                'logo' => null,
            ],
            [
                'name' => 'Volkswagen',
                'description' => 'Marca alemana con división de vehículos comerciales',
                'logo' => null,
            ],
            [
                'name' => 'Kia',
                'description' => 'Fabricante surcoreano de vehículos comerciales ligeros',
                'logo' => null,
            ],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }

        $this->command->info('✅ Se crearon ' . count($brands) . ' marcas de vehículos.');
    }
}
