<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $departments = [
            ['name' => 'Amazonas', 'code' => 'AMA'],
            ['name' => 'Áncash', 'code' => 'ANC'],
            ['name' => 'Apurímac', 'code' => 'APU'],
            ['name' => 'Arequipa', 'code' => 'ARE'],
            ['name' => 'Ayacucho', 'code' => 'AYA'],
            ['name' => 'Cajamarca', 'code' => 'CAJ'],
            ['name' => 'Callao', 'code' => 'CAL'],
            ['name' => 'Cusco', 'code' => 'CUS'],
            ['name' => 'Huancavelica', 'code' => 'HUV'],
            ['name' => 'Huánuco', 'code' => 'HUC'],
            ['name' => 'Ica', 'code' => 'ICA'],
            ['name' => 'Junín', 'code' => 'JUN'],
            ['name' => 'La Libertad', 'code' => 'LAL'],
            ['name' => 'Lambayeque', 'code' => 'LAM'],
            ['name' => 'Lima', 'code' => 'LIM'],
            ['name' => 'Loreto', 'code' => 'LOR'],
            ['name' => 'Madre de Dios', 'code' => 'MDD'],
            ['name' => 'Moquegua', 'code' => 'MOQ'],
            ['name' => 'Pasco', 'code' => 'PAS'],
            ['name' => 'Piura', 'code' => 'PIU'],
            ['name' => 'Puno', 'code' => 'PUN'],
            ['name' => 'San Martín', 'code' => 'SAM'],
            ['name' => 'Tacna', 'code' => 'TAC'],
            ['name' => 'Tumbes', 'code' => 'TUM'],
            ['name' => 'Ucayali', 'code' => 'UCA'],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                ['code' => $department['code']],
                ['name' => $department['name']]
            );
        }


    }
}
