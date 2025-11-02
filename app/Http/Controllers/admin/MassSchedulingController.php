<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeGroup;
use App\Models\Scheduling;
use App\Models\GroupDetail;
use App\Models\Shift;
use App\Models\Vehicle;
use App\Models\Zone;
use App\Models\Attendance;
use App\Models\Vacation;
use App\Models\Contract;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MassSchedulingController extends Controller
{
    public function index()
    {
        try {
            // Cargar grupos con relaciones optimizadas
            $groups = EmployeeGroup::with([
                'zone:id,name', 
                'shift:id,name', 
                'vehicle:id,code,people_capacity',
                'driver:id,name,last_name',
                'assistant1:id,name,last_name',
                'assistant2:id,name,last_name', 
                'assistant3:id,name,last_name',
                'assistant4:id,name,last_name',
                'assistant5:id,name,last_name'
            ])->active()->get();
                
            $shifts = Shift::select('id', 'name')->get();
            
            // FALTABA: Cargar empleados
            $employees = Employee::where('estado', 'activo')
                ->select('id', 'name', 'last_name', 'employeetype_id')
                ->get();
            
            return view('admin.scheduling.mass-index', compact('groups', 'shifts', 'employees'));
            
        } catch (\Exception $e) {
            \Log::error('Error en MassSchedulingController@index: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar la página de programación masiva');
        }
    }

    public function validateMassScheduling(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'groups' => 'required|array',
            'groups.*.group_id' => 'required|exists:employeegroups,id',
            'groups.*.shift_id' => 'required|exists:shifts,id',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $groupsData = $request->groups;

        $validationResults = [];
        $hasErrors = false;

        foreach ($groupsData as $groupData) {
            $validation = $this->validateGroupScheduling(
                $groupData['group_id'],
                $groupData['shift_id'],
                $startDate,
                $endDate,
                $groupData // Pasar todos los datos del grupo incluyendo empleados seleccionados
            );

            $validationResults[] = $validation;
            
            if ($validation['has_errors']) {
                $hasErrors = true;
            }
        }

        return response()->json([
            'success' => true,
            'has_errors' => $hasErrors,
            'validation_results' => $validationResults,
            'date_range' => [
                'start' => $startDate->format('d/m/Y'),
                'end' => $endDate->format('d/m/Y')
            ]
        ]);
    }

    private function validateGroupScheduling($groupId, $shiftId, $startDate, $endDate, $groupData)
    {
        $group = EmployeeGroup::with([
            'zone', 
            'shift',
            'vehicle'
        ])->find($groupId);
        
        $shift = Shift::find($shiftId);

        $errors = [];
        $warnings = [];

        // 1. Validar disponibilidad del vehículo del grupo (no se puede cambiar)
        $vehicleConflicts = $this->checkVehicleAvailability($group->vehicle_id, $startDate, $endDate, $groupId);
        if (!empty($vehicleConflicts)) {
            $errors[] = "Vehículo no disponible en las siguientes fechas: " . implode(', ', $vehicleConflicts);
        }

        // 2. Validar capacidad del vehículo vs grupo
        $groupSize = $this->getGroupMemberCount($groupData);
        if ($group->vehicle->people_capacity < $groupSize) {
            $errors[] = "El vehículo tiene capacidad para {$group->vehicle->people_capacity} personas, pero el grupo tiene {$groupSize} miembros";
        }

        // 3. Validar empleados SELECCIONADOS (no los originales del grupo)
        $employeeValidations = $this->validateSelectedEmployees($groupData, $startDate, $endDate);
        $errors = array_merge($errors, $employeeValidations['errors']);
        $warnings = array_merge($warnings, $employeeValidations['warnings']);

        // 4. Validar superposición con programaciones existentes
        $schedulingConflicts = $this->checkExistingSchedules($groupId, $startDate, $endDate);
        if (!empty($schedulingConflicts)) {
            $warnings[] = "El grupo ya tiene programaciones en las siguientes fechas: " . implode(', ', $schedulingConflicts);
        }

        return [
            'group_id' => $groupId,
            'group_name' => $group->name,
            'zone_name' => $group->zone->name ?? 'Sin zona',
            'shift_name' => $shift->name,
            'vehicle_code' => $group->vehicle->code,
            'has_errors' => !empty($errors),
            'has_warnings' => !empty($warnings),
            'errors' => $errors,
            'warnings' => $warnings,
            'employee_details' => $employeeValidations['details']
        ];
    }

    private function getGroupMemberCount($groupData)
    {
        $count = 0;
        if (!empty($groupData['driver_id'])) $count++;
        if (!empty($groupData['assistant1_id'])) $count++;
        if (!empty($groupData['assistant2_id'])) $count++;
        if (!empty($groupData['assistant3_id'])) $count++;
        if (!empty($groupData['assistant4_id'])) $count++;
        if (!empty($groupData['assistant5_id'])) $count++;
        
        return $count;
    }

    private function checkVehicleAvailability($vehicleId, $startDate, $endDate, $currentGroupId = null)
    {
        $conflicts = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $existingScheduling = Scheduling::where('vehicle_id', $vehicleId)
                ->where('date', $currentDate->format('Y-m-d'))
                ->whereHas('group', function($q) use ($currentGroupId) {
                    if ($currentGroupId) {
                        $q->where('id', '!=', $currentGroupId);
                    }
                })
                ->first();

            if ($existingScheduling) {
                $conflicts[] = $currentDate->format('d/m/Y');
            }

            $currentDate->addDay();
        }

        return $conflicts;
    }

    private function validateSelectedEmployees($groupData, $startDate, $endDate)
    {
        $errors = [];
        $warnings = [];
        $details = [];

        // Validar conductor SELECCIONADO
        if (!empty($groupData['driver_id'])) {
            $driver = Employee::find($groupData['driver_id']);
            if ($driver) {
                $driverValidation = $this->validateEmployee($driver, 'conductor', $startDate, $endDate);
                $details[] = $driverValidation['detail'];
                
                if (!empty($driverValidation['errors'])) {
                    $errors[] = "Conductor {$driver->name}: " . implode(', ', $driverValidation['errors']);
                }
            }
        } else {
            $errors[] = "Debe seleccionar un conductor";
        }

        // Validar ayudantes SELECCIONADOS
        $assistants = [
            ['id' => $groupData['assistant1_id'] ?? null, 'number' => 1],
            ['id' => $groupData['assistant2_id'] ?? null, 'number' => 2],
            ['id' => $groupData['assistant3_id'] ?? null, 'number' => 3],
            ['id' => $groupData['assistant4_id'] ?? null, 'number' => 4],
            ['id' => $groupData['assistant5_id'] ?? null, 'number' => 5],
        ];

        $assistantCount = 0;
        foreach ($assistants as $assistantData) {
            if (!empty($assistantData['id'])) {
                $assistant = Employee::find($assistantData['id']);
                if ($assistant) {
                    $assistantCount++;
                    $assistantValidation = $this->validateEmployee($assistant, 'ayudante', $startDate, $endDate);
                    $details[] = $assistantValidation['detail'];
                    
                    if (!empty($assistantValidation['errors'])) {
                        $errors[] = "Ayudante {$assistantData['number']} {$assistant->name}: " . implode(', ', $assistantValidation['errors']);
                    }
                }
            }
        }

        // Validar que haya al menos un ayudante
        if ($assistantCount === 0) {
            $errors[] = "Debe seleccionar al menos un ayudante";
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'details' => $details
        ];
    }

    private function validateEmployee($employee, $role, $startDate, $endDate)
    {
        $errors = [];
        $warnings = [];

        // Validar contrato activo
        $activeContract = Contract::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->where(function($q) use ($startDate) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $startDate);
            })
            ->first();

        if (!$activeContract) {
            $errors[] = "Sin contrato activo";
        }

        // Validar vacaciones
        $vacationConflict = Vacation::where('employee_id', $employee->id)
            ->whereIn('status', ['Approved', 'Pending'])
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            })
            ->exists();

        if ($vacationConflict) {
            $errors[] = "Tiene vacaciones programadas en este período";
        }

        // Validar si ya está programado en otro grupo para las mismas fechas
        $existingSchedule = Scheduling::whereHas('groupDetails', function($q) use ($employee, $startDate, $endDate) {
                $q->where('employee_id', $employee->id);
            })
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->exists();

        if ($existingSchedule) {
            $errors[] = "Ya está programado en otro grupo para estas fechas";
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'detail' => [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name . ' ' . $employee->last_name,
                'role' => $role,
                'has_errors' => !empty($errors),
                'has_warnings' => !empty($warnings),
                'errors' => $errors,
                'warnings' => $warnings
            ]
        ];
    }

    private function checkExistingSchedules($groupId, $startDate, $endDate)
    {
        $conflicts = [];
        $existingSchedules = Scheduling::where('group_id', $groupId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        foreach ($existingSchedules as $schedule) {
            $conflicts[] = Carbon::parse($schedule->date)->format('d/m/Y');
        }

        return $conflicts;
    }

    public function storeMassScheduling(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'groups' => 'required|array',
            'groups.*.group_id' => 'required|exists:employeegroups,id',
            'groups.*.shift_id' => 'required|exists:shifts,id',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $groupsData = $request->groups;

        $createdSchedules = [];
        $errors = [];

        foreach ($groupsData as $groupData) {
            try {
                $schedules = $this->createGroupSchedules(
                    $groupData['group_id'],
                    $groupData['shift_id'],
                    $startDate,
                    $endDate,
                    $groupData
                );

                $createdSchedules = array_merge($createdSchedules, $schedules);
            } catch (\Exception $e) {
                $errors[] = "Error al programar grupo {$groupData['group_id']}: " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Algunas programaciones no pudieron ser creadas',
                'errors' => $errors,
                'created_count' => count($createdSchedules)
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Programación masiva creada exitosamente',
            'created_count' => count($createdSchedules),
            'schedules' => $createdSchedules
        ]);
    }

    private function createGroupSchedules($groupId, $shiftId, $startDate, $endDate, $groupData)
    {
        $group = EmployeeGroup::find($groupId);
        $schedules = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            // Verificar si ya existe una programación para esta fecha
            $existing = Scheduling::where('group_id', $groupId)
                ->where('date', $currentDate->format('Y-m-d'))
                ->first();

            if (!$existing) {
                $scheduling = Scheduling::create([
                    'group_id' => $groupId,
                    'shift_id' => $shiftId,
                    'vehicle_id' => $group->vehicle_id, // Usar el vehículo del grupo
                    'date' => $currentDate->format('Y-m-d'),
                    'status' => 'active',
                    'notes' => 'Programación masiva'
                ]);

                // Crear detalles del grupo - Conductor SELECCIONADO
                if (!empty($groupData['driver_id'])) {
                    GroupDetail::create([
                        'employee_id' => $groupData['driver_id'],
                        'scheduling_id' => $scheduling->id,
                        'role' => 'conductor'
                    ]);
                }

                // Crear detalles del grupo - Ayudantes SELECCIONADOS
                $assistants = [
                    'assistant1_id',
                    'assistant2_id', 
                    'assistant3_id',
                    'assistant4_id',
                    'assistant5_id'
                ];

                foreach ($assistants as $assistantField) {
                    if (!empty($groupData[$assistantField])) {
                        GroupDetail::create([
                            'employee_id' => $groupData[$assistantField],
                            'scheduling_id' => $scheduling->id,
                            'role' => 'ayudante'
                        ]);
                    }
                }

                $schedules[] = $scheduling;
            }

            $currentDate->addDay();
        }

        return $schedules;
    }
    
    public function validateEmployeeAvailability(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'role' => 'required|string'
        ]);

        $employee = Employee::find($request->employee_id);
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $role = $request->role;

        $validation = $this->validateEmployee($employee, $role, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'employee_id' => $employee->id,
            'employee_name' => $employee->name . ' ' . $employee->last_name,
            'role' => $role,
            'has_errors' => !empty($validation['errors']),
            'has_warnings' => !empty($validation['warnings']),
            'errors' => $validation['errors'],
            'warnings' => $validation['warnings']
        ]);
    }

    // Nuevo método para obtener empleados disponibles
    public function getAvailableEmployees(Request $request)
    {
        try {
            $employees = Employee::active()
                ->with(['contracts' => function($q) {
                    $q->where('is_active', true);
                }])
                ->select('id', 'name', 'last_name', 'employeetype_id')
                ->get()
                ->map(function($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->name . ' ' . $employee->last_name,
                        'type' => $employee->employeetype_id == 1 ? 'conductor' : 'ayudante',
                        'has_active_contract' => $employee->contracts->isNotEmpty()
                    ];
                });

            return response()->json([
                'success' => true,
                'employees' => $employees
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar empleados'
            ], 500);
        }
    }
}