<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UbigeoTransferSeeder extends Seeder
{
    public function run(): void
    {
        // Conexión al origen (base local)
        $source = DB::connection('pgsql_local');

        // Conexión destino (por defecto .env = nube)
        $target = DB::connection('pgsql');

        $this->command->info('Iniciando transferencia de datos desde tabla ubigeo...');

        // 1️⃣ Departamentos
        $departamentos = $source->table('ubigeo')->where('tipo', '2')->get();
        foreach ($departamentos as $dep) {
            $depId = $target->table('departments')->insertGetId([
                'name' => $dep->descripcion,
                'code' => $dep->codigo ?? $dep->id, // depende del campo que tengas
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2️⃣ Provincias del departamento
            $provincias = $source->table('ubigeo')
                ->where('tipo', '3')
                ->where('ubigeo_id', $dep->id)
                ->get();

            foreach ($provincias as $prov) {
                $provId = $target->table('provinces')->insertGetId([
                    'name' => $prov->descripcion,
                    'code' => $prov->codigo ?? $prov->id,
                    'department_id' => $depId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 3️⃣ Distritos de la provincia
                $distritos = $source->table('ubigeo')
                    ->where('tipo', '4')
                    ->where('ubigeo_id', $prov->id)
                    ->get();

                foreach ($distritos as $dist) {
                    $target->table('districts')->insert([
                        'name' => $dist->descripcion,
                        'code' => $dist->codigo ?? $dist->id,
                        'department_id' => $depId,
                        'province_id' => $provId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('✅ Transferencia completada exitosamente.');
    }
}
