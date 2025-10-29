<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Scheduling;
use App\Models\Zone;
use App\Models\Vehicle;
use App\Models\Shift;
use App\Models\Employee;
use App\Models\GroupDetail;
use App\Models\EmployeeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SchedulingController extends Controller
{
    /**
     * Visualización diaria de programaciones (admin/schedulings)
     */
    public function daily(Request $request)
    {
        if ($request->ajax()) {
            return $this->dailyData($request);
        }

        return view('admin.scheduling.daily');
    }
    // Obtener datos diarios de programaciones
    public function dailyData(Request $request)
    {
        try {
            $query = Scheduling::with([
                'shift',
                'vehicle',
                'groupDetails.employee'
            ]);

            // SOLO aplicar filtro si el usuario proporciona fechas específicas
            if ($request->start_date && $request->end_date) {
                // Usuario proporcionó fechas específicas
                $query->whereBetween('date', [$request->start_date, $request->end_date])->orderBy('date', 'asc');;
            } else {
                // Si no hay filtros, mostrar todos los registros ordenados por fecha
                $query->orderBy('date', 'asc');

            }

            $schedulings = $query->get();

            $data = $schedulings->map(function ($scheduling) {
                // Obtener información del conductor
                $driver = $scheduling->groupDetails
                    ->where('role', 'conductor')
                    ->first();

                $driverName = $driver && $driver->employee
                    ? "{$driver->employee->name} {$driver->employee->last_name}"
                    : 'N/A';

                // Obtener información de ayudantes
                $assistants = $scheduling->groupDetails
                    ->where('role', 'ayudante')
                    ->map(function ($detail) {
                        return $detail->employee
                            ? "{$detail->employee->name} {$detail->employee->last_name}"
                            : null;
                    })
                    ->filter()
                    ->implode(', ');

                return [
                    'id' => $scheduling->id,
                    'date' => $scheduling->date->format('Y-m-d'),
                    'status' => $scheduling->status,
                    'zone_name' => 'Zona General', // Puedes ajustar esto según tu estructura
                    'shift_name' => $scheduling->shift->name ?? 'N/A',
                    'vehicle_name' => $scheduling->vehicle->name ?? 'N/A',
                    'vehicle_plate' => $scheduling->vehicle->plate ?? 'N/A',
                    'group_name' => "{$driverName}" . ($assistants ? " + {$assistants}" : ''),
                    'driver_name' => $driverName,
                    'assistants' => $assistants
                ];
            });

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error al cargar los datos: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Lista de programaciones para el CRUD (admin/scheduling)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $schedulings = Scheduling::with([
                'shift',
                'vehicle',
                'groupDetails.employee'
            ])
                ->orderBy('date', 'desc')
                ->get();

            // Asegurar que siempre tenemos una colección
            $schedulings = $schedulings ?: collect();

            $data = $schedulings->map(function ($scheduling) {
                // Obtener conductor - asegurar que groupDetails existe
                $groupDetails = $scheduling->groupDetails ?: collect();

                $driver = $groupDetails
                    ->where('role', 'conductor')
                    ->first();

                $driverInfo = $driver && $driver->employee ? [
                    'name' => $driver->employee->name,
                    'last_name' => $driver->employee->last_name
                ] : null;

                // Obtener ayudantes - asegurar que tenemos una colección para mapear
                $assistants = $groupDetails
                    ->where('role', 'ayudante')
                    ->map(function ($detail) {
                        return $detail->employee ? [
                            'name' => $detail->employee->name,
                            'last_name' => $detail->employee->last_name
                        ] : null;
                    })
                    ->filter()
                    ->values()
                    ->toArray();

                return [
                    'id' => $scheduling->id,
                    'date' => $scheduling->date ? $scheduling->date->format('Y-m-d') : 'N/A',
                    'vehicle_info' => $scheduling->vehicle ? [
                        'name' => $scheduling->vehicle->name,
                        'plate' => $scheduling->vehicle->plate
                    ] : null,
                    'shift_info' => $scheduling->shift ? [
                        'name' => $scheduling->shift->name,
                        'hour_in' => $scheduling->shift->hour_in,
                        'hour_out' => $scheduling->shift->hour_out
                    ] : null,
                    'driver_info' => $driverInfo,
                    'assistants_info' => $assistants,
                    'status' => $scheduling->status ?? 'N/A',
                    'created_at' => $scheduling->created_at ? $scheduling->created_at->format('Y-m-d H:i:s') : 'N/A',
                ];
            });

            return response()->json($data);
        }

        return view('admin.scheduling.index');
    }

    /**
     * Vista para ver todas las programaciones con validación completa
     */
    public function all(Request $request)
    {
        if ($request->ajax()) {
            return $this->getAllSchedulingsData($request);
        }

        return view('admin.scheduling.all');
    }

    /**
     * Datos para la vista de todas las programaciones
     */
    public function getAllSchedulingsData(Request $request)
    {
        $schedulings = Scheduling::with([
            'shift',
            'vehicle',
            'groupDetails.employee.contracts',
            'groupDetails.employee.vacations'
        ])->get();

        $data = $schedulings->map(function ($scheduling) {
            // Información del conductor
            $driver = $scheduling->groupDetails->where('role', 'conductor')->first();
            $driverInfo = $driver && $driver->employee ? [
                'id' => $driver->employee->id,
                'name' => $driver->employee->full_name,
                'dni' => $driver->employee->dni,
                'has_active_contract' => $driver->employee->isActiveContract($scheduling->date),
                'on_vacation' => $driver->employee->hasVacation($scheduling->date)
            ] : null;

            // Información de ayudantes
            $assistants = $scheduling->groupDetails->where('role', 'ayudante')
                ->map(function ($detail) use ($scheduling) {
                    return $detail->employee ? [
                        'id' => $detail->employee->id,
                        'name' => $detail->employee->full_name,
                        'dni' => $detail->employee->dni,
                        'has_active_contract' => $detail->employee->isActiveContract($scheduling->date),
                        'on_vacation' => $detail->employee->hasVacation($scheduling->date)
                    ] : null;
                })
                ->filter()
                ->values();

            // Estado de validación
            $validationStatus = $this->getSchedulingValidationStatus($scheduling);

            return [
                'id' => $scheduling->id,
                'date' => $scheduling->date ? $scheduling->date->format('Y-m-d') : 'N/A',
                'shift_name' => $scheduling->shift->name ?? 'N/A',
                'vehicle_info' => $scheduling->vehicle ? [
                    'name' => $scheduling->vehicle->name,
                    'plate' => $scheduling->vehicle->plate,
                    'status' => $scheduling->vehicle->status
                ] : null,
                'driver_info' => $driverInfo,
                'assistants_info' => $assistants,
                'status' => $scheduling->status ?? 'N/A',
                'validation_status' => $validationStatus,
                'can_start_route' => $validationStatus === 'valid' && $scheduling->status === 'programado',
                'created_at' => $scheduling->created_at ? $scheduling->created_at->format('Y-m-d H:i:s') : 'N/A',
            ];
        });

        return response()->json($data);
    }

    public function create()
    {
        $zones = Zone::where('status', true)->get();

        if (request()->ajax()) {
            return view('admin.scheduling.templates.form', compact('zones'));
        }
        return view('admin.scheduling.create', compact('zones'));
    }

    public function getZoneData(Zone $zone)
    {
        $data = $zone->getProgrammingData();

        $employees = Employee::where('status', true)
            ->get()
            ->filter(function ($employee) {
                return $employee->isAvailableForDate(now());
            });

        $data['employees'] = $employees;

        return response()->json($data);
    }

    /**
     * Búsqueda de grupos de personal para Select2
     */
    public function searchEmployeeGroups(Request $request)
    {
        try {
            $term = $request->get('search', '');
            $page = $request->get('page', 1);
            $perPage = 10;

            $query = EmployeeGroup::with(['zone', 'shift', 'vehicle', 'driver'])
                ->where('status', 'active');

            if (!empty($term)) {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'LIKE', "%{$term}%")
                        ->orWhereHas('zone', function ($z) use ($term) {
                            $z->where('name', 'LIKE', "%{$term}%");
                        })
                        ->orWhereHas('shift', function ($s) use ($term) {
                            $s->where('name', 'LIKE', "%{$term}%");
                        })
                        ->orWhereHas('driver', function ($d) use ($term) {
                            $d->where('name', 'LIKE', "%{$term}%")
                                ->orWhere('last_name', 'LIKE', "%{$term}%")
                                ->orWhere('dni', 'LIKE', "%{$term}%");
                        });
                });
            }

            $groups = $query->paginate($perPage, ['*'], 'page', $page);

            // Formatear los resultados correctamente para Select2
            $results = collect($groups->items())->map(function ($group) {
                $zoneName = $group->zone ? $group->zone->name : 'N/A';
                $shiftName = $group->shift ? $group->shift->name : 'N/A';
                $vehiclePlate = $group->vehicle ? $group->vehicle->plate : 'N/A';
                $driverName = $group->driver ? "{$group->driver->name} {$group->driver->last_name}" : 'Sin conductor';

                return [
                    'id' => $group->id,
                    'text' => $group->name,
                    'name' => $group->name,
                    'zone_name' => $zoneName,
                    'shift_name' => $shiftName,
                    'vehicle_plate' => $vehiclePlate,
                    'driver_name' => $driverName
                ];
            });

            return response()->json([
                'results' => $results,
                'pagination' => ['more' => $groups->hasMorePages()]
            ]);
        } catch (\Exception $e) {
            // \Log::error('Error en searchEmployeeGroups: ' . $e->getMessage());
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
                'error' => 'Error en la búsqueda'
            ], 500);
        }
    }

    /**
     * Obtener datos completos de un grupo
     */
    public function getGroupData($groupId)
    {
        try {
            $group = EmployeeGroup::with([
                'zone',
                'shift',
                'vehicle',
                'driver',
                'assistant1',
                'assistant2',
                'assistant3',
                'assistant4',
                'assistant5'
            ])->findOrFail($groupId);

            // Obtener conductor
            $driver = $group->driver ? [
                'id' => $group->driver->id,
                'name' => $group->driver->name,
                'last_name' => $group->driver->last_name,
                'dni' => $group->driver->dni,
                'names' => "{$group->driver->name} {$group->driver->last_name}",
                'role' => 'conductor'
            ] : null;

            // Obtener todos los ayudantes
            $assistants = collect();
            for ($i = 1; $i <= 5; $i++) {
                $assistant = $group->{"assistant$i"};
                if ($assistant) {
                    $assistants->push([
                        'id' => $assistant->id,
                        'name' => $assistant->name,
                        'last_name' => $assistant->last_name,
                        'dni' => $assistant->dni,
                        'names' => "{$assistant->name} {$assistant->last_name}",
                        'role' => 'ayudante'
                    ]);
                }
            }

            // Agrega los dias
            $workDays = $group->days ? explode(',', $group->days) : [];

            return response()->json([
                'success' => true,
                'group' => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'zone_id' => $group->zone_id,
                    'zone_name' => $group->zone->name ?? 'N/A',
                    'shift_id' => $group->shift_id,
                    'shift_name' => $group->shift->name ?? 'N/A',
                    'shift_hours' => $group->shift ? "{$group->shift->hour_in} - {$group->shift->hour_out}" : 'N/A',
                    'vehicle_id' => $group->vehicle_id,
                    'vehicle_name' => $group->vehicle->name ?? 'N/A',
                    'vehicle_plate' => $group->vehicle->plate ?? 'N/A',
                ],
                'driver' => $driver,
                'assistants' => $assistants,
                'work_days' => $workDays,
                'total_employees' => $assistants->count() + ($driver ? 1 : 0)
            ]);
        } catch (\Exception $e) {
            // \Log::error('Error en getGroupData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos del grupo'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_group_id' => 'required|exists:employeegroups,id',
            'zone_id' => 'required|exists:zones,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'shift_id' => 'required|exists:shifts,id',
            'driver_id' => 'required|exists:employees,id',
            'assistant_ids' => 'required|array|min:1',
            'assistant_ids.*' => 'exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'work_days' => 'required|array|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $validated['start_date'], 'America/Lima')
                ->startOfDay()
                ->setTimezone('UTC');
            $endDate = Carbon::createFromFormat('Y-m-d', $validated['end_date'], 'America/Lima')
                ->startOfDay()
                ->setTimezone('UTC');
        } catch (\Exception $e) {
            try {
                $startDate = Carbon::createFromFormat('d/m/Y', $validated['start_date'], 'America/Lima')
                    ->startOfDay()
                    ->setTimezone('UTC');
                $endDate = Carbon::createFromFormat('d/m/Y', $validated['end_date'], 'America/Lima')
                    ->startOfDay()
                    ->setTimezone('UTC');
            } catch (\Exception $e) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato de fecha inválido: ' . $e->getMessage()
                    ], 422);
                }
                return back()->withErrors(['error' => 'Formato de fecha inválido']);
            }
        }

        $validationResult = $this->validateScheduling(array_merge($validated, [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]));

        if (!$validationResult['is_valid']) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validationResult['errors'],
                    'suggestions' => $validationResult['suggestions']
                ], 422);
            }
            return back()->withErrors($validationResult['errors']);
        }

        $employeeGroup = EmployeeGroup::find($validated['employee_group_id']);

        try {
            DB::transaction(function () use ($validated, $employeeGroup, $startDate, $endDate) {
                $dayMap = [
                    'Lunes' => 'Monday',
                    'Martes' => 'Tuesday',
                    'Miércoles' => 'Wednesday',
                    'Jueves' => 'Thursday',
                    'Viernes' => 'Friday',
                    'Sábado' => 'Saturday',
                    'Domingo' => 'Sunday'
                ];

                $createdDates = [];

                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    $dayNameEnglish = $date->format('l');

                    $isWorkDay = collect($validated['work_days'])->contains(function ($spanishDay) use ($dayNameEnglish, $dayMap) {
                        return $dayMap[$spanishDay] === $dayNameEnglish;
                    });

                    if ($isWorkDay) {
                        $createdDates[] = $date->format('Y-m-d');

                        $scheduling = Scheduling::create([
                            'group_id' => $validated['employee_group_id'],
                            'shift_id' => $validated['shift_id'],
                            'vehicle_id' => $validated['vehicle_id'],
                            'date' => $date->format('Y-m-d'),
                            'status' => 'programado',
                            'notes' => $validated['notes'] ?? "Programación recurrente del grupo: " . ($employeeGroup->name ?? 'N/A')
                        ]);

                        GroupDetail::create([
                            'scheduling_id' => $scheduling->id,
                            'employee_id' => $validated['driver_id'],
                            'role' => 'conductor'
                        ]);

                        foreach ($validated['assistant_ids'] as $assistantId) {
                            GroupDetail::create([
                                'scheduling_id' => $scheduling->id,
                                'employee_id' => $assistantId,
                                'role' => 'ayudante'
                            ]);
                        }
                    }
                }
            });

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Programación creada exitosamente.'
                ]);
            }

            return redirect()->route('admin.scheduling.index')->with('success', 'Programación creada exitosamente.');
        } catch (\Exception $e) {

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la programación: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Error al crear la programación: ' . $e->getMessage()]);
        }
    }
