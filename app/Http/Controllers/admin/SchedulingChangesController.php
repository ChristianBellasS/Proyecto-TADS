<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scheduling;
use App\Models\SchedulingChange;
use App\Models\Employee;
use App\Models\Vehicle;
use App\Models\Shift;
use App\Models\GroupDetail;
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
                'change_type' => 'required|in:turno,conductor,vehiculo,ocupante'
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
            } else if ($changeType === 'turno') {
                // Turnos que tienen programaciones en el rango de fechas
                $resources = Shift::whereHas('schedulings', function($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('date', [$fechaInicio, $fechaFin]);
                })->get()->map(function($shift) {
                    return [
                        'id' => $shift->id,
                        'text' => $shift->name . ' (' . $shift->hour_in . ' - ' . $shift->hour_out . ')'
                    ];
                });
            } else {
                // Para conductor y ocupante - usar groupdetails
                $role = $changeType === 'conductor' ? 'conductor' : 'ayudante';
                
                $resources = Employee::whereHas('groupDetails', function($q) use ($role, $fechaInicio, $fechaFin) {
                    $q->where('role', $role)
                      ->whereHas('scheduling', function($query) use ($fechaInicio, $fechaFin) {
                          $query->whereBetween('date', [$fechaInicio, $fechaFin]);
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
                'change_type' => 'required|in:turno,conductor,vehiculo,ocupante'
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
            } else if ($changeType === 'turno') {
                // TODOS los turnos
                $resources = Shift::all()->map(function($shift) {
                    return [
                        'id' => $shift->id,
                        'text' => $shift->name . ' (' . $shift->hour_in . ' - ' . $shift->hour_out . ')'
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
                'change_type' => 'required|in:turno,conductor,vehiculo,ocupante',
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
            } else if ($changeType === 'turno') {
                // Para turnos, validar que exista
                $shift = Shift::find($resourceId);
                if (!$shift) {
                    return response()->json([
                        'available' => false,
                        'message' => 'El turno no existe'
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
                    ->whereHas('groupDetails', function($query) use ($resourceId, $role) {
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

    // MÉTODO: Validar antes de guardar con SweetAlert
    public function validateResourceBeforeSave(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date',
                'change_type' => 'required|in:turno,conductor,vehiculo,ocupante',
                'resource_actual' => 'required',
                'resource_nuevo' => 'required'
            ]);

            $fechaInicio = $request->fecha_inicio;
            $fechaFin = $request->fecha_fin;
            $changeType = $request->change_type;
            $resourceActual = $request->resource_actual;
            $resourceNuevo = $request->resource_nuevo;

            $warnings = [];
            $blockingErrors = [];

            // Validar que no sean el mismo recurso
            if ($resourceActual == $resourceNuevo) {
                $blockingErrors[] = 'No puede seleccionar el mismo recurso para reemplazar';
            }

            // Validar disponibilidad del nuevo recurso
            $validationResponse = $this->validateResourceAvailability(new Request([
                'resource_id' => $resourceNuevo,
                'change_type' => $changeType,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]));

            if (!$validationResponse->getData()->available) {
                $blockingErrors[] = $validationResponse->getData()->message;
            }

            // Verificar conflictos adicionales
            if ($changeType === 'ocupante') {
                $employee = Employee::find($resourceNuevo);
                if ($employee) {
                    // Verificar si el ocupante ya tiene programaciones en otras rutas
                    $otherSchedules = Scheduling::whereBetween('date', [$fechaInicio, $fechaFin])
                        ->whereHas('groupDetails', function($query) use ($resourceNuevo) {
                            $query->where('employee_id', $resourceNuevo)
                                  ->where('role', 'ayudante');
                        })
                        ->count();

                    if ($otherSchedules > 0) {
                        $warnings[] = "El ocupante tiene $otherSchedules programación(es) existente(s) en el rango seleccionado";
                    }
                }
            }

            return response()->json([
                'valid' => empty($blockingErrors),
                'warnings' => $warnings,
                'blocking_errors' => $blockingErrors,
                'can_proceed' => empty($blockingErrors)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en validateResourceBeforeSave: ' . $e->getMessage());
            return response()->json([
                'valid' => false,
                'warnings' => [],
                'blocking_errors' => ['Error al validar los datos'],
                'can_proceed' => false
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'change_type' => 'required|in:turno,conductor,vehiculo,ocupante',
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
                case 'turno':
                    $request->validate([
                        'turno_actual' => 'required|exists:shifts,id',
                        'nuevo_turno' => 'required|exists:shifts,id|different:turno_actual'
                    ]);

                    $oldShift = Shift::find($request->turno_actual);
                    $newShift = Shift::find($request->nuevo_turno);

                    $schedulings = Scheduling::whereBetween('date', [$fechaInicio, $fechaFin])
                        ->where('shift_id', $request->turno_actual)
                        ->get();

                    foreach ($schedulings as $scheduling) {
                        $oldShiftId = $scheduling->shift_id;
                        $scheduling->update([
                            'shift_id' => $request->nuevo_turno,
                            'status' => 'Reprogramado' // ← AGREGADO AQUÍ
                        ]);
                        
                        // Registrar el cambio con formato detallado como en el controlador individual
                        SchedulingChange::create([
                            'scheduling_id' => $scheduling->id,
                            'changed_by' => auth()->id(),
                            'change_type' => 'turno',
                            'reason' => $request->reason,
                            'old_values' => [
                                'id' => $oldShift->id,
                                'name' => $oldShift->name,
                                'hour_in' => $oldShift->hour_in,
                                'hour_out' => $oldShift->hour_out
                            ],
                            'new_values' => [
                                'id' => $newShift->id,
                                'name' => $newShift->name,
                                'hour_in' => $newShift->hour_in,
                                'hour_out' => $newShift->hour_out
                            ]
                        ]);
                        
                        $affectedSchedulings[] = $scheduling->id;
                    }
                    break;

                case 'conductor':
                    $request->validate([
                        'conductor_actual' => 'required|exists:employees,id',
                        'nuevo_conductor' => 'required|exists:employees,id|different:conductor_actual'
                    ]);

                    $oldConductor = Employee::find($request->conductor_actual);
                    $newConductor = Employee::find($request->nuevo_conductor);

                    // Buscar programaciones en el rango donde el conductor actual está asignado
                    $schedulings = Scheduling::whereBetween('date', [$fechaInicio, $fechaFin])
                        ->whereHas('groupDetails', function($query) use ($request) {
                            $query->where('employee_id', $request->conductor_actual)
                                ->where('role', 'conductor');
                        })->get();

                    foreach ($schedulings as $scheduling) {
                        // Actualizar el groupdetail correspondiente
                        $groupDetail = GroupDetail::where('scheduling_id', $scheduling->id)
                            ->where('employee_id', $request->conductor_actual)
                            ->where('role', 'conductor')
                            ->first();

                        if ($groupDetail) {
                            $oldEmployeeId = $groupDetail->employee_id;
                            $groupDetail->update(['employee_id' => $request->nuevo_conductor]);
                            
                            // Actualizar estado del scheduling a Reprogramado
                            $scheduling->update(['status' => 'Reprogramado']); // ← AGREGADO AQUÍ
                            
                            // Registrar el cambio con formato detallado
                            SchedulingChange::create([
                                'scheduling_id' => $scheduling->id,
                                'changed_by' => auth()->id(),
                                'change_type' => 'ocupante',
                                'reason' => $request->reason,
                                'old_values' => [
                                    'id' => $oldConductor->id,
                                    'name' => $oldConductor->name . ' ' . $oldConductor->last_name,
                                    'dni' => $oldConductor->dni,
                                    'role' => 'conductor',
                                    'employee_type' => $oldConductor->employeeType->name ?? 'N/A'
                                ],
                                'new_values' => [
                                    'id' => $newConductor->id,
                                    'name' => $newConductor->name . ' ' . $newConductor->last_name,
                                    'dni' => $newConductor->dni,
                                    'role' => 'conductor',
                                    'employee_type' => $newConductor->employeeType->name ?? 'N/A'
                                ]
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

                    $oldVehicle = Vehicle::find($request->vehiculo_actual);
                    $newVehicle = Vehicle::find($request->nuevo_vehiculo);

                    $schedulings = Scheduling::whereBetween('date', [$fechaInicio, $fechaFin])
                        ->where('vehicle_id', $request->vehiculo_actual)
                        ->get();

                    foreach ($schedulings as $scheduling) {
                        $oldVehicleId = $scheduling->vehicle_id;
                        $scheduling->update([
                            'vehicle_id' => $request->nuevo_vehiculo,
                            'status' => 'Reprogramado' // ← AGREGADO AQUÍ
                        ]);
                        
                        // Registrar el cambio con formato detallado
                        SchedulingChange::create([
                            'scheduling_id' => $scheduling->id,
                            'changed_by' => auth()->id(),
                            'change_type' => 'vehiculo',
                            'reason' => $request->reason,
                            'old_values' => [
                                'id' => $oldVehicle->id,
                                'name' => $oldVehicle->name,
                                'plate' => $oldVehicle->plate
                            ],
                            'new_values' => [
                                'id' => $newVehicle->id,
                                'name' => $newVehicle->name,
                                'plate' => $newVehicle->plate
                            ]
                        ]);
                        
                        $affectedSchedulings[] = $scheduling->id;
                    }
                    break;

                case 'ocupante':
                    $request->validate([
                        'ocupante_actual' => 'required|exists:employees,id',
                        'nuevo_ocupante' => 'required|exists:employees,id|different:ocupante_actual'
                    ]);

                    $oldOcupante = Employee::find($request->ocupante_actual);
                    $newOcupante = Employee::find($request->nuevo_ocupante);

                    // Buscar programaciones en el rango donde el ocupante (ayudante) actual está asignado
                    $schedulings = Scheduling::whereBetween('date', [$fechaInicio, $fechaFin])
                        ->whereHas('groupDetails', function($query) use ($request) {
                            $query->where('employee_id', $request->ocupante_actual)
                                ->where('role', 'ayudante');
                        })->get();

                    foreach ($schedulings as $scheduling) {
                        // Actualizar el groupdetail correspondiente
                        $groupDetail = GroupDetail::where('scheduling_id', $scheduling->id)
                            ->where('employee_id', $request->ocupante_actual)
                            ->where('role', 'ayudante')
                            ->first();

                        if ($groupDetail) {
                            $oldEmployeeId = $groupDetail->employee_id;
                            $groupDetail->update(['employee_id' => $request->nuevo_ocupante]);
                            
                            // Actualizar estado del scheduling a Reprogramado
                            $scheduling->update(['status' => 'Reprogramado']); // ← AGREGADO AQUÍ
                            
                            // Registrar el cambio con formato detallado
                            SchedulingChange::create([
                                'scheduling_id' => $scheduling->id,
                                'changed_by' => auth()->id(),
                                'change_type' => 'ocupante',
                                'reason' => $request->reason,
                                'old_values' => [
                                    'id' => $oldOcupante->id,
                                    'name' => $oldOcupante->name . ' ' . $oldOcupante->last_name,
                                    'dni' => $oldOcupante->dni,
                                    'role' => 'ayudante',
                                    'employee_type' => $oldOcupante->employeeType->name ?? 'N/A'
                                ],
                                'new_values' => [
                                    'id' => $newOcupante->id,
                                    'name' => $newOcupante->name . ' ' . $newOcupante->last_name,
                                    'dni' => $newOcupante->dni,
                                    'role' => 'ayudante',
                                    'employee_type' => $newOcupante->employeeType->name ?? 'N/A'
                                ]
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

    // MÉTODO PARA ELIMINAR UN CAMBIO
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