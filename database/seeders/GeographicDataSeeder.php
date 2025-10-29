<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeographicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('seeders/csv');

        // Importar Departments
        $this->importCSV($csvPath . '/departments.csv', 'departments');

        // Importar Provinces
        $this->importCSV($csvPath . '/provinces.csv', 'provinces');

        // Importar Districts
        $this->importCSV($csvPath . '/districts.csv', 'districts');

        $this->command->info('✅ Datos geográficos importados exitosamente');
    }

    /**
     * Importar CSV genérico
     */
    private function importCSV(string $filePath, string $tableName): void
    {
        if (!file_exists($filePath)) {
            $this->command->error("❌ Archivo no encontrado: {$filePath}");
            return;
        }

        $file = fopen($filePath, 'r');

        // Leer headers (primera línea)
        $headers = fgetcsv($file);

        // Leer cada línea
        while (($row = fgetcsv($file)) !== false) {
            // Combinar headers con valores
            $data = array_combine($headers, $row);

            $nullableFields = ['deleted_at']; 

            foreach ($nullableFields as $field) {
                
                if (isset($data[$field]) && ($data[$field] === 'NULL' || $data[$field] === '')) {
                    $data[$field] = null; 
                }
            }

            // Agregar timestamps si no existen
            if (!isset($data['created_at'])) {
                $data['created_at'] = now();
            }
            if (!isset($data['updated_at'])) {
                $data['updated_at'] = now();
            }

            // Insertar en la BD
            DB::table($tableName)->insert($data);
        }

        fclose($file);

        $this->command->info("✅ Tabla {$tableName} importada");
    }
}
