<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $provinces = [
            // Lima
            ['name' => 'Lima', 'code' => 'LIM', 'department_id' => 15],
            ['name' => 'Huaura', 'code' => 'HUA', 'department_id' => 15],
            ['name' => 'Barranca', 'code' => 'BAR', 'department_id' => 15],
            ['name' => 'Cajatambo', 'code' => 'CAJ', 'department_id' => 15],
            ['name' => 'Canta', 'code' => 'CAN', 'department_id' => 15],
            ['name' => 'Cañete', 'code' => 'CNT', 'department_id' => 15],
            ['name' => 'Huaral', 'code' => 'HUR', 'department_id' => 15],
            ['name' => 'Huarochirí', 'code' => 'HCH', 'department_id' => 15],
            ['name' => 'Oyón', 'code' => 'OYO', 'department_id' => 15],
            ['name' => 'Yauyos', 'code' => 'YAU', 'department_id' => 15],
            
            // Arequipa
            ['name' => 'Arequipa', 'code' => 'ARE', 'department_id' => 4],
            ['name' => 'Camaná', 'code' => 'CAM', 'department_id' => 4],
            ['name' => 'Caravelí', 'code' => 'CRV', 'department_id' => 4],
            ['name' => 'Castilla', 'code' => 'CAS', 'department_id' => 4],
            ['name' => 'Caylloma', 'code' => 'CAY', 'department_id' => 4],
            ['name' => 'Condesuyos', 'code' => 'CON', 'department_id' => 4],
            ['name' => 'Islay', 'code' => 'ISL', 'department_id' => 4],
            ['name' => 'La Uniòn', 'code' => 'UNI', 'department_id' => 4],
            
            // Cusco
            ['name' => 'Cusco', 'code' => 'CUS', 'department_id' => 8],
            ['name' => 'Acomayo', 'code' => 'ACO', 'department_id' => 8],
            ['name' => 'Anta', 'code' => 'ANT', 'department_id' => 8],
            ['name' => 'Calca', 'code' => 'CAL', 'department_id' => 8],
            ['name' => 'Canas', 'code' => 'CAN', 'department_id' => 8],
            ['name' => 'Canchis', 'code' => 'CNC', 'department_id' => 8],
            ['name' => 'Chumbivilcas', 'code' => 'CHU', 'department_id' => 8],
            ['name' => 'Espinar', 'code' => 'ESP', 'department_id' => 8],
            ['name' => 'La Convención', 'code' => 'CON', 'department_id' => 8],
            ['name' => 'Paruro', 'code' => 'PAR', 'department_id' => 8],
            ['name' => 'Paucartambo', 'code' => 'PAU', 'department_id' => 8],
            ['name' => 'Quispicanchi', 'code' => 'QUI', 'department_id' => 8],
            ['name' => 'Urubamba', 'code' => 'URU', 'department_id' => 8],
        ];

        // foreach ($provinces as $province) {
        //     // Obtener department_id basado en el código del departamento
        //     $department = DB::table('departments')->where('code', $province['department_code'])->first();
        //     if ($department) {
        //         DB::table('provinces')->updateOrInsert(
        //             ['code' => $province['code']],
        //             [
        //                 'name' => $province['name'],
        //                 'department_id' => $department->id
        //             ]
        //         );
        //     }
        // }

        foreach ($provinces as $province) {
            DB::table('provinces')->updateOrInsert(
                ['code' => $province['code']],
                [
                    'name' => $province['name'],
                    'department_id' => $province['department_id']
                ]
            );
        }


    }
}
