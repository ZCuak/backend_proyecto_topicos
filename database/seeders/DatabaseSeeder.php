<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Programacion\TurnoSeeder;
use Database\Seeders\Programacion\ZonasCoordsSeeder;
use Database\Seeders\Programacion\ZonasSeeder;
use Database\Seeders\Users\ContractsSeeder;
use Database\Seeders\Users\UserSeeder;
use Database\Seeders\Users\UserTypeSeeder;
use Database\Seeders\Vehicles\BrandModelSeeder;
use Database\Seeders\Vehicles\BrandSeeder;
use Database\Seeders\Vehicles\ColorSeeder;
use Database\Seeders\Vehicles\VehicleTypeSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            GeographicDataSeeder::class,
            SectorSeeder::class,

            TurnoSeeder::class,
            ZonasSeeder::class,
            ZonasCoordsSeeder::class,

            UserTypeSeeder::class,
            UserSeeder::class,

            VehicleTypeSeeder::class,
            ColorSeeder::class,
            BrandSeeder::class,
            BrandModelSeeder::class,  

            ContractsSeeder::class,
        ]);
    }
}
