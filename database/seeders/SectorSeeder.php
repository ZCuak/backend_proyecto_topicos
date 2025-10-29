<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Sector;

class SectorSeeder extends Seeder
{
    public function run()
    {
        $url = 'https://geoespacial.inei.gob.pe/geoserver/wfs';
        $params = [
            'service' => 'WFS',
            'version' => '1.0.0',
            'request' => 'GetFeature',
            'typeName' => 'Interoperabilidad:ig_manzana',
            'outputFormat' => 'json',
            'CQL_FILTER' => "ubigeo='140101'" // ✅ minúsculas, correcto
        ];

        $response = Http::withoutVerifying()->timeout(90)->get($url, $params);
        
        if (! $response->ok()) {
            $this->command->error("❌ Error al obtener datos: " . $response->status());
            return;
        }

        $data = $response->json();

        if (! isset($data['features'])) {
            $this->command->error("⚠️ La respuesta no contiene 'features'");
            file_put_contents(storage_path('logs/sector_debug.json'), $response->body());
            $this->command->warn("Se guardó la respuesta completa en storage/logs/sector_debug.json para revisión.");
            return;
        }

        $count = 0;

        foreach ($data['features'] as $feature) {
            $props = $feature['properties'] ?? [];

            $name = $props['manzana'] ?? $props['idmanzana'] ?? 'Sin nombre';
            $ubigeo = $props['ubigeo'] ?? null;

            if (! $ubigeo) {
                $this->command->warn("⚠️ Sin ubigeo, se omite el registro.");
                continue;
            }

            $districtId = \App\Models\District::where('code', $ubigeo)->value('id');
            if (! $districtId) {
                $this->command->warn("⚠️ No existe distrito para ubigeo: {$ubigeo}");
                continue;
            }

            Sector::create([
                'name' => $name,
                'area' => $props['shape_area'] ?? null,
                'description' => 'Manzana censal importada desde INEI',
                'district_id' => 1255,
            ]);

            $count++;
        }

        $this->command->info("✅ Se importaron {$count} sectores (manzanas censales) correctamente.");
    }
}
