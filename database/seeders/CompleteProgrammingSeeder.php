<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class CompleteProgrammingSeeder extends Seeder
{
    public function run()
    {
        $this->call(UbigeoSeeder::class);
        $this->truncateTables();
        $this->seedBasicData();
        $zones = $this->seedZones();
        $shifts = $this->seedShifts();
        $vehicles = $this->seedVehicles($zones);
        $employees = $this->seedEmployees();
        $groups = $this->seedEmployeeGroups($zones, $shifts, $vehicles);
        $this->seedConfigGroups($groups, $employees);
        $this->seedSchedulings($groups, $shifts, $vehicles);

        echo "✅ Siembra completada exitosamente!\n";
    }

    private function truncateTables()
    {
        echo "Limpiando tablas...\n";

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = [
            'schedulings',
            'groupdetails',
            'configgroups',
            'employeegroups',
            'contracts',
            'employees',
            'zonevehicle',
            'zoneshifts',
            'vehicles',
            'vehicleimages',
            'brandmodels',
            'brands',
            'vehicletypes',
            'colors',
            'shifts',
            'zones',
            'coords',
            'employeetype'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                echo "✅ Tabla {$table} limpiada\n";
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function seedBasicData()
    {
        echo "Sembrando datos básicos...\n";

        // Tipos de Empleado
        $employeeTypes = [
            ['name' => 'Conductor', 'description' => 'Personal autorizado para conducir vehículos'],
            ['name' => 'Ayudante', 'description' => 'Personal de apoyo en la recolección'],
        ];

        foreach ($employeeTypes as $type) {
            DB::table('employeetype')->insert($type);
        }

        // Tipos de Vehículo
        $vehicleTypes = [
            ['name' => 'Compactador', 'description' => 'Vehículo para compactar residuos'],
            ['name' => 'Volquete', 'description' => 'Vehículo de carga volquete'],
            ['name' => 'Camión Recolector', 'description' => 'Vehículo para recolección de residuos'],
        ];

        foreach ($vehicleTypes as $type) {
            DB::table('vehicletypes')->insert($type);
        }

        // Marcas
        $brands = [
            ['name' => 'Mercedes Benz', 'description' => 'Marca alemana'],
            ['name' => 'Volvo', 'description' => 'Marca sueca'],
            ['name' => 'Hyundai', 'description' => 'Marca coreana'],
            ['name' => 'Toyota', 'description' => 'Marca japonesa'],
        ];

        foreach ($brands as $brand) {
            $brandData = ['name' => $brand['name'], 'description' => $brand['description']];

            if (Schema::hasColumn('brands', 'logo')) {
                $brandData['logo'] = null;
            }

            $brandId = DB::table('brands')->insertGetId($brandData);

            // Modelos para cada marca
            $models = [
                ['name' => $brand['name'] . ' Modelo A', 'code' => 'MOD-A', 'brand_id' => $brandId, 'description' => 'Modelo estándar'],
                ['name' => $brand['name'] . ' Modelo B', 'code' => 'MOD-B', 'brand_id' => $brandId, 'description' => 'Modelo avanzado'],
            ];

            foreach ($models as $model) {
                DB::table('brandmodels')->insert($model);
            }
        }

        // Colores - CON CÓDIGOS RGB
        $colors = [
            ['name' => 'Blanco', 'code' => '#FFFFFF', 'description' => 'Color blanco RGB'],
            ['name' => 'Azul', 'code' => '#0000FF', 'description' => 'Color azul RGB'],
            ['name' => 'Verde', 'code' => '#008000', 'description' => 'Color verde RGB'],
            ['name' => 'Amarillo', 'code' => '#FFFF00', 'description' => 'Color amarillo RGB'],
            ['name' => 'Rojo', 'code' => '#FF0000', 'description' => 'Color rojo RGB'],
            ['name' => 'Negro', 'code' => '#000000', 'description' => 'Color negro RGB'],
            ['name' => 'Gris', 'code' => '#808080', 'description' => 'Color gris RGB'],
        ];

        foreach ($colors as $color) {
            DB::table('colors')->insert($color);
        }
    }

    private function seedZones()
    {
        echo "Creando zonas en Lambayeque/Chiclayo/José Leonardo Ortiz...\n";

        // Buscar específicamente José Leonardo Ortiz en Lambayeque
        $lambayequeDept = DB::table('departments')->where('name', 'like', '%Lambayeque%')->first();
        $chiclayoProv = DB::table('provinces')->where('name', 'like', '%Chiclayo%')->first();

        if (!$lambayequeDept || !$chiclayoProv) {
            echo "No se encontró Lambayeque o Chiclayo en la base de datos\n";
            return [];
        }

        // Buscar específicamente el distrito José Leonardo Ortiz
        $joseLeonardoDistrict = DB::table('districts')
            ->where('name', 'like', '%Jose Leonardo Ortiz%')
            ->where('province_id', $chiclayoProv->id)
            ->first();

        if (!$joseLeonardoDistrict) {
            echo "No se encontró el distrito José Leonardo Ortiz\n";
            return [];
        }

        echo "Distrito encontrado: {$joseLeonardoDistrict->name}\n";

        $zones = [];

        $zoneNames = [
            'Zona Norte - Jose Leonardo Ortiz',
            'Zona Sur - Jose Leonardo Ortiz',
            'Zona Este - Jose Leonardo Ortiz',
            'Zona Oeste - Jose Leonardo Ortiz',
            'Zona Centro - Jose Leonardo Ortiz'
        ];

        foreach ($zoneNames as $index => $zoneName) {
            $zoneId = DB::table('zones')->insertGetId([
                'name' => $zoneName,
                'description' => "Zona de recolección en {$zoneName}",
                'district_id' => $joseLeonardoDistrict->id,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $baseLatitude = -6.7833;
            $baseLongitude = -79.8333;

            $latitude = $baseLatitude + (rand(-50, 50) / 10000);
            $longitude = $baseLongitude + (rand(-50, 50) / 10000);

            if (Schema::hasTable('coords')) {
                DB::table('coords')->insert([
                    'type_coord' => 'zone',
                    'coord_index' => 1,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'zone_id' => $zoneId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                echo "✅ Zona creada: {$zoneName} - Coord: {$latitude}, {$longitude}\n";
            }

            $zones[] = $zoneId;
        }

        return $zones;
    }

    private function getLambayequeCoordinates($districtName)
    {
        // Coordenadas reales de Lambayeque/Chiclayo
        $coordinates = [
            'Chiclayo' => ['latitude' => -6.7760, 'longitude' => -79.8443],
            'José Leonardo Ortiz' => ['latitude' => -6.7833, 'longitude' => -79.8333],
            'Lambayeque' => ['latitude' => -6.7011, 'longitude' => -79.9061],
            'Pimentel' => ['latitude' => -6.8378, 'longitude' => -79.9375],
            'Santa Rosa' => ['latitude' => -6.7667, 'longitude' => -79.8333],
        ];

        // Si no encontramos el distrito exacto, usar Chiclayo como default
        foreach ($coordinates as $name => $coords) {
            if (str_contains($districtName, $name)) {
                return $coords;
            }
        }

        return $coordinates['Chiclayo']; // Default
    }

    private function seedShifts()
    {
        echo "Creando turnos...\n";

        $shifts = [
            ['name' => 'Mañana', 'description' => 'Turno de 6:00 AM a 2:00 PM', 'hour_in' => '06:00:00', 'hour_out' => '14:00:00'],
            ['name' => 'Tarde', 'description' => 'Turno de 2:00 PM a 10:00 PM', 'hour_in' => '14:00:00', 'hour_out' => '22:00:00'],
            ['name' => 'Noche', 'description' => 'Turno de 10:00 PM a 6:00 AM', 'hour_in' => '22:00:00', 'hour_out' => '06:00:00'],
        ];

        $shiftIds = [];
        foreach ($shifts as $shift) {
            $shiftId = DB::table('shifts')->insertGetId($shift);
            $shiftIds[] = $shiftId;
        }

        // Relacionar turnos con zonas
        $zones = DB::table('zones')->get();
        if (Schema::hasTable('zoneshifts') && $zones->isNotEmpty()) {
            foreach ($zones as $zone) {
                foreach ($shiftIds as $shiftId) {
                    DB::table('zoneshifts')->insert([
                        'zone_id' => $zone->id,
                        'shift_id' => $shiftId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        return $shiftIds;
    }

    private function seedVehicles($zones)
    {
        echo "Creando vehículos...\n";

        $brandModels = DB::table('brandmodels')->get();
        $colors = DB::table('colors')->get();
        $types = DB::table('vehicletypes')->get();

        $vehicleIds = [];
        for ($i = 1; $i <= 8; $i++) {
            $vehicleData = [
                'name' => "Vehículo " . $i,
                'plate' => "CLO-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'description' => "Vehículo de recolección " . $i . " - Lambayeque",
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Solo agregar campos si existen
            if (Schema::hasColumn('vehicles', 'code')) {
                $vehicleData['code'] = "V" . str_pad($i, 3, '0', STR_PAD_LEFT);
            }
            if (Schema::hasColumn('vehicles', 'year')) {
                $vehicleData['year'] = 2020 + ($i % 4);
            }
            if (Schema::hasColumn('vehicles', 'load_capacity')) {
                $vehicleData['load_capacity'] = rand(5000, 15000);
            }
            if (Schema::hasColumn('vehicles', 'fuel_capacity')) {
                $vehicleData['fuel_capacity'] = rand(100, 300);
            }
            if (Schema::hasColumn('vehicles', 'compaction_capacity')) {
                $vehicleData['compaction_capacity'] = rand(2000, 8000);
            }
            if (Schema::hasColumn('vehicles', 'people_capacity')) {
                $vehicleData['people_capacity'] = rand(3, 6);
            }
            if (Schema::hasColumn('vehicles', 'brand_id') && $brandModels->isNotEmpty()) {
                $vehicleData['brand_id'] = $brandModels->random()->brand_id;
            }
            if (Schema::hasColumn('vehicles', 'model_id') && $brandModels->isNotEmpty()) {
                $vehicleData['model_id'] = $brandModels->random()->id;
            }
            if (Schema::hasColumn('vehicles', 'type_id') && $types->isNotEmpty()) {
                $vehicleData['type_id'] = $types->random()->id;
            }
            if (Schema::hasColumn('vehicles', 'color_id') && $colors->isNotEmpty()) {
                $vehicleData['color_id'] = $colors->random()->id;
            }

            $vehicleId = DB::table('vehicles')->insertGetId($vehicleData);

            // Asignar vehículo a zona
            if (Schema::hasTable('zonevehicle') && !empty($zones)) {
                $zoneIndex = ($i - 1) % count($zones);
                DB::table('zonevehicle')->insert([
                    'zone_id' => $zones[$zoneIndex],
                    'vehicle_id' => $vehicleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Imagen del vehículo
            if (Schema::hasTable('vehicleimages')) {
                DB::table('vehicleimages')->insert([
                    'vehicle_id' => $vehicleId,
                    'image' => "vehicles/vehicle{$i}.jpg",
                    'profile' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $vehicleIds[] = $vehicleId;
        }

        return $vehicleIds;
    }

    private function seedEmployees()
    {
        echo "Creando empleados...\n";

        $driverTypeId = DB::table('employeetype')->where('name', 'Conductor')->first()->id;
        $assistantTypeId = DB::table('employeetype')->where('name', 'Ayudante')->first()->id;

        $employees = [];

        // Nombres realistas para empleados de Lambayeque
        $nombres = ['Juan', 'Carlos', 'Luis', 'Miguel', 'José', 'Pedro', 'Jorge', 'Ricardo', 'Fernando', 'Roberto'];
        $apellidos = ['García', 'Rodríguez', 'González', 'Fernández', 'López', 'Martínez', 'Sánchez', 'Pérez', 'Gómez', 'Díaz'];

        // Tipos de contrato CORREGIDOS
        $contractTypes = ['Permanente', 'Nombrado', 'Temporal'];

        // Conductores (8)
        for ($i = 1; $i <= 8; $i++) {
            $nombre = $nombres[array_rand($nombres)];
            $apellido = $apellidos[array_rand($apellidos)];

            $employeeId = DB::table('employees')->insertGetId([
                'name' => $nombre,
                'last_name' => $apellido,
                'dni' => "7" . str_pad($i, 7, '0', STR_PAD_LEFT),
                'birthdate' => Carbon::now()->subYears(rand(25, 50)),
                'license' => "LIC" . str_pad($i, 6, '0', STR_PAD_LEFT),
                'address' => "Av. " . $nombre . " " . $apellido . " #" . $i . ", Chiclayo",
                'email' => strtolower($nombre) . "." . strtolower($apellido) . "{$i}@empresa.com",
                'telefono' => "9" . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'password' => bcrypt('password123'),
                'estado' => 'activo',
                'employeetype_id' => $driverTypeId,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('contracts')->insert([
                'employee_id' => $employeeId,
                'contract_type' => $contractTypes[array_rand($contractTypes)], // permanente, nombrado, temporal
                'start_date' => Carbon::now()->subMonths(rand(6, 36)),
                'end_date' => null,
                'salary' => rand(2500, 4000),
                'position_id' => $driverTypeId,
                'department_id' => DB::table('departments')->where('name', 'like', '%Lambayeque%')->first()->id ?? 1,
                'vacation_days_per_year' => 30,
                'probation_period_months' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $employees[] = [
                'id' => $employeeId,
                'type' => 'driver',
                'name' => $nombre . ' ' . $apellido // Guardar nombre completo para debug
            ];
        }

        // Ayudantes (15)
        for ($i = 1; $i <= 15; $i++) {
            $nombre = $nombres[array_rand($nombres)];
            $apellido = $apellidos[array_rand($apellidos)];

            $employeeId = DB::table('employees')->insertGetId([
                'name' => $nombre,
                'last_name' => $apellido,
                'dni' => "8" . str_pad($i, 7, '0', STR_PAD_LEFT),
                'birthdate' => Carbon::now()->subYears(rand(20, 45)),
                'license' => null,
                'address' => "Jr. " . $nombre . " " . $apellido . " #" . ($i + 100) . ", Lambayeque",
                'email' => strtolower($nombre) . "." . strtolower($apellido) . ".ayudante{$i}@empresa.com",
                'telefono' => "9" . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'password' => bcrypt('password123'),
                'estado' => 'activo',
                'employeetype_id' => $assistantTypeId,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            DB::table('contracts')->insert([
                'employee_id' => $employeeId,
                'contract_type' => $contractTypes[array_rand($contractTypes)],
                'start_date' => Carbon::now()->subMonths(rand(3, 24)),
                'end_date' => null,
                'salary' => rand(1500, 2500),
                'position_id' => $assistantTypeId,
                'department_id' => DB::table('departments')->where('name', 'like', '%Lambayeque%')->first()->id ?? 1,
                'vacation_days_per_year' => 30,
                'probation_period_months' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $employees[] = [
                'id' => $employeeId,
                'type' => 'assistant',
                'name' => $nombre . ' ' . $apellido // Guardar nombre completo para debug
            ];
        }

        echo "✅ Empleados creados: " . count($employees) . " (8 conductores, 15 ayudantes)\n";
        return $employees;
    }

    private function seedEmployeeGroups($zones, $shifts, $vehicles)
    {
        echo "Creando grupos de empleados...\n";

        $groupNames = [
            'Grupo Recolector Norte',
            'Grupo Recolector Sur',
            'Grupo Recolector Este',
            'Grupo Recolector Oeste',
            'Grupo Recolector Centro'
        ];

        $groupIds = [];
        foreach ($groupNames as $index => $name) {
            if (isset($zones[$index]) && isset($shifts[$index % count($shifts)]) && isset($vehicles[$index % count($vehicles)])) {
                $groupId = DB::table('employeegroups')->insertGetId([
                    'name' => $name,
                    'days' => 'Lunes,Martes,Miércoles,Jueves,Viernes,Sábado',
                    'status' => 'active',
                    'zone_id' => $zones[$index],
                    'shift_id' => $shifts[$index % count($shifts)],
                    'vehicle_id' => $vehicles[$index % count($vehicles)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $groupIds[] = $groupId;
                echo "✅ Grupo creado: {$name} (Zona: {$zones[$index]}, Turno: {$shifts[$index % count($shifts)]}, Vehículo: {$vehicles[$index % count($vehicles)]})\n";
            }
        }

        return $groupIds;
    }

    private function seedConfigGroups($groups, $employees)
    {
        echo "Configurando grupos con empleados...\n";

        // Obtener información COMPLETA de los empleados con sus nombres
        $employeeIds = array_column($employees, 'id');
        $employeesWithNames = DB::table('employees')
            ->whereIn('id', $employeeIds)
            ->select('id', 'name', 'last_name')
            ->get()
            ->keyBy('id')
            ->toArray();

        // Separar conductores y ayudantes con información completa
        $drivers = [];
        $assistants = [];

        foreach ($employees as $employee) {
            $employeeId = $employee['id'];
            if (isset($employeesWithNames[$employeeId])) {
                $empData = $employeesWithNames[$employeeId];
                $fullName = $empData->name . ' ' . $empData->last_name;

                if ($employee['type'] === 'driver') {
                    $drivers[] = [
                        'id' => $employeeId,
                        'name' => $fullName
                    ];
                } elseif ($employee['type'] === 'assistant') {
                    $assistants[] = [
                        'id' => $employeeId,
                        'name' => $fullName
                    ];
                }
            }
        }

        // Verificar que tenemos suficientes empleados
        if (count($drivers) < count($groups)) {
            // Limitar grupos a la cantidad de conductores disponibles
            $groups = array_slice($groups, 0, count($drivers));
        }

        foreach ($groups as $index => $groupId) {

            // ASIGNAR CONDUCTOR
            if (isset($drivers[$index])) {
                $driver = $drivers[$index];
                DB::table('configgroups')->insert([
                    'employeegroup_id' => $groupId,
                    'employee_id' => $driver['id'],
                    'role' => 'conductor',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                continue; // Saltar este grupo si no hay conductor
            }

            // ASIGNAR AYUDANTES (2-3 por grupo)
            $numAssistants = rand(2, 3);
            $assistantsAssigned = 0;
            $startIndex = $index * 3; // Empezar desde diferente posición para cada grupo

            for ($j = 0; $j < $numAssistants; $j++) {
                $assistantIndex = $startIndex + $j;
                if (isset($assistants[$assistantIndex])) {
                    $assistant = $assistants[$assistantIndex];
                    DB::table('configgroups')->insert([
                        'employeegroup_id' => $groupId,
                        'employee_id' => $assistant['id'],
                        'role' => 'ayudante',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $assistantsAssigned++;
                } else {
                    break;
                }
            }

            // MOSTRAR RESUMEN DEL GRUPO CONFIGURADO
            $groupConfig = DB::table('configgroups')
                ->join('employees', 'configgroups.employee_id', '=', 'employees.id')
                ->where('configgroups.employeegroup_id', $groupId)
                ->select('employees.name', 'employees.last_name', 'configgroups.role')
                ->get();

            foreach ($groupConfig as $member) {
                $roleIcon = $member->role === 'conductor' ? '🚗' : '👷';
                echo "      {$roleIcon} {$member->name} {$member->last_name} - {$member->role}\n";
            }
        }

        echo "\n✅ Configuración de grupos COMPLETADA\n";

        // ESTADÍSTICAS FINALES
        $totalAssigned = DB::table('configgroups')->count();
        $driversAssigned = DB::table('configgroups')->where('role', 'conductor')->count();
        $assistantsAssigned = DB::table('configgroups')->where('role', 'ayudante')->count();
    }

    private function seedSchedulings($groups, $shifts, $vehicles)
    {
        echo "Creando programaciones de prueba...\n";

        // Obtener información de grupos para crear programaciones coherentes
        $employeeGroups = DB::table('employeegroups')
            ->select('id', 'zone_id', 'shift_id', 'vehicle_id')
            ->whereIn('id', $groups)
            ->get();

        // Crear programaciones para los próximos 5 días
        for ($i = 1; $i <= 5; $i++) {
            $date = Carbon::now()->addDays($i);

            foreach ($employeeGroups as $group) {
                // Solo crear programación algunos días (70% probabilidad)
                if (rand(1, 10) <= 7) {
                    $schedulingData = [
                        'group_id' => $group->id,
                        'shift_id' => $group->shift_id, // Usar el turno del grupo
                        'vehicle_id' => $group->vehicle_id, // Usar el vehículo del grupo
                        'date' => $date->format('Y-m-d'),
                        'status' => 'programado',
                        'notes' => "Programación automática - Grupo #{$group->id}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    DB::table('schedulings')->insert($schedulingData);
                    echo "✅ Programación: Grupo {$group->id} - {$date->format('Y-m-d')}\n";
                }
            }
        }

        echo "✅ " . DB::table('schedulings')->count() . " programaciones creadas\n";
    }
}
