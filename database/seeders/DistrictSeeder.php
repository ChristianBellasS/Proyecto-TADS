<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $districts = [
            // Distritos de Lima Metropolitana
            ['name' => 'Lima', 'code' => 'LIM01', 'province_id' => 1, 'latitude' => -12.046374, 'longitude' => -77.042793],
            ['name' => 'Ancón', 'code' => 'LIM02', 'province_id' => 1, 'latitude' => -11.773, 'longitude' => -77.178],
            ['name' => 'Ate', 'code' => 'LIM03', 'province_id' => 1, 'latitude' => -12.033, 'longitude' => -76.917],
            ['name' => 'Barranco', 'code' => 'LIM04', 'province_id' => 1, 'latitude' => -12.144, 'longitude' => -77.022],
            ['name' => 'Breña', 'code' => 'LIM05', 'province_id' => 1, 'latitude' => -12.056, 'longitude' => -77.052],
            ['name' => 'Carabayllo', 'code' => 'LIM06', 'province_id' => 1, 'latitude' => -11.861, 'longitude' => -77.045],
            ['name' => 'Chaclacayo', 'code' => 'LIM07', 'province_id' => 1, 'latitude' => -11.989, 'longitude' => -76.767],
            ['name' => 'Chorrillos', 'code' => 'LIM08', 'province_id' => 1, 'latitude' => -12.189, 'longitude' => -77.018],
            ['name' => 'Cieneguilla', 'code' => 'LIM09', 'province_id' => 1, 'latitude' => -12.117, 'longitude' => -76.800],
            ['name' => 'Comas', 'code' => 'LIM10', 'province_id' => 1, 'latitude' => -11.940, 'longitude' => -77.062],
            ['name' => 'El Agustino', 'code' => 'LIM11', 'province_id' => 1, 'latitude' => -12.039, 'longitude' => -76.999],
            ['name' => 'Independencia', 'code' => 'LIM12', 'province_id' => 1, 'latitude' => -11.990, 'longitude' => -77.052],
            ['name' => 'Jesús María', 'code' => 'LIM13', 'province_id' => 1, 'latitude' => -12.077, 'longitude' => -77.052],
            ['name' => 'La Molina', 'code' => 'LIM14', 'province_id' => 1, 'latitude' => -12.088, 'longitude' => -76.931],
            ['name' => 'La Victoria', 'code' => 'LIM15', 'province_id' => 1, 'latitude' => -12.072, 'longitude' => -77.028],
            ['name' => 'Lince', 'code' => 'LIM16', 'province_id' => 1, 'latitude' => -12.087, 'longitude' => -77.033],
            ['name' => 'Los Olivos', 'code' => 'LIM17', 'province_id' => 1, 'latitude' => -11.987, 'longitude' => -77.068],
            ['name' => 'Lurigancho', 'code' => 'LIM18', 'province_id' => 1, 'latitude' => -11.939, 'longitude' => -76.700],
            ['name' => 'Lurín', 'code' => 'LIM19', 'province_id' => 1, 'latitude' => -12.277, 'longitude' => -76.875],
            ['name' => 'Magdalena del Mar', 'code' => 'LIM20', 'province_id' => 1, 'latitude' => -12.095, 'longitude' => -77.070],
            ['name' => 'Miraflores', 'code' => 'LIM21', 'province_id' => 1, 'latitude' => -12.121, 'longitude' => -77.034],
            ['name' => 'Pachacámac', 'code' => 'LIM22', 'province_id' => 1, 'latitude' => -12.233, 'longitude' => -76.850],
            ['name' => 'Pucusana', 'code' => 'LIM23', 'province_id' => 1, 'latitude' => -12.483, 'longitude' => -76.783],
            ['name' => 'Pueblo Libre', 'code' => 'LIM24', 'province_id' => 1, 'latitude' => -12.072, 'longitude' => -77.062],
            ['name' => 'Puente Piedra', 'code' => 'LIM25', 'province_id' => 1, 'latitude' => -11.844, 'longitude' => -77.084],
            ['name' => 'Punta Hermosa', 'code' => 'LIM26', 'province_id' => 1, 'latitude' => -12.333, 'longitude' => -76.817],
            ['name' => 'Punta Negra', 'code' => 'LIM27', 'province_id' => 1, 'latitude' => -12.367, 'longitude' => -76.800],
            ['name' => 'Rímac', 'code' => 'LIM28', 'province_id' => 1, 'latitude' => -12.028, 'longitude' => -77.038],
            ['name' => 'San Bartolo', 'code' => 'LIM29', 'province_id' => 1, 'latitude' => -12.383, 'longitude' => -76.783],
            ['name' => 'San Borja', 'code' => 'LIM30', 'province_id' => 1, 'latitude' => -12.100, 'longitude' => -77.008],
            ['name' => 'San Isidro', 'code' => 'LIM31', 'province_id' => 1, 'latitude' => -12.099, 'longitude' => -77.035],
            ['name' => 'San Juan de Lurigancho', 'code' => 'LIM32', 'province_id' => 1, 'latitude' => -11.980, 'longitude' => -76.999],
            ['name' => 'San Juan de Miraflores', 'code' => 'LIM33', 'province_id' => 1, 'latitude' => -12.156, 'longitude' => -76.966],
            ['name' => 'San Luis', 'code' => 'LIM34', 'province_id' => 1, 'latitude' => -12.077, 'longitude' => -77.008],
            ['name' => 'San Martín de Porres', 'code' => 'LIM35', 'province_id' => 1, 'latitude' => -12.035, 'longitude' => -77.085],
            ['name' => 'San Miguel', 'code' => 'LIM36', 'province_id' => 1, 'latitude' => -12.083, 'longitude' => -77.088],
            ['name' => 'Santa Anita', 'code' => 'LIM37', 'province_id' => 1, 'latitude' => -12.046, 'longitude' => -76.974],
            ['name' => 'Santa María del Mar', 'code' => 'LIM38', 'province_id' => 1, 'latitude' => -12.433, 'longitude' => -76.817],
            ['name' => 'Santa Rosa', 'code' => 'LIM39', 'province_id' => 1, 'latitude' => -12.050, 'longitude' => -77.108],
            ['name' => 'Santiago de Surco', 'code' => 'LIM40', 'province_id' => 1, 'latitude' => -12.144, 'longitude' => -76.991],
            ['name' => 'Surquillo', 'code' => 'LIM41', 'province_id' => 1, 'latitude' => -12.117, 'longitude' => -77.007],
            ['name' => 'Villa El Salvador', 'code' => 'LIM42', 'province_id' => 1, 'latitude' => -12.216, 'longitude' => -76.941],
            ['name' => 'Villa María del Triunfo', 'code' => 'LIM43', 'province_id' => 1, 'latitude' => -12.166, 'longitude' => -76.933],
        ];

        foreach ($districts as $district) {
            // Obtener province_id basado en el código de la provincia
            $province = DB::table('provinces')->where('code', substr($district['code'], 0, 3))->first();
            if ($province) {
                DB::table('districts')->updateOrInsert(
                    ['code' => $district['code']],
                    [
                        'name' => $district['name'],
                        'province_id' => $province->id,
                        'latitude' => $district['latitude'],
                        'longitude' => $district['longitude']
                    ]
                );
            }
        }


    }
}
