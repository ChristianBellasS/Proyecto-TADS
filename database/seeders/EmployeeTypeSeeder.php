<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Conductor',
                'description' => 'Empleado responsable de conducir los vehículos asignados y garantizar el transporte seguro.'
            ],
            [
                'name' => 'Ayudante',
                'description' => 'Asiste al conductor u otros empleados en las tareas de carga, descarga y apoyo operativo.'
            ],
        ];

        foreach ($types as $type) {
            DB::table('employeetype')->updateOrInsert(
                ['name' => $type['name']],
                [
                    'description' => $type['description'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }

        $this->command->info('Tipos de empleados insertados correctamente.');
    }
}