/*
    public function edit($id)
    {
        $scheduling = Scheduling::with(['shift', 'vehicle', 'employees'])->findOrFail($id);

        $zones = Zone::where('status', true)->get();
        $vehicles = Vehicle::where('status', 'active')->get();
        $shifts = Shift::all();

        return view('admin.scheduling.edit', compact('scheduling', 'zones', 'vehicles', 'shifts'));
    }
*/
    public function edit($id)
    {
        try {
            $scheduling = Scheduling::with(['shift', 'vehicle', 'group.zone', 'groupDetails.employee'])->findOrFail($id);

            $zones = Zone::where('status', true)->get();
            $vehicles = Vehicle::where('status', 'active')->get();
            $shifts = Shift::all();

            // Renderizar la vista parcial (solo el formulario, sin layout)
            $view = view('admin.scheduling.templates.form', compact('scheduling', 'zones', 'vehicles', 'shifts'))->render();

            return response()->json([
                'success' => true,
                'html' => $view
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el formulario de edición: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validación completa de la programación con sugerencias
     */
    private function validateScheduling($data)
    {
        $errors = [];
        $suggestions = [];

        // 1. Validar empleados (contratos y vacaciones)
        $employeeValidation = $this->validateEmployees($data);
        $errors = array_merge($errors, $employeeValidation['errors']);
        $suggestions = array_merge($suggestions, $employeeValidation['suggestions']);

        // 2. Validar vehículo activo
        $vehicleValidation = $this->validateVehicle($data['vehicle_id']);
        $errors = array_merge($errors, $vehicleValidation['errors']);
        $suggestions = array_merge($suggestions, $vehicleValidation['suggestions']);

        // 3. Validar duplicados
        $duplicateErrors = $this->validateDuplicates($data);
        $errors = array_merge($errors, $duplicateErrors);

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'suggestions' => $suggestions
        ];
    }

    /**
     * Validación 1: Empleados con contrato activo y sin vacaciones
     */
    private function validateEmployees($data)
    {
        $errors = [];
        $suggestions = [];

        try {
            // Intentar con formato Y-m-d primero
            $startDate = Carbon::createFromFormat('Y-m-d', $data['start_date'], 'America/Lima')
                ->startOfDay()
                ->setTimezone('UTC');
            $endDate = Carbon::createFromFormat('Y-m-d', $data['end_date'], 'America/Lima')
                ->startOfDay()
                ->setTimezone('UTC');
        } catch (\Exception $e) {
            // Si falla, intentar con otro formato
            try {
                $startDate = Carbon::parse($data['start_date'], 'America/Lima')
                    ->startOfDay()
                    ->setTimezone('UTC');
                $endDate = Carbon::parse($data['end_date'], 'America/Lima')
                    ->startOfDay()
                    ->setTimezone('UTC');
            } catch (\Exception $e) {
                $errors[] = "Formato de fecha inválido: " . $e->getMessage();
                return [
                    'errors' => $errors,
                    'suggestions' => $suggestions
                ];
            }
        }

        $workDays = $data['work_days'];
        $allEmployeeIds = array_merge([$data['driver_id']], $data['assistant_ids']);

        $dayMap = [
            'Lunes' => 'Monday',
            'Martes' => 'Tuesday',
            'Miércoles' => 'Wednesday',
            'Jueves' => 'Thursday',
            'Viernes' => 'Friday',
            'Sábado' => 'Saturday',
            'Domingo' => 'Sunday'
        ];

        foreach ($allEmployeeIds as $employeeId) {
            $employee = Employee::find($employeeId);

            if (!$employee) {
                $errors[] = "Empleado ID {$employeeId} no encontrado";
                continue;
            }

            $current = $startDate->copy();
            $end = $endDate->copy();

            // Validar cada día del rango que sea día laboral
            while ($current <= $end) {
                $dayNameEnglish = $current->format('l');

                // Verificar si es día laboral
                $isWorkDay = collect($workDays)->contains(function ($spanishDay) use ($dayNameEnglish, $dayMap) {
                    return $dayMap[$spanishDay] === $dayNameEnglish;
                });

                if ($isWorkDay) {
                    // Convertir la fecha a America/Lima para la validación del empleado
                    $dateForValidation = $current->copy()->setTimezone('America/Lima');

                    // Usar el método canBeScheduled del modelo Employee
                    $validation = $employee->canBeScheduled($dateForValidation);

                    if (!$validation['can_be_scheduled']) {
                        // Mostrar la fecha en formato local para el usuario
                        $displayDate = $dateForValidation->format('d/m/Y');
                        $errors[] = "{$employee->full_name} - {$displayDate}: {$validation['error']}";

                        // Buscar reemplazo
                        $replacement = $this->findReplacementEmployee($employee, $dateForValidation, $data['zone_id']);
                        if ($replacement) {
                            $suggestions[] = "Sugerencia: Reemplazar a {$employee->full_name} con {$replacement->full_name} el {$displayDate}";
                        }
                    }
                }

                $current->addDay();
            }
        }

        return [
            'errors' => $errors,
            'suggestions' => $suggestions
        ];
    }

    /**
     * Validación 2: Vehículo activo con sugerencias de reemplazo
     */
    private function validateVehicle($vehicleId)
    {
        $errors = [];
        $suggestions = [];
        $vehicle = Vehicle::find($vehicleId);

        if (!$vehicle) {
            $errors[] = "Vehículo no encontrado";
        } elseif (!$vehicle->status) {
            $errors[] = "El vehículo {$vehicle->name} ({$vehicle->plate}) no está activo";

            // Buscar vehículos alternativos
            $alternativeVehicles = Vehicle::where('status', 'active')
                ->where('id', '!=', $vehicleId)
                ->get();

            if ($alternativeVehicles->count() > 0) {
                $vehicleList = $alternativeVehicles->map(function ($v) {
                    return "{$v->name} ({$v->plate})";
                })->implode(', ');

                $suggestions[] = "Vehículos alternativos disponibles: " . $vehicleList;
            } else {
                $suggestions[] = "No hay vehículos alternativos disponibles en esta zona";
            }
        }

        return [
            'errors' => $errors,
            'suggestions' => $suggestions
        ];
    }

    /**
     * Buscar empleado de reemplazo
     */
    private function findReplacementEmployee($originalEmployee, $date, $zoneId)
    {
        // Determinar el tipo de empleado original
        $employeeType = $originalEmployee->employeeType;

        if (!$employeeType) {
            return null;
        }

        return Employee::where('id', '!=', $originalEmployee->id)
            ->where('estado', 'activo')
            ->whereHas('employeeType', function ($q) use ($employeeType) {
                $q->where('name', $employeeType->name);
            })
            ->whereDoesntHave('vacations', function ($query) use ($date) {
                $query->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date)
                    ->where('status', 'approved');
            })
            ->whereHas('contracts', function ($query) use ($date) {
                $query->where('is_active', true)
                    ->where('start_date', '<=', $date)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $date);
                    });
            })
            ->first();
    }

    /**
     * Validación 3: Sin duplicados (mismo turno, vehículo, conductor y ayudantes)
     */
    private function validateDuplicates($data)
    {
        $errors = [];

        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $data['start_date'], 'America/Lima')
                ->startOfDay()
                ->setTimezone('UTC');
            $endDate = Carbon::createFromFormat('Y-m-d', $data['end_date'], 'America/Lima')
                ->startOfDay()
                ->setTimezone('UTC');
        } catch (\Exception $e) {
            $startDate = Carbon::parse($data['start_date'], 'America/Lima')
                ->startOfDay()
                ->setTimezone('UTC');
            $endDate = Carbon::parse($data['end_date'], 'America/Lima')
                ->startOfDay()
                ->setTimezone('UTC');
        }

        $dayMap = [
            'Lunes' => 'Monday',
            'Martes' => 'Tuesday',
            'Miércoles' => 'Wednesday',
            'Jueves' => 'Thursday',
            'Viernes' => 'Friday',
            'Sábado' => 'Saturday',
            'Domingo' => 'Sunday'
        ];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayNameEnglish = $date->format('l');

            $isWorkDay = collect($data['work_days'])->contains(function ($spanishDay) use ($dayNameEnglish, $dayMap) {
                return $dayMap[$spanishDay] === $dayNameEnglish;
            });

            if ($isWorkDay) {
                $dateString = $date->format('Y-m-d');

                // Validar duplicado de turno y vehículo
                $existingScheduling = Scheduling::where('date', $dateString)
                    ->where('vehicle_id', $data['vehicle_id'])
                    ->exists();

                if ($existingScheduling) {
                    $displayDate = $date->copy()->setTimezone('America/Lima')->format('d/m/Y');
                    $errors[] = "Ya existe una programación para el {$displayDate} con el mismo turno y vehículo";
                }

                // Validar duplicado de conductor
                $existingDriver = Scheduling::where('date', $dateString)
                    ->whereHas('groupDetails', function ($query) use ($data) {
                        $query->where('employee_id', $data['driver_id'])
                            ->where('role', 'conductor');
                    })
                    ->exists();

                if ($existingDriver) {
                    $driver = Employee::find($data['driver_id']);
                    $displayDate = $date->copy()->setTimezone('America/Lima')->format('d/m/Y');
                    $errors[] = "El conductor {$driver->full_name} ya está asignado el {$displayDate} en el mismo turno";
                }

                // Validar duplicado de ayudantes
                foreach ($data['assistant_ids'] as $assistantId) {
                    $existingAssistant = Scheduling::where('date', $dateString)
                        ->whereHas('groupDetails', function ($query) use ($assistantId) {
                            $query->where('employee_id', $assistantId)
                                ->where('role', 'ayudante');
                        })
                        ->exists();

                    if ($existingAssistant) {
                        $assistant = Employee::find($assistantId);
                        $displayDate = $date->copy()->setTimezone('America/Lima')->format('d/m/Y');
                        $errors[] = "El ayudante {$assistant->full_name} ya está asignado el {$displayDate} en el mismo turno";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Obtener estado de validación de una programación
     */
    private function getSchedulingValidationStatus($scheduling)
    {
        // Validar vehículo
        if (!$scheduling->vehicle || $scheduling->vehicle->status !== 'active') {
            return 'invalid_vehicle';
        }

        // Validar empleados
        foreach ($scheduling->groupDetails as $detail) {
            if (!$detail->employee) {
                return 'invalid_employee';
            }

            if (!$detail->employee->isActiveContract($scheduling->date)) {
                return 'invalid_contract';
            }

            if ($detail->employee->hasVacation($scheduling->date)) {
                return 'on_vacation';
            }
        }

        return 'valid';
    }

    /**
     * Validación en tiempo real para el frontend (muestra en verde/rojo)
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'work_days' => 'required|array',
            'vehicle_id' => 'required|exists:vehicles,id',
            'zone_id' => 'required|exists:zones,id'
        ]);

        $testData = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'work_days' => $request->work_days,
            'driver_id' => $request->employee_ids[0],
            'assistant_ids' => array_slice($request->employee_ids, 1),
            'vehicle_id' => $request->vehicle_id,
            'zone_id' => $request->zone_id,
            'shift_id' => $request->shift_id
        ];

        $validationResult = $this->validateScheduling($testData);

        return response()->json([
            'available' => $validationResult['is_valid'],
            'message' => $validationResult['is_valid'] ? 'Todo está correcto. Puede guardar la programación.' : 'Hay errores que corregir',
            'errors' => $validationResult['errors'],
            'suggestions' => $validationResult['suggestions'],
            'is_valid' => $validationResult['is_valid']
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Programaciones actualizadas correctamente'
        ]);
    }

    /**
     * Iniciar recorrido
     */
    public function startRoute(Request $request, $schedulingId)
    {
        try {
            $scheduling = Scheduling::with(['groupDetails.employee', 'vehicle'])->findOrFail($schedulingId);

            // Validar que pueda iniciar
            $validationErrors = $this->validateRouteStart($scheduling);

            if (!empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'errors' => $validationErrors
                ], 422);
            }

            // Cambiar estado
            $scheduling->update(['status' => 'iniciado']);

            return response()->json([
                'success' => true,
                'message' => 'Recorrido iniciado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar el recorrido'
            ], 500);
        }
    }

    /**
     * Validar inicio de recorrido
     */
    private function validateRouteStart($scheduling)
    {
        $errors = [];

        // Validar vehículo activo
        if (!$scheduling->vehicle || $scheduling->vehicle->status !== 'active') {
            $errors[] = 'El vehículo no está activo';
        }

        // Validar asistencia del personal
        foreach ($scheduling->groupDetails as $detail) {
            if (!$detail->employee) {
                $errors[] = "Empleado no encontrado en la programación";
                continue;
            }

            $validation = $detail->employee->canBeScheduled($scheduling->date);
            if (!$validation['can_be_scheduled']) {
                $errors[] = "{$detail->employee->full_name}: {$validation['error']}";
            }
        }

        return $errors;
    }

    public function destroy($id)
    {
        try {
            $scheduling = Scheduling::findOrFail($id);

            // Eliminar detalles del grupo primero
            GroupDetail::where('scheduling_id', $id)->delete();

            // Eliminar la programación
            $scheduling->delete();

            return response()->json([
                'success' => true,
                'message' => 'Programación eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la programación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Búsqueda de ayudantes disponibles para Select2
     */
    public function searchAvailableAssistants(Request $request)
    {
        try {
            $term = $request->get('search', '');
            $date = $request->get('date', now()->format('Y-m-d'));
            $excludeEmployees = $request->get('exclude_employees', []);
            $page = $request->get('page', 1);
            $perPage = 10;

            $excludeArray = [];

            if (is_array($excludeEmployees)) {
                foreach ($excludeEmployees as $item) {
                    if (is_numeric($item)) {
                        $excludeArray[] = (int)$item;
                    } elseif (is_string($item) && is_numeric(trim($item))) {
                        $excludeArray[] = (int)trim($item);
                    }
                }
            } elseif (is_string($excludeEmployees) && !empty($excludeEmployees)) {
                // Si viene como string separado por comas
                $parts = explode(',', $excludeEmployees);
                foreach ($parts as $part) {
                    if (is_numeric(trim($part))) {
                        $excludeArray[] = (int)trim($part);
                    }
                }
            }

            $excludeArray = array_unique(array_filter($excludeArray));

            $query = Employee::where('estado', 'activo')
                ->whereHas('employeeType', function ($q) {
                    $q->where('name', 'Ayudante');
                });

            // Excluir empleados ya seleccionados
            if (!empty($excludeArray)) {
                $query->whereNotIn('id', $excludeArray);
            }

            // Buscar por término
            if (!empty($term)) {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'LIKE', "%{$term}%")
                        ->orWhere('last_name', 'LIKE', "%{$term}%");
                });
            }

            // Ordenar y paginar
            $query->orderBy('name')->orderBy('last_name');
            $employees = $query->paginate($perPage, ['*'], 'page', $page);

            $results = collect($employees->items())->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'text' => "{$employee->name} {$employee->last_name}",
                    'name' => $employee->name,
                    'last_name' => $employee->last_name,
                ];
            });

            return response()->json([
                'results' => $results,
                'pagination' => ['more' => $employees->hasMorePages()]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Búsqueda de conductores disponibles para Select2
     */
    public function searchAvailableDrivers(Request $request)
    {
        try {
            $term = $request->get('search', '');
            $date = $request->get('date', now()->format('Y-m-d'));
            $excludeEmployees = $request->get('exclude_employees', []);
            $page = $request->get('page', 1);
            $perPage = 10;

            $excludeArray = [];

            if (is_array($excludeEmployees)) {
                foreach ($excludeEmployees as $item) {
                    if (is_numeric($item)) {
                        $excludeArray[] = (int)$item;
                    } elseif (is_string($item) && is_numeric(trim($item))) {
                        $excludeArray[] = (int)trim($item);
                    }
                }
            } elseif (is_string($excludeEmployees) && !empty($excludeEmployees)) {
                // Si viene como string separado por comas
                $parts = explode(',', $excludeEmployees);
                foreach ($parts as $part) {
                    if (is_numeric(trim($part))) {
                        $excludeArray[] = (int)trim($part);
                    }
                }
            }

            $excludeArray = array_unique(array_filter($excludeArray));

            // Filtrar SOLO por empleados que son conductores (por nombre del tipo)
            $query = Employee::where('estado', 'activo')
                ->whereHas('employeeType', function ($q) {
                    $q->where('name', 'Conductor');
                });

            // Excluir empleados ya seleccionados
            if (!empty($excludeArray)) {
                $query->whereNotIn('id', $excludeArray);
            }

            // Buscar por término
            if (!empty($term)) {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'LIKE', "%{$term}%")
                        ->orWhere('last_name', 'LIKE', "%{$term}%");
                });
            }

            // Ordenar y paginar
            $query->orderBy('name')->orderBy('last_name');
            $employees = $query->paginate($perPage, ['*'], 'page', $page);

            $results = collect($employees->items())->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'text' => "{$employee->name} {$employee->last_name}",
                    'name' => $employee->name,
                    'last_name' => $employee->last_name,
                ];
            });

            return response()->json([
                'results' => $results,
                'pagination' => ['more' => $employees->hasMorePages()]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de un grupo para mostrar en modal
     */
    public function getGroupDetails($schedulingId)
    {
        try {
            // Primero obtener la programación con sus relaciones
            $scheduling = Scheduling::with([
                'group.zone',
                'group.shift',
                'group.vehicle',
                'group.driver',
                'group.assistant1',
                'group.assistant2',
                'group.assistant3',
                'group.assistant4',
                'group.assistant5',
                'groupDetails.employee'
            ])->find($schedulingId);

            if (!$scheduling) {
                return response()->json([
                    'success' => false,
                    'message' => 'Programación no encontrada'
                ], 404);
            }

            // Si hay un grupo asignado, usarlo
            if ($scheduling->group) {
                $group = $scheduling->group;
                \Log::info('Días del grupo: ' . $scheduling->group->days);
                $workDays = isset($group->days) ? explode(',', $group->days) : [];
                \Log::info('Días laborables: ' . json_encode($workDays));

            } else {
                $group = (object)[
                    'name' => 'Grupo Temporal - Programación #' . $scheduling->id,
                    'zone' => $scheduling->zone ?? null,
                    'shift' => $scheduling->shift ?? null,
                    'vehicle' => $scheduling->vehicle ?? null,
                    'days' => 'Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
                    'status' => 'active',
                    'driver' => null,
                    'assistant1' => null,
                    'assistant2' => null,
                    'assistant3' => null,
                    'assistant4' => null,
                    'assistant5' => null
                ];


            }

            $view = view('admin.scheduling.templates.group-details', [
                'group' => $group,
                'scheduling' => $scheduling
                // Imprime en pantalla para depuración
            ])->render();


            return response()->json([
                'success' => true,
                'html' => $view
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los detalles del grupo: ' . $e->getMessage()
            ], 500);
        }
    }
}
