<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Scheduling;
use App\Models\SchedulingChange;
use App\Models\Shift;
use App\Models\Vehicle;
use App\Models\Employee;
use App\Models\GroupDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SchedulingChangeController extends Controller
{
    public function showChangesForm($id)
    {
        try {
            $scheduling = Scheduling::with([
                'shift',
                'vehicle',
                'groupDetails.employee.employeeType',
                'changes.changedBy'
            ])->findOrFail($id);

            $shifts = Shift::all();
            $vehicles = Vehicle::where('status', 1)->get();

            $employees = Employee::where('estado', 'activo')
                ->with('employeeType')
                ->get()
                ->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'last_name' => $employee->last_name,
                        'full_name' => $employee->name . ' ' . $employee->last_name,
                        'dni' => $employee->dni,
                        'employee_type_name' => $employee->employeeType->name ?? 'N/A',
                        'estado' => $employee->estado
                    ];
                });

            if (request()->ajax()) {
                return view('admin.scheduling.changes', compact(
                    'scheduling',
                    'shifts',
                    'vehicles',
                    'employees'
                ));
            }

            return redirect()->back()
                ->with('error', 'Esta función solo está disponible via AJAX');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar el formulario de cambios: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al cargar el formulario de cambios: ' . $e->getMessage());
        }
    }

    public function validateChange(Request $request, $id)
    {
        try {
            $scheduling = Scheduling::findOrFail($id);
            $changeType = $request->change_type;
            $newValueId = $request->new_value_id;
            $employeeRole = $request->employee_role;
            $pendingChanges = $request->get('pending_changes', []);

            $validation = $this->validateSingleChange($scheduling, $changeType, $newValueId, $employeeRole, $pendingChanges);

            return response()->json([
                'valid' => $validation['valid'],
                'message' => $validation['message'],
                'suggestions' => $validation['suggestions'] ?? [],
                'errors' => $validation['errors'] ?? []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Error en la validación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function applyChanges(Request $request, $id)
    {
        $rawChanges = null;

        if ($request->isJson()) {
            $jsonData = $request->json()->all();
            $rawChanges = $jsonData['changes'] ?? null;
        } else {
            $rawChanges = $request->input('changes');
        }

        if ($rawChanges === null) {
            return response()->json([
                'success' => false,
                'message' => 'No se recibieron cambios. Verifique que el campo "changes" esté presente.',
                'debug_info' => [
                    'request_method' => $request->method(),
                    'content_type' => $request->header('Content-Type'),
                    'is_json_request' => $request->isJson(),
                    'all_input_keys' => array_keys($request->all()),
                    'json_keys' => $request->isJson() ? array_keys($request->json()->all()) : [],
                    'raw_content_length' => strlen($request->getContent()),
                    'csrf_token_valid' => $request->input('_token') === csrf_token()
                ]
            ], 422);
        }

        $changes = $rawChanges;

        if (is_string($rawChanges)) {
            $changes = json_decode($rawChanges, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error en formato JSON: ' . json_last_error_msg()
                ], 422);
            }
        }

        if (!is_array($changes) || count($changes) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cambios para aplicar'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $scheduling = Scheduling::findOrFail($id);
            $changedBy = auth()->id();

            foreach ($changes as $index => $change) {
                if (!is_array($change) || !isset($change['type']) || !isset($change['new_values'])) {
                    throw new \Exception("Cambio inválido en índice {$index}");
                }

                $validation = $this->validateSingleChange(
                    $scheduling,
                    $change['type'],
                    $change['new_values']['id'],
                    $change['new_values']['role'] ?? null,
                    $changes
                );

                if (!$validation['valid']) {
                    throw new \Exception($validation['message']);
                }

                $this->applySingleChange($scheduling, $change, $changedBy);
            }

            $scheduling->update(['status' => 'Reprogramado']);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Cambios aplicados correctamente',
                'changes_count' => count($changes)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function validateSingleChange($scheduling, $changeType, $newValueId, $employeeRole = null, $pendingChanges = [])
    {
        switch ($changeType) {
            case 'turno':
                return $this->validateShiftChange($scheduling, $newValueId, $pendingChanges);
            case 'vehiculo':
                return $this->validateVehicleChange($scheduling, $newValueId, $pendingChanges);
            case 'ocupante':
                return $this->validateEmployeeChange($scheduling, $newValueId, $employeeRole, $pendingChanges);
            default:
                return [
                    'valid' => false,
                    'message' => 'Tipo de cambio no válido'
                ];
        }
    }

    /**
     * Validar cambio de turno
     */
    private function validateShiftChange($scheduling, $newShiftId, $pendingChanges = [])
    {
        $newShift = Shift::find($newShiftId);

        if (!$newShift) {
            return [
                'valid' => false,
                'message' => 'El turno seleccionado no existe',
                'errors' => ['Turno no encontrado']
            ];
        }

        $vehicleIdToUse = $scheduling->vehicle_id;

        foreach ($pendingChanges as $change) {
            if ($change['type'] === 'vehiculo') {
                $vehicleIdToUse = $change['new_values']['id'];
                break;
            }
        }

        // Validar duplicado de turno y vehículo
        $existing = Scheduling::where('date', $scheduling->date)
            ->where('shift_id', $newShiftId)
            ->where('vehicle_id', $vehicleIdToUse)
            ->where('id', '!=', $scheduling->id)
            ->exists();

        if ($existing) {
            return [
                'valid' => false,
                'message' => 'Ya existe una programación para esta fecha con el mismo turno y vehículo'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Turno disponible para el cambio'
        ];
    }

    /**
     * Validar cambio de vehículo
     */
    private function validateVehicleChange($scheduling, $newVehicleId, $pendingChanges = [])
    {
        // Validación de vehículo activo
        $vehicleValidation = $this->validateVehicleAvailability($newVehicleId);

        if (!empty($vehicleValidation['errors'])) {
            return [
                'valid' => false,
                'message' => $vehicleValidation['errors'][0],
                'errors' => $vehicleValidation['errors'],
                'suggestions' => $vehicleValidation['suggestions'] ?? []
            ];
        }

        $shiftIdToUse = $scheduling->shift_id;

        foreach ($pendingChanges as $change) {
            if ($change['type'] === 'turno') {
                $shiftIdToUse = $change['new_values']['id'];
                break;
            }
        }

        // Validar que el vehículo no esté usado en el MISMO TURNO de la misma fecha
        $existing = Scheduling::where('date', $scheduling->date)
            ->where('vehicle_id', $newVehicleId)
            ->where('shift_id', $shiftIdToUse)
            ->where('id', '!=', $scheduling->id)
            ->exists();

        if ($existing) {
            $conflictingSchedulings = Scheduling::with([
                'shift',
                'group.zone'
            ])
                ->where('date', $scheduling->date)
                ->where('vehicle_id', $newVehicleId)
                ->where('shift_id', $shiftIdToUse)
                ->where('id', '!=', $scheduling->id)
                ->get();

            $conflictDetails = $conflictingSchedulings->map(function ($conflict) {
                $zoneName = $conflict->group->zone->name ?? 'Otra zona';
                $shiftName = $conflict->shift->name ?? 'N/A';
                return "{$zoneName} (Turno {$shiftName})";
            })->implode(', ');

            return [
                'valid' => false,
                'message' => "El vehículo ya está asignado para esta fecha y turno en: {$conflictDetails}"
            ];
        }

        return [
            'valid' => true,
            'message' => 'Vehículo disponible para el cambio'
        ];
    }

    /**
     * Validar cambio de empleado considerando turnos
     */
    private function validateEmployeeChange($scheduling, $newEmployeeId, $role, $pendingChanges = [])
    {
        $newEmployee = Employee::find($newEmployeeId);

        if (!$newEmployee || $newEmployee->estado != 'activo') {
            return [
                'valid' => false,
                'message' => 'El empleado seleccionado no está disponible',
                'errors' => ['Empleado no disponible o inactivo']
            ];
        }

        // Validar disponibilidad del empleado para la fecha
        $validation = $newEmployee->canBeScheduled($scheduling->date);

        if (!$validation['can_be_scheduled']) {
            return [
                'valid' => false,
                'message' => $validation['error'],
                'errors' => [$validation['error']]
            ];
        }

        $shiftIdToUse = $scheduling->shift_id;

        foreach ($pendingChanges as $change) {
            if ($change['type'] === 'turno') {
                $shiftIdToUse = $change['new_values']['id'];
                break;
            }
        }

        $existing = GroupDetail::whereHas('scheduling', function ($q) use ($scheduling, $shiftIdToUse) {
            $q->where('date', $scheduling->date)
                ->where('shift_id', $shiftIdToUse);
        })
            ->where('employee_id', $newEmployeeId)
            ->where('scheduling_id', '!=', $scheduling->id)
            ->exists();

        if ($existing) {
            $conflictingScheduling = Scheduling::with(['shift', 'group.zone'])
                ->where('date', $scheduling->date)
                ->where('shift_id', $shiftIdToUse)
                ->whereHas('groupDetails', function ($q) use ($newEmployeeId) {
                    $q->where('employee_id', $newEmployeeId);
                })
                ->where('id', '!=', $scheduling->id)
                ->first();

            $conflictInfo = '';
            if ($conflictingScheduling) {
                $zoneName = $conflictingScheduling->group->zone->name ?? 'Otra zona';
                $shiftName = $conflictingScheduling->shift->name ?? 'N/A';
                $conflictInfo = " (asignado actualmente a {$zoneName} - Turno {$shiftName})";
            }

            return [
                'valid' => false,
                'message' => "El empleado ya está asignado para esta fecha y turno{$conflictInfo}"
            ];
        }

        // Validar tipo de empleado según el rol
        $expectedType = $role === 'conductor' ? 'Conductor' : 'Ayudante';
        $actualType = $newEmployee->employeeType->name ?? 'N/A';

        if ($actualType !== $expectedType) {
            return [
                'valid' => false,
                'message' => "El empleado seleccionado es {$actualType}, pero se requiere un {$expectedType}",
                'errors' => ["Tipo de empleado incorrecto: se esperaba {$expectedType}"]
            ];
        }

        return [
            'valid' => true,
            'message' => 'Empleado disponible para el cambio'
        ];
    }

    /**
     * Validación de vehículo
     */
    private function validateVehicleAvailability($vehicleId)
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
                $suggestions[] = "No hay vehículos alternativos disponibles";
            }
        }

        return [
            'errors' => $errors,
            'suggestions' => $suggestions
        ];
    }

    // Método para aplicar un cambio individual
    private function applySingleChange($scheduling, $change, $changedBy)
    {
        $oldValues = $this->getCurrentValues($scheduling, $change['type'], $change);

        switch ($change['type']) {
            case 'turno':
                $scheduling->update(['shift_id' => $change['new_values']['id']]);
                break;

            case 'vehiculo':
                $scheduling->update(['vehicle_id' => $change['new_values']['id']]);
                break;

            case 'ocupante':
                $this->applyOccupantChange($scheduling, $change, $oldValues);
                break;
        }

        // Registrar en el historial
        $scheduling->logChange(
            $changedBy,
            $change['type'],
            $change['reason'],
            $oldValues,
            $change['new_values']
        );
    }

    /**
     * Obtener valores actuales antes del cambio
     */
    private function getCurrentValues($scheduling, $changeType, $change = null)
    {
        switch ($changeType) {
            case 'turno':
                return $scheduling->shift ? [
                    'id' => $scheduling->shift->id,
                    'name' => $scheduling->shift->name,
                    'hour_in' => $scheduling->shift->hour_in,
                    'hour_out' => $scheduling->shift->hour_out
                ] : null;

            case 'vehiculo':
                return $scheduling->vehicle ? [
                    'id' => $scheduling->vehicle->id,
                    'name' => $scheduling->vehicle->name,
                    'plate' => $scheduling->vehicle->plate
                ] : null;

            case 'ocupante':
                return $this->getCurrentEmployeeValue($scheduling, $change);

            default:
                return null;
        }
    }

    private function getCurrentEmployeeValue($scheduling, $change)
    {
        $oldEmployeeId = $change['old_values']['id'] ?? null;
        $role = $change['new_values']['role'] ?? null;

        if (!$oldEmployeeId || !$role) {
            return null;
        }

        // Buscar el empleado actual en la base de datos
        $currentEmployee = Employee::find($oldEmployeeId);

        if (!$currentEmployee) {
            return null;
        }

        // Buscar el detalle del grupo para obtener información adicional
        $groupDetail = GroupDetail::where('scheduling_id', $scheduling->id)
            ->where('employee_id', $oldEmployeeId)
            ->where('role', $role)
            ->first();

        return [
            'id' => $currentEmployee->id,
            'name' => $currentEmployee->name . ' ' . $currentEmployee->last_name,
            'dni' => $currentEmployee->dni,
            'role' => $role,
            'employee_type' => $currentEmployee->employeeType->name ?? 'N/A'
        ];
    }

    /**
     * Aplicar cambio de ocupantes
     */
    private function applyOccupantChange($scheduling, $change, $oldValues)
    {
        $oldEmployeeId = $change['old_values']['id'];
        $newEmployeeId = $change['new_values']['id'];
        $role = $change['new_values']['role'];

        $groupDetail = GroupDetail::where('scheduling_id', $scheduling->id)
            ->where('employee_id', $oldEmployeeId)
            ->where('role', $role)
            ->first();

        if ($groupDetail) {
            $groupDetail->update(['employee_id' => $newEmployeeId]);
        } else {
            GroupDetail::create([
                'scheduling_id' => $scheduling->id,
                'employee_id' => $newEmployeeId,
                'role' => $role
            ]);
        }
    }

    // Obtener historial de cambios
    public function getChangeHistory($id)
    {
        try {
            $changes = SchedulingChange::with('changedBy')
                ->where('scheduling_id', $id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($change) {
                    return [
                        'id' => $change->id,
                        'change_type' => $change->change_type,
                        'change_type_display' => $this->getChangeTypeDisplay($change->change_type),
                        'reason' => $change->reason,
                        'old_values' => $change->old_values,
                        'new_values' => $change->new_values,
                        'changed_by' => $change->changedBy ? [
                            'name' => $change->changedBy->name,
                            'last_name' => $change->changedBy->last_name
                        ] : null,
                        'created_at' => $change->created_at->format('d/m/Y H:i:s'),
                        'created_at_raw' => $change->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'changes' => $changes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getChangeTypeDisplay($changeType)
    {
        $types = [
            'turno' => 'Cambio de Turno',
            'vehiculo' => 'Cambio de Vehículo',
            'ocupante' => 'Cambio de Personal'
        ];

        return $types[$changeType] ?? $changeType;
    }

    /**
     * Validar todos los cambios pendientes juntos
     */
    public function validateAllChanges(Request $request, $id)
    {
        try {
            $scheduling = Scheduling::findOrFail($id);
            $pendingChanges = $request->pending_changes ?? [];

            if (is_string($pendingChanges)) {
                $pendingChanges = json_decode($pendingChanges, true) ?? [];
            }

            $validation = $this->validateAllChangesCombined($scheduling, $pendingChanges);

            return response()->json([
                'valid' => $validation['valid'],
                'errors' => $validation['errors'] ?? [],
                'message' => $validation['valid'] ?
                    'Todos los cambios son válidos' :
                    'Se encontraron conflictos en los cambios'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Error en la validación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar todos los cambios juntos
     */
    private function validateAllChangesCombined($scheduling, $pendingChanges)
    {
        $errors = [];

        $simulatedScheduling = (object)[
            'date' => $scheduling->date,
            'shift_id' => $scheduling->shift_id,
            'vehicle_id' => $scheduling->vehicle_id,
            'id' => $scheduling->id
        ];

        // Aplicar cambios pendientes a la simulación
        foreach ($pendingChanges as $change) {
            switch ($change['type']) {
                case 'turno':
                    $simulatedScheduling->shift_id = $change['new_values']['id'];
                    break;
                case 'vehiculo':
                    $simulatedScheduling->vehicle_id = $change['new_values']['id'];
                    break;
            }
        }

        // Validar combinación de turno y vehículo
        if ($simulatedScheduling->shift_id && $simulatedScheduling->vehicle_id) {
            $existing = Scheduling::where('date', $scheduling->date)
                ->where('shift_id', $simulatedScheduling->shift_id)
                ->where('vehicle_id', $simulatedScheduling->vehicle_id)
                ->where('id', '!=', $scheduling->id)
                ->exists();

            if ($existing) {
                $errors[] = 'La combinación de turno y vehículo ya existe para esta fecha';
            }
        }

        // Validar empleados contra el turno simulado
        foreach ($pendingChanges as $change) {
            if ($change['type'] === 'ocupante') {
                $employeeValidation = $this->validateEmployeeChange(
                    $scheduling,
                    $change['new_values']['id'],
                    $change['new_values']['role'],
                    $pendingChanges
                );

                if (!$employeeValidation['valid']) {
                    $errors[] = $employeeValidation['message'];
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
