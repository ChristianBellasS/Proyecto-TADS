<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class CompletarDatos extends Seeder
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
        $groups = $this->seedEmployeeGroups($zones, $shifts, $vehicles, $employees);
        $this->seedConfigGroups($groups, $employees);
        $this->seedSchedulings($groups);

        echo "✅ Siembra completada exitosamente!\n";
        $this->showFinalStats();
    }

    private function truncateTables()
    {
        echo "Limpiando tablas...\n";

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = [
            'groupdetails',
            'schedulings',
            'configgroups',
            'employeegroups',
            'contracts',
            'attendances',
            'vacations',
            'employees',
            'zone_vehicle',
            'zone_shift',
            'vehicleimages',
            'vehicles',
            'brandmodels',
            'brands',
            'vehicletypes',
            'colors',
            'shifts',
            'zone_coords',
            'zones',
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
        echo "\n📦 Sembrando datos básicos...\n";

        // Tipos de Empleado
        $employeeTypes = [
            ['name' => 'Conductor', 'description' => 'Personal autorizado para conducir vehículos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ayudante', 'description' => 'Personal de apoyo en la recolección', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($employeeTypes as $type) {
            DB::table('employeetype')->insert($type);
        }
        echo "✅ Tipos de empleado creados\n";

        // Tipos de Vehículo
        $vehicleTypes = [
            ['name' => 'Compactador', 'description' => 'Vehículo para compactar residuos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Volquete', 'description' => 'Vehículo de carga volquete', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Camión Recolector', 'description' => 'Vehículo para recolección de residuos', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($vehicleTypes as $type) {
            DB::table('vehicletypes')->insert($type);
        }
        echo "✅ Tipos de vehículo creados\n";

        // Marcas y Modelos
        $brandsData = [
            ['name' => 'Mercedes Benz', 'description' => 'Marca alemana de vehículos de alta calidad'],
            ['name' => 'Volvo', 'description' => 'Marca sueca especializada en vehículos pesados'],
            ['name' => 'Hyundai', 'description' => 'Marca coreana de vehículos comerciales'],
            ['name' => 'Toyota', 'description' => 'Marca japonesa reconocida mundialmente'],
        ];

        foreach ($brandsData as $brand) {
            $brandData = [
                'name' => $brand['name'],
                'description' => $brand['description'],
                'logo' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $brandId = DB::table('brands')->insertGetId($brandData);

            // Modelos para cada marca
            $models = [
                ['name' => $brand['name'] . ' Atego', 'code' => 'ATEGO-' . substr($brand['name'], 0, 3), 'description' => 'Modelo resistente para trabajo pesado'],
                ['name' => $brand['name'] . ' Axor', 'code' => 'AXOR-' . substr($brand['name'], 0, 3), 'description' => 'Modelo avanzado con alta capacidad'],
            ];

            foreach ($models as $model) {
                DB::table('brandmodels')->insert([
                    'name' => $model['name'],
                    'code' => $model['code'],
                    'description' => $model['description'],
                    'brand_id' => $brandId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        echo "✅ Marcas y modelos creados\n";

        // Colores con códigos RGB
        $colors = [
            ['name' => 'Blanco', 'code' => '#FFFFFF', 'description' => 'Color blanco perlado'],
            ['name' => 'Azul', 'code' => '#0000FF', 'description' => 'Color azul corporativo'],
            ['name' => 'Verde', 'code' => '#008000', 'description' => 'Color verde institucional'],
            ['name' => 'Amarillo', 'code' => '#FFFF00', 'description' => 'Color amarillo alta visibilidad'],
            ['name' => 'Rojo', 'code' => '#FF0000', 'description' => 'Color rojo señalización'],
            ['name' => 'Negro', 'code' => '#000000', 'description' => 'Color negro mate'],
            ['name' => 'Gris', 'code' => '#808080', 'description' => 'Color gris metálico'],
        ];

        foreach ($colors as $color) {
            DB::table('colors')->insert([
                'name' => $color['name'],
                'code' => $color['code'],
                'description' => $color['description'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        echo "✅ Colores creados\n";
    }

    private function seedZones()
    {
        echo "\n🗺️  Creando zonas en José Leonardo Ortiz...\n";

        $joseLeonardoDistrict = DB::table('districts')
            ->join('provinces', 'districts.province_id', '=', 'provinces.id')
            ->where('districts.name', 'like', '%Jose Leonardo Ortiz%')
            ->orWhere('districts.name', 'like', '%José Leonardo Ortiz%')
            ->select('districts.*')
            ->first();

        if (!$joseLeonardoDistrict) {
            echo "⚠️  No se encontró José Leonardo Ortiz, buscando Chiclayo...\n";
            $joseLeonardoDistrict = DB::table('districts')
                ->join('provinces', 'districts.province_id', '=', 'provinces.id')
                ->where('districts.name', 'like', '%Chiclayo%')
                ->select('districts.*')
                ->first();
        }

        if (!$joseLeonardoDistrict) {
            echo "❌ No se encontró ningún distrito válido\n";
            return [];
        }

        echo "✅ Distrito encontrado: {$joseLeonardoDistrict->name}\n";

        $zonesData = [
            ['name' => 'Zona Norte', 'desc' => 'Sector norte de José Leonardo Ortiz', 'lat_offset' => 0.005, 'lng_offset' => 0.005],
            ['name' => 'Zona Sur', 'desc' => 'Sector sur de José Leonardo Ortiz', 'lat_offset' => -0.005, 'lng_offset' => -0.005],
            ['name' => 'Zona Este', 'desc' => 'Sector este de José Leonardo Ortiz', 'lat_offset' => 0.003, 'lng_offset' => 0.007],
            ['name' => 'Zona Oeste', 'desc' => 'Sector oeste de José Leonardo Ortiz', 'lat_offset' => -0.003, 'lng_offset' => -0.007],
            ['name' => 'Zona Centro', 'desc' => 'Sector centro de José Leonardo Ortiz', 'lat_offset' => 0.000, 'lng_offset' => 0.000],
        ];

        $zones = [];
        $baseLatitude = -6.7833;
        $baseLongitude = -79.8333;

        foreach ($zonesData as $index => $zoneData) {
            $zoneId = DB::table('zones')->insertGetId([
                'name' => $zoneData['name'],
                'description' => $zoneData['desc'],
                'district_id' => $joseLeonardoDistrict->id,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear coordenadas para la zona
            $latitude = $baseLatitude + $zoneData['lat_offset'];
            $longitude = $baseLongitude + $zoneData['lng_offset'];

            DB::table('zone_coords')->insert([
                'zone_id' => $zoneId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $zones[] = $zoneId;
            echo "  ✅ {$zoneData['name']} creada (ID: {$zoneId})\n";
        }

        return $zones;
    }

    private function seedShifts()
    {
        echo "\n⏰ Creando turnos...\n";

        $shifts = [
            ['name' => 'Mañana', 'description' => 'Turno matutino', 'hour_in' => '06:00:00', 'hour_out' => '14:00:00'],
            ['name' => 'Tarde', 'description' => 'Turno vespertino', 'hour_in' => '14:00:00', 'hour_out' => '22:00:00'],
            ['name' => 'Noche', 'description' => 'Turno nocturno', 'hour_in' => '22:00:00', 'hour_out' => '06:00:00'],
        ];

        $shiftIds = [];
        foreach ($shifts as $shift) {
            $shiftId = DB::table('shifts')->insertGetId([
                'name' => $shift['name'],
                'description' => $shift['description'],
                'hour_in' => $shift['hour_in'],
                'hour_out' => $shift['hour_out'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $shiftIds[] = $shiftId;
            echo "  ✅ Turno {$shift['name']} creado (ID: {$shiftId})\n";
        }

        // Relacionar turnos con zonas
        $zones = DB::table('zones')->pluck('id');
        foreach ($zones as $zoneId) {
            foreach ($shiftIds as $shiftId) {
                DB::table('zone_shift')->insert([
                    'zone_id' => $zoneId,
                    'shift_id' => $shiftId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        echo "  ✅ Relaciones zona-turno creadas\n";

        return $shiftIds;
    }

    private function seedVehicles($zones)
    {
        echo "\n🚛 Creando vehículos...\n";

        $brands = DB::table('brands')->get();
        $colors = DB::table('colors')->get();
        $types = DB::table('vehicletypes')->get();

        if ($brands->isEmpty() || $colors->isEmpty() || $types->isEmpty()) {
            echo "❌ Faltan datos básicos para crear vehículos\n";
            return [];
        }

        $vehicleIds = [];
        for ($i = 1; $i <= 8; $i++) {
            $brand = $brands->random();
            $model = DB::table('brandmodels')->where('brand_id', $brand->id)->inRandomOrder()->first();
            
            $vehicleData = [
                'name' => "Vehículo Recolector " . str_pad($i, 2, '0', STR_PAD_LEFT),
                'code' => "VEH-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'plate' => "CLO-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'year' => 2018 + ($i % 6),
                'load_capacity' => rand(8000, 15000),
                'fuel_capacity' => rand(150, 300),
                'compaction_capacity' => rand(3000, 8000),
                'people_capacity' => rand(3, 5),
                'description' => "Vehículo de recolección municipal - Lambayeque",
                'status' => 1,
                'brand_id' => $brand->id,
                'model_id' => $model->id,
                'type_id' => $types->random()->id,
                'color_id' => $colors->random()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $vehicleId = DB::table('vehicles')->insertGetId($vehicleData);

            // Asignar vehículo a zona
            if (!empty($zones)) {
                $zoneIndex = ($i - 1) % count($zones);
                DB::table('zone_vehicle')->insert([
                    'zone_id' => $zones[$zoneIndex],
                    'vehicle_id' => $vehicleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Imagen del vehículo
            DB::table('vehicleimages')->insert([
                'vehicle_id' => $vehicleId,
                'image' => "vehicles/vehicle_" . str_pad($i, 2, '0', STR_PAD_LEFT) . ".jpg",
                'profile' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $vehicleIds[] = $vehicleId;
            echo "  ✅ Vehículo {$vehicleData['plate']} creado (ID: {$vehicleId})\n";
        }

        return $vehicleIds;
    }

    private function seedEmployees()
    {
        echo "\n👥 Creando empleados...\n";

        $driverType = DB::table('employeetype')->where('name', 'Conductor')->first();
        $assistantType = DB::table('employeetype')->where('name', 'Ayudante')->first();

        if (!$driverType || !$assistantType) {
            echo "❌ Tipos de empleado no encontrados\n";
            return [];
        }

        $departmentId = DB::table('departments')->where('name', 'like', '%Lambayeque%')->first()->id ?? 1;

        $nombres = ['Juan', 'Carlos', 'Luis', 'Miguel', 'José', 'Pedro', 'Jorge', 'Ricardo', 'Fernando', 'Roberto', 'Daniel', 'Andrés'];
        $apellidos = ['García', 'Rodríguez', 'González', 'Fernández', 'López', 'Martínez', 'Sánchez', 'Pérez', 'Gómez', 'Díaz', 'Torres', 'Ramírez'];
        $contractTypes = ['Permanente', 'Nombrado', 'Temporal'];

        $employees = [];

        // Crear 8 Conductores
        echo "  Creando conductores...\n";
        for ($i = 1; $i <= 8; $i++) {
            $nombre = $nombres[array_rand($nombres)];
            $apellido = $apellidos[array_rand($apellidos)];

            $employeeId = DB::table('employees')->insertGetId([
                'name' => $nombre,
                'last_name' => $apellido,
                'dni' => "7" . str_pad($i, 7, '0', STR_PAD_LEFT),
                'birthdate' => Carbon::now()->subYears(rand(28, 55)),
                'license' => "A-" . str_pad($i, 6, '0', STR_PAD_LEFT),
                'address' => "Av. Principal #" . ($i * 100) . ", José Leonardo Ortiz",
                'email' => strtolower($nombre . "." . $apellido . $i) . "@conductor.com",
                'telefono' => "9" . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'password' => bcrypt('password123'),
                'estado' => 'activo',
                'employeetype_id' => $driverType->id,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('contracts')->insert([
                'employee_id' => $employeeId,
                'contract_type' => $contractTypes[array_rand($contractTypes)],
                'start_date' => Carbon::now()->subMonths(rand(12, 48)),
                'end_date' => null,
                'salary' => rand(3000, 4500),
                'position_id' => $driverType->id,
                'department_id' => $departmentId,
                'vacation_days_per_year' => 30,
                'probation_period_months' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $employees[] = ['id' => $employeeId, 'type' => 'driver', 'name' => $nombre . ' ' . $apellido];
            echo "    ✅ Conductor: {$nombre} {$apellido} (DNI: 7" . str_pad($i, 7, '0', STR_PAD_LEFT) . ")\n";
        }

        // Crear 20 Ayudantes (más que suficiente para 8 grupos con 2-3 ayudantes cada uno)
        echo "  Creando ayudantes...\n";
        for ($i = 1; $i <= 20; $i++) {
            $nombre = $nombres[array_rand($nombres)];
            $apellido = $apellidos[array_rand($apellidos)];

            $employeeId = DB::table('employees')->insertGetId([
                'name' => $nombre,
                'last_name' => $apellido,
                'dni' => "8" . str_pad($i, 7, '0', STR_PAD_LEFT),
                'birthdate' => Carbon::now()->subYears(rand(20, 50)),
                'license' => null,
                'address' => "Jr. Secundaria #" . ($i * 50) . ", José Leonardo Ortiz",
                'email' => strtolower($nombre . "." . $apellido . $i) . "@ayudante.com",
                'telefono' => "9" . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'password' => bcrypt('password123'),
                'estado' => 'activo',
                'employeetype_id' => $assistantType->id,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('contracts')->insert([
                'employee_id' => $employeeId,
                'contract_type' => $contractTypes[array_rand($contractTypes)],
                'start_date' => Carbon::now()->subMonths(rand(6, 36)),
                'end_date' => null,
                'salary' => rand(1800, 2800),
                'position_id' => $assistantType->id,
                'department_id' => $departmentId,
                'vacation_days_per_year' => 30,
                'probation_period_months' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $employees[] = ['id' => $employeeId, 'type' => 'assistant', 'name' => $nombre . ' ' . $apellido];
            
            if ($i % 5 == 0) {
                echo "    ✅ {$i} ayudantes creados...\n";
            }
        }

        echo "  ✅ Total empleados: " . count($employees) . " (8 conductores, 20 ayudantes)\n";
        return $employees;
    }

    private function seedEmployeeGroups($zones, $shifts, $vehicles, $employees)
    {
        echo "\n👥 Creando grupos de empleados...\n";

        $drivers = array_filter($employees, fn($e) => $e['type'] === 'driver');
        $assistants = array_filter($employees, fn($e) => $e['type'] === 'assistant');

        $drivers = array_values($drivers);
        $assistants = array_values($assistants);

        $groupIds = [];
        $numGroups = min(count($zones), count($shifts), count($vehicles), count($drivers));

        for ($i = 0; $i < $numGroups; $i++) {
            $zoneId = $zones[$i % count($zones)];
            $shiftId = $shifts[$i % count($shifts)];
            $vehicleId = $vehicles[$i % count($vehicles)];
            $driverId = $drivers[$i]['id'];

            // Seleccionar 2-3 ayudantes para este grupo
            $numAssistants = rand(2, 3);
            $assistantIds = [];
            $startIdx = $i * 3;
            
            for ($j = 0; $j < $numAssistants && ($startIdx + $j) < count($assistants); $j++) {
                $assistantIds[] = $assistants[$startIdx + $j]['id'];
            }

            $groupData = [
                'name' => "Grupo Recolector " . ($i + 1),
                'days' => 'Lunes,Martes,Miércoles,Jueves,Viernes,Sábado',
                'status' => 'active',
                'zone_id' => $zoneId,
                'shift_id' => $shiftId,
                'vehicle_id' => $vehicleId,
                'driver_id' => $driverId,
                'assistant1_id' => $assistantIds[0] ?? null,
                'assistant2_id' => $assistantIds[1] ?? null,
                'assistant3_id' => $assistantIds[2] ?? null,
                'assistant4_id' => null,
                'assistant5_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $groupId = DB::table('employeegroups')->insertGetId($groupData);
            $groupIds[] = $groupId;

            echo "  ✅ Grupo " . ($i + 1) . " creado (Zona: {$zoneId}, Conductor: {$drivers[$i]['name']})\n";
        }

        return $groupIds;
    }

    private function seedConfigGroups($groups, $employees)
    {
        echo "\n⚙️  Configurando grupos con empleados...\n";

        foreach ($groups as $groupId) {
            $group = DB::table('employeegroups')->find($groupId);
            
            // Insertar conductor
            if ($group->driver_id) {
                DB::table('configgroups')->insert([
                    'employeegroup_id' => $groupId,
                    'employee_id' => $group->driver_id,
                    'role' => 'conductor',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Insertar ayudantes
            $assistantFields = ['assistant1_id', 'assistant2_id', 'assistant3_id', 'assistant4_id', 'assistant5_id'];
            foreach ($assistantFields as $field) {
                if ($group->$field) {
                    DB::table('configgroups')->insert([
                        'employeegroup_id' => $groupId,
                        'employee_id' => $group->$field,
                        'role' => 'ayudante',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $memberCount = DB::table('configgroups')->where('employeegroup_id', $groupId)->count();
            echo "  ✅ Grupo {$groupId} configurado con {$memberCount} miembros\n";
        }
    }

    private function seedSchedulings($groups)
    {
        echo "\n📅 Creando programaciones...\n";

        $employeeGroups = DB::table('employeegroups')
            ->whereIn('id', $groups)
            ->get();

        $schedulingCount = 0;

        // Crear programaciones para los próximos 7 días
        for ($day = 0; $day < 7; $day++) {
            $date = Carbon::now()->addDays($day);

            foreach ($employeeGroups as $group) {
                // 80% de probabilidad de trabajar cada día
                if (rand(1, 100) <= 80) {
                    $schedulingId = DB::table('schedulings')->insertGetId([
                        'group_id' => $group->id,
                        'shift_id' => $group->shift_id,
                        'vehicle_id' => $group->vehicle_id,
                        'date' => $date->format('Y-m-d'),
                        'status' => 'programado',
                        'notes' => "Programación automática - {$date->format('d/m/Y')}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Crear detalles del grupo para esta programación
                    $configMembers = DB::table('configgroups')
                        ->where('employeegroup_id', $group->id)
                        ->get();

                    foreach ($configMembers as $member) {
                        DB::table('groupdetails')->insert([
                            'employee_id' => $member->employee_id,
                            'scheduling_id' => $schedulingId,
                            'role' => $member->role,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $schedulingCount++;
                }
            }
        }

        echo "  ✅ {$schedulingCount} programaciones creadas\n";
    }

    private function showFinalStats()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 ESTADÍSTICAS FINALES\n";
        echo str_repeat("=", 50) . "\n";

        $stats = [
            'Zonas' => DB::table('zones')->count(),
            'Turnos' => DB::table('shifts')->count(),
            'Vehículos' => DB::table('vehicles')->count(),
            'Conductores' => DB::table('employees')->join('employeetype', 'employees.employeetype_id', '=', 'employeetype.id')->where('employeetype.name', 'Conductor')->count(),
            'Ayudantes' => DB::table('employees')->join('employeetype', 'employees.employeetype_id', '=', 'employeetype.id')->where('employeetype.name', 'Ayudante')->count(),
            'Grupos' => DB::table('employeegroups')->count(),
            'Configuraciones' => DB::table('configgroups')->count(),
            'Programaciones' => DB::table('schedulings')->count(),
            'Detalles Programación' => DB::table('groupdetails')->count(),
        ];

        foreach ($stats as $label => $count) {
            echo sprintf("%-25s: %d\n", $label, $count);
        }

        echo str_repeat("=", 50) . "\n";
    }
}