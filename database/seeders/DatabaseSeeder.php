<?php

namespace Database\Seeders;

use App\Models\User;
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
            // GeographicDataSeeder::class,

            UserTypeSeeder::class,
            UserSeeder::class,

            VehicleTypeSeeder::class,
            ColorSeeder::class,
            BrandSeeder::class,
            BrandModelSeeder::class,  
        ]);
    }
}
