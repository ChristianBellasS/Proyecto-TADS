<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scheduling;
use App\Models\SchedulingChange;
use App\Models\Employee;
use App\Models\Vehicle;
use App\Models\Employeegroup;
use App\Models\Configgroup;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SchedulingChangesController extends Controller
{
    public function index(Request $request)
    {
        $query = SchedulingChange::with(['scheduling', 'changedBy'])->latest();

        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && $request->fecha_fin) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        $schedulingChanges = $query->get();

        return view('admin.scheduling-changes.index', compact('schedulingChanges'));
    }

    public function create()
    {
        return view('admin.scheduling-changes.create');
    }

    // RUTA PARA RECURSOS A REEMPLAZAR (con filtro de fechas)
    public function getResourcesByRange(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date',
                'change_type' => 'required|in:conductor,vehiculo,ocupante'
            ]);

            $fechaInicio = $request->fecha_inicio;
            $fechaFin = $request->fecha_fin;
            $changeType = $request->change_type;

            if ($changeType === 'vehiculo') {
                // Vehículos que tienen programaciones en el rango de fechas
                $resources = Vehicle::whereHas('schedulings', function($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('date', [$fechaInicio, $fechaFin]);
                })->get()->map(function($vehicle) {
                    return [
                        'id' => $vehicle->id,
                        'text' => $vehicle->plate . ' - ' . $vehicle->name
                    ];
                });
            } else {
                // Para conductor y ocupante (ayudante)
                $role = $changeType === 'conductor' ? 'conductor' : 'ayudante';
                
                $resources = Employee::whereHas('configgroups', function($q) use ($role, $fechaInicio, $fechaFin) {
                    $q->where('role', $role)
                      ->whereHas('employeegroup', function($query) use ($fechaInicio, $fechaFin) {
                          $query->whereHas('schedulings', function($subQuery) use ($fechaInicio, $fechaFin) {
                              $subQuery->whereBetween('date', [$fechaInicio, $fechaFin]);
                          });
                      });
                })->get()->map(function($empleado) {
                    return [
                        'id' => $empleado->id,
                        'text' => trim($empleado->name . ' ' . $empleado->last_name) . ' - ' . $empleado->dni
                    ];
                });
            }

            return response()->json(['resources' => $resources]);

        } catch (\Exception $e) {
            \Log::error('Error en getResourcesByRange: ' . $e->getMessage());
            return response()->json(['resources' => []]);
        }
    }

    // RUTA PARA NUEVOS RECURSOS (TODOS los disponibles con validaciones)
    public function getAllResources(Request $request)
    {
        try {
            $request->validate([
                'change_type' => 'required|in:conductor,vehiculo,ocupante'
            ]);

            $changeType = $request->change_type;

            if ($changeType === 'vehiculo') {
                // TODOS los vehículos activos
                $resources = Vehicle::where('status', 1)->get()->map(function($vehicle) {
                    return [
                        'id' => $vehicle->id,
                        'text' => $vehicle->plate . ' - ' . $vehicle->name . ' (Activo)'
                    ];
                });
            } else {
                // Filtrar por tipo de empleado según employeetype_id
                $employeetypeId = $changeType === 'conductor' ? 1 : 2;
                
                // Empleados activos con contrato vigente y del tipo correcto
                $resources = Employee::where('estado', 'activo')
                    ->where('employeetype_id', $employeetypeId)
                    ->whereHas('contracts', function($q) {
                        $q->where('is_active', true)
                          ->where(function($query) {
                              $query->whereNull('end_date')
                                    ->orWhere('end_date', '>=', now());
                          });
                    })
                    ->get()
                    ->map(function($empleado) {
                        return [
                            'id' => $empleado->id,
                            'text' => trim($empleado->name . ' ' . $empleado->last_name) . ' - ' . $empleado->dni . ' (Contrato Vigente)'
                        ];
                    });
            }

            return response()->json(['resources' => $resources]);

        } catch (\Exception $e) {
            \Log::error('Error en getAllResources: ' . $e->getMessage());
            return response()->json(['resources' => []]);
        }
    }

    // MÉTODO: Validar disponibilidad del nuevo recurso
    public function validateResourceAvailability(Request $request)
    {
        try {
            $request->validate([
                'resource_id' => 'required',
                'change_type' => 'required|in:conductor,vehiculo,ocupante',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date'
            ]);

            $resourceId = $request->resource_id;
            $changeType = $request->change_type;
            $fechaInicio = $request->fecha_inicio;
            $fechaFin = $request->fecha_fin;

            if ($changeType === 'vehiculo') {
                // Validar si el vehículo está activo
                $vehicle = Vehicle::find($resourceId);
                if (!$vehicle || $vehicle->status != 1) {
                    return response()->json([
                        'available' => false,
                        'message' => 'El vehículo no está activo o no existe'
                    ]);
                }

                // Validar conflictos de horario
                $conflicts = Scheduling::whereBetween('date', [$fechaInicio, $fechaFin])
                    ->where('vehicle_id', $resourceId)
                    ->exists();
                
                if ($conflicts) {
                    $message = 'El vehículo ya tiene programaciones en el rango de fechas seleccionado';
                    return response()->json([
                        'available' => false,
                        'message' => $message
                    ]);
                }
            } else {
                // Validar si el empleado está activo
                $employee = Employee::find($resourceId);
                if (!$employee || $employee->estado !== 'activo') {
                    return response()->json([
                        'available' => false,
                        'message' => 'El empleado no está activo o no existe'
                    ]);
                }

                // Validar contrato vigente
                $hasValidContract = Contract::where('employee_id', $resourceId)
                    ->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    })
                    ->exists();

                if (!$hasValidContract) {
                    return response()->json([
                        'available' => false,
                        'message' => 'El empleado no tiene un contrato vigente'
                    ]);
                }

                // Validar tipo de empleado correcto
                $expectedEmployeetype = $changeType === 'conductor' ? 1 : 2;
                if ($employee->employeetype_id != $expectedEmployeetype) {
                    $expectedType = $changeType === 'conductor' ? 'conductor' : 'ocupante';
                    return response()->json([
                        'available' => false,
                        'message' => "El empleado no es del tipo $expectedType"
                    ]);
                }

                // Validar conflictos de horario según el rol
                $role = $changeType === 'conductor' ? 'conductor' : 'ayudante';
                $conflicts = Scheduling::whereBetween('date', [$fechaInicio, $fechaFin])
                    ->whereHas('group.configgroups', function($query) use ($resourceId, $role) {
                        $query->where('employee_id', $resourceId)
                              ->where('role', $role);
                    })
                    ->exists();
                
                if ($conflicts) {
                    $message = $changeType === 'conductor' 
                        ? 'El conductor ya tiene programaciones como conductor en el rango de fechas seleccionado'
                        : 'El ocupante ya tiene programaciones como ayudante en el rango de fechas seleccionado';
                    return response()->json([
                        'available' => false,
                        'message' => $message
                    ]);
                }
            }

            return response()->json([
                'available' => true,
                'message' => 'Recurso disponible'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en validateResourceAvailability: ' . $e->getMessage());
            return response()->json([
                'available' => false,
                'message' => 'Error al validar disponibilidad'
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'change_type' => 'required|in:conductor,vehiculo,ocupante',
                'reason' => 'required|string|min:10'
            ]);

            DB::beginTransaction();

            $fechaInicio = $request->fecha_inicio;
            $fechaFin = $request->fecha_fin;
            $changeType = $request->change_type;
            $affectedSchedulings = [];

            // Validar disponibilidad del nuevo recurso ANTES de procesar
            $newResourceField = 'nuevo_' . $changeType;
            $validationResponse = $this->validateResourceAvailability(new Request([
                'resource_id' => $request->input($newResourceField),
                'change_type' => $changeType,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]));

            if (!$validationResponse->getData()->available) {
                throw new \Exception($validationResponse->getData()->message);
            }

            switch ($changeType) {
                case 'conductor':
                    $request->validate([
                        'conductor_actual' => 'required|exists:employees,id',
                        'nuevo_conductor' => 'required|exists:employees,id|different:conductor_actual'
                    ]);

                    // Buscar programaciones en el rango donde el conductor actual está asignado
                    $schedulings = Scheduling::whereBetween('date', [$fechaInicio, $fechaFin])
                        ->whereHas('group.configgroups', function($query) use ($request) {
                            $query->where('employee_id', $request->conductor_actual)
                                  ->where('role', 'conductor');
                        })->get();

                    foreach ($schedulings as $scheduling) {
                        // Actualizar el configgroup correspondiente
                        $configGroup = Configgroup::where('employeegroup_id', $scheduling->group_id)
                            ->where('employee_id', $request->conductor_actual)
                            ->where('role', 'conductor')
                            ->first();

                        if ($configGroup) {
                            $oldEmployeeId = $configGroup->employee_id;
                            $configGroup->update(['employee_id' => $request->nuevo_conductor]);
                            
                            // Registrar el cambio - usar 'ocupante' en la tabla según la migración
                            SchedulingChange::create([
                                'scheduling_id' => $scheduling->id,
                                'changed_by' => auth()->id(),
                                'change_type' => 'ocupante', // Según la migración
                                'reason' => $request->reason,
                                'old_values' => ['conductor_id' => $oldEmployeeId],
                                'new_values' => ['conductor_id' => $request->nuevo_conductor]
                            ]);
                            
                            $affectedSchedulings[] = $scheduling->id;
                        }
                    }
                    break;

                case 'vehiculo':
                    $request->validate([
                        'vehiculo_actual' => 'required|exists:vehicles,id',
                        'nuevo_vehiculo' => 'required|exists:vehicles,id|different:vehiculo_actual'
                    ]);

                    $schedulings = Scheduling::whereBetween('date', [$fechaInicio, $fechaFin])
                        ->where('vehicle_id', $request->vehiculo_actual)
                        ->get();

                    foreach ($schedulings as $scheduling) {
                        $oldVehicleId = $scheduling->vehicle_id;
                        // CORRECCIÓN: Cambiar la coma por =>
                        $scheduling->update(['vehicle_id' => $request->nuevo_vehiculo]);
                        
                        SchedulingChange::create([
                            'scheduling_id' => $scheduling->id,
                            'changed_by' => auth()->id(),
                            'change_type' => 'vehiculo',
                            'reason' => $request->reason,
                            'old_values' => ['vehicle_id' => $oldVehicleId],
                            'new_values' => ['vehicle_id' => $request->nuevo_vehiculo]
                        ]);
                        
                        $affectedSchedulings[] = $scheduling->id;
                    }
                    break;

                case 'ocupante':
                    $request->validate([
                        'ocupante_actual' => 'required|exists:employees,id',
                        'nuevo_ocupante' => 'required|exists:employees,id|different:ocupante_actual'
                    ]);

                    // Buscar programaciones en el rango donde el ocupante (ayudante) actual está asignado
                    $schedulings = Scheduling::whereBetween('date', [$fechaInicio, $fechaFin])
                        ->whereHas('group.configgroups', function($query) use ($request) {
                            $query->where('employee_id', $request->ocupante_actual)
                                  ->where('role', 'ayudante');
                        })->get();

                    foreach ($schedulings as $scheduling) {
                        // Actualizar el configgroup correspondiente
                        $configGroup = Configgroup::where('employeegroup_id', $scheduling->group_id)
                            ->where('employee_id', $request->ocupante_actual)
                            ->where('role', 'ayudante')
                            ->first();

                        if ($configGroup) {
                            $oldEmployeeId = $configGroup->employee_id;
                            $configGroup->update(['employee_id' => $request->nuevo_ocupante]);
                            
                            // Registrar el cambio
                            SchedulingChange::create([
                                'scheduling_id' => $scheduling->id,
                                'changed_by' => auth()->id(),
                                'change_type' => 'ocupante',
                                'reason' => $request->reason,
                                'old_values' => ['ocupante_id' => $oldEmployeeId],
                                'new_values' => ['ocupante_id' => $request->nuevo_ocupante]
                            ]);
                            
                            $affectedSchedulings[] = $scheduling->id;
                        }
                    }
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cambio masivo aplicado exitosamente',
                'affected_count' => count($affectedSchedulings)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // MÉTODO PARA MOSTRAR DETALLES DE UN CAMBIO
    public function show(SchedulingChange $schedulingChange)
    {
        return view('admin.scheduling-changes.show', compact('schedulingChange'));
    }

    // MÉTODO PARA ELIMINAR UN CAMBIO (OPCIONAL)
    public function destroy(SchedulingChange $schedulingChange)
    {
        try {
            DB::beginTransaction();
            
            $schedulingChange->delete();
            
            DB::commit();
            
            return redirect()->route('admin.scheduling-changes.index')
                ->with('success', 'Cambio eliminado exitosamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error eliminando cambio: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al eliminar el cambio: ' . $e->getMessage());
        }
    }
}