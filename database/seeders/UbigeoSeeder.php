<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;


class UbigeoSeeder extends Seeder
{
    public function run(): void
    {
        $client = new Client();
        $url = 'https://api.apis.net.pe/v1/ubigeo?all=true';

        try {
            $response = $client->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            if (!is_array($data)) {
                $this->command->error('⚠️ Error: formato inesperado de la API.');
                return;
            }

            foreach ($data as $depCode => $departamento) {
                $depName = ucwords(strtolower($departamento['nombre']));
                $departmentId = DB::table('departments')->updateOrInsert(
                    ['code' => str_pad($depCode, 2, '0', STR_PAD_LEFT)],
                    ['name' => $depName]
                );

                $department = DB::table('departments')->where('code', str_pad($depCode, 2, '0', STR_PAD_LEFT))->first();

                if (!isset($departamento['provincias'])) continue;

                foreach ($departamento['provincias'] as $provCode => $provincia) {
                    $provName = ucwords(strtolower($provincia['nombre']));
                    $provinceCode = str_pad($depCode, 2, '0', STR_PAD_LEFT) . str_pad($provCode, 2, '0', STR_PAD_LEFT);

                    DB::table('provinces')->updateOrInsert(
                        ['code' => $provinceCode],
                        [
                            'name' => $provName,
                            'department_id' => $department->id
                        ]
                    );

                    $province = DB::table('provinces')->where('code', $provinceCode)->first();

                    if (!isset($provincia['distritos'])) continue;

                    

                    foreach ($provincia['distritos'] as $distCode => $distName) {
                        $districtCode = $provinceCode . str_pad($distCode, 2, '0', STR_PAD_LEFT);

                        DB::table('districts')->updateOrInsert(
                            ['code' => $districtCode],
                            [
                                'name' => ucwords(strtolower($distName)),
                                'province_id' => $province->id,
                                'latitude' => null,
                                'longitude' => null
                            ]
                        );
                    }
                    
                    //Nuevo 
                    /*
                    foreach ($provincia['distritos'] as $distCode => $distName) {
                        $districtCode = $provinceCode . str_pad($distCode, 2, '0', STR_PAD_LEFT);

                        // 🔹 NUEVO: buscar coordenadas desde OpenStreetMap (Nominatim)
                        $lat = null;
                        $lng = null;

                        try {
                            $query = $distName . ', ' . $provName . ', ' . $depName . ', Perú';
                            $geoResponse = $client->get('https://nominatim.openstreetmap.org/search', [
                                'query' => [
                                    'q' => $query,
                                    'format' => 'json',
                                    'limit' => 1
                                ],
                                'headers' => [
                                    'User-Agent' => 'UbigeoSeeder/1.0 (contacto@tudominio.com)'
                                ]
                            ]);

                            $geoData = json_decode($geoResponse->getBody()->getContents(), true);

                            if (!empty($geoData) && isset($geoData[0]['lat'], $geoData[0]['lon'])) {
                                $lat = $geoData[0]['lat'];
                                $lng = $geoData[0]['lon'];
                            }

                            // 🔹 pausa de 1 segundo por respeto a la API gratuita
                            sleep(1);

                        } catch (\Exception $e) {
                            $this->command->warn("⚠️ No se pudo obtener coordenadas de $distName: " . $e->getMessage());
                        }

                        DB::table('districts')->updateOrInsert(
                            ['code' => $districtCode],
                            [
                                'name' => ucwords(strtolower($distName)),
                                'province_id' => $province->id,
                                'latitude' => $lat,    // 🔹 reemplaza null por $lat
                                'longitude' => $lng    // 🔹 reemplaza null por $lng
                            ]
                        );
                    }
                    */
                    // FFin de nuevo
                }
            }

            $this->command->info('✅ Ubigeo del Perú insertado correctamente.');

        } catch (\Exception $e) {
            $this->command->error('❌ Error al obtener datos de ubigeo: ' . $e->getMessage());
        }
    }
}
