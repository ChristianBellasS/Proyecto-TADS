<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeGroup;
use App\Models\ConfigGroup;
use App\Models\Zone;
use App\Models\Shift;
use App\Models\Vehicle;
use App\Models\Employee;
use App\Models\ZoneShift;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EmployeeGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $groups = EmployeeGroup::with(['zone', 'shift', 'vehicle', 'driver', 'assistant1', 'assistant2', 'assistant3', 'assistant4', 'assistant5'])->get();

        if ($request->ajax()) {
            return DataTables::of($groups)
                ->addColumn("zone", function ($group) {
                    return $group->zone->name ?? 'N/A';
                })
                ->addColumn("shift", function ($group) {
                    return $group->shift->name ?? 'N/A';
                })
                ->addColumn("vehicle", function ($group) {
                    return $group->vehicle->plate ?? 'N/A';
                })
                ->addColumn("driver", function ($group) {
                    return $group->driver ? $group->driver->name . ' ' . $group->driver->last_name : 'N/A';
                })
                ->addColumn("assistants", function ($group) {
                    $assistants = [];
                    for ($i = 1; $i <= 5; $i++) {
                        $assistant = $group->{"assistant{$i}"};
                        if ($assistant) {
                            $assistants[] = $assistant->name . ' ' . $assistant->last_name;
                        }
                    }
                    return count($assistants) > 0 ? implode(', ', $assistants) : 'N/A';
                })
                ->addColumn("edit", function ($group) {
                    return '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $group->id . '"><i class="fas fa-pen"></i></button>';
                })
                ->addColumn("delete", function ($group) {
                    return '<form action="' . route('admin.employeegroups.destroy', $group->id) . '" method="POST" class="frmDelete">' . 
                        csrf_field() . method_field('DELETE') . 
                        '<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>';
                })
                ->rawColumns(['edit', 'delete'])
                ->make(true);
        } else {
            return view('admin.employeegroups.index', compact('groups'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $zones = Zone::all();
            $shifts = Shift::all();
            
            // Obtener vehículos que NO están asignados a ningún grupo activo
            $vehicles = Vehicle::whereDoesntHave('employeeGroups', function($query) {
                $query->where('status', 'active');
            })->get();
            
            $employees = Employee::where('estado', 'activo')->get();
            
            return view('admin.employeegroups.create', compact('zones', 'shifts', 'vehicles', 'employees'));
        } catch (\Exception $e) {
            abort(500, 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'days' => 'required|array',
                'zone_id' => 'required|exists:zones,id',
                'shift_id' => 'required|exists:shifts,id',
                'vehicle_id' => 'required|exists:vehicles,id',
                'driver_id' => 'nullable|exists:employees,id',
                'assistant1_id' => 'nullable|exists:employees,id',
                'assistant2_id' => 'nullable|exists:employees,id',
                'assistant3_id' => 'nullable|exists:employees,id',
                'assistant4_id' => 'nullable|exists:employees,id',
                'assistant5_id' => 'nullable|exists:employees,id',
            ]);

            // Verificar que el vehículo no esté ya asignado a otro grupo activo
            $existingVehicle = EmployeeGroup::where('vehicle_id', $request->vehicle_id)
                ->where('status', 'active')
                ->first();

            if ($existingVehicle) {
                return response()->json([
                    'message' => 'Error: El vehículo ya está asignado a otro grupo activo.',
                    'error' => 'Vehículo no disponible'
                ], 422);
            }

            // Verificar si ya existe un grupo activo para esta zona y turno
            $existingZoneShift = EmployeeGroup::where('zone_id', $request->zone_id)
                ->where('shift_id', $request->shift_id)
                ->where('status', 'active')
                ->first();

            if ($existingZoneShift) {
                return response()->json([
                    'message' => 'Advertencia: Ya existe un grupo activo para esta zona y turno.',
                    'warning' => 'Zona y turno ya asignados',
                    'existing_group' => $existingZoneShift->name
                ], 422);
            }

            // Crear el grupo
            $group = EmployeeGroup::create([
                'name' => $request->name,
                'days' => implode(',', $request->days),
                'zone_id' => $request->zone_id,
                'shift_id' => $request->shift_id,
                'vehicle_id' => $request->vehicle_id,
                'driver_id' => $request->driver_id,
                'assistant1_id' => $request->assistant1_id,
                'assistant2_id' => $request->assistant2_id,
                'assistant3_id' => $request->assistant3_id,
                'assistant4_id' => $request->assistant4_id,
                'assistant5_id' => $request->assistant5_id,
                'status' => 'active'
            ]);

            // Registrar en configgroups
            $this->syncConfigGroups($group, $request);

            // Registrar en zone_shift
            $this->syncZoneShift($request->zone_id, $request->shift_id);

            DB::commit();

            return response()->json(['message' => 'Grupo de personal creado exitosamente.'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el grupo.', 'error' => $th->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $group = EmployeeGroup::with([
                'driver', 
                'assistant1', 'assistant2', 'assistant3', 'assistant4', 'assistant5',
                'vehicle',
                'zone',
                'shift'
            ])->findOrFail($id);
            
            $zones = Zone::all();
            $shifts = Shift::all();
            $vehicles = Vehicle::all();
            $employees = Employee::where('estado', 'activo')->get();
            
            $isEdit = true;
            
            return view('admin.employeegroups.edit', compact('group', 'zones', 'shifts', 'vehicles', 'employees', 'isEdit'));
        } catch (\Exception $e) {
            abort(404, 'Grupo no encontrado');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();

        try {
            $group = EmployeeGroup::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'days' => 'required|array',
                'zone_id' => 'required|exists:zones,id',
                'shift_id' => 'required|exists:shifts,id',
                'driver_id' => 'nullable|exists:employees,id',
                'assistant1_id' => 'nullable|exists:employees,id',
                'assistant2_id' => 'nullable|exists:employees,id',
                'assistant3_id' => 'nullable|exists:employees,id',
                'assistant4_id' => 'nullable|exists:employees,id',
                'assistant5_id' => 'nullable|exists:employees,id',
            ]);

            // Verificar si ya existe otro grupo activo para esta zona y turno (excluyendo el actual)
            $existingZoneShift = EmployeeGroup::where('zone_id', $request->zone_id)
                ->where('shift_id', $request->shift_id)
                ->where('status', 'active')
                ->where('id', '!=', $id)
                ->first();

            if ($existingZoneShift) {
                return response()->json([
                    'message' => 'Advertencia: Ya existe otro grupo activo para esta zona y turno.',
                    'warning' => 'Zona y turno ya asignados',
                    'existing_group' => $existingZoneShift->name
                ], 422);
            }

            $group->update([
                'name' => $request->name,
                'days' => implode(',', $request->days),
                'zone_id' => $request->zone_id,
                'shift_id' => $request->shift_id,
                'driver_id' => $request->driver_id,
                'assistant1_id' => $request->assistant1_id,
                'assistant2_id' => $request->assistant2_id,
                'assistant3_id' => $request->assistant3_id,
                'assistant4_id' => $request->assistant4_id,
                'assistant5_id' => $request->assistant5_id,
            ]);

            // Sincronizar configgroups
            $this->syncConfigGroups($group, $request);

            // Sincronizar zone_shift
            $this->syncZoneShift($request->zone_id, $request->shift_id);

            DB::commit();

            return response()->json(['message' => 'Grupo actualizado exitosamente.'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar el grupo.', 'error' => $th->getMessage()], 500);
        }
    }

    /**
     * Sincronizar configgroups
     */
    private function syncConfigGroups(EmployeeGroup $group, Request $request)
    {
        // Eliminar registros existentes
        ConfigGroup::where('employeegroup_id', $group->id)->delete();

        $configGroups = [];

        // Registrar conductor si existe
        if ($request->driver_id) {
            $configGroups[] = [
                'employeegroup_id' => $group->id,
                'employee_id' => $request->driver_id,
                'role' => 'conductor',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Registrar ayudantes si existen
        for ($i = 1; $i <= 5; $i++) {
            $assistantId = $request->{"assistant{$i}_id"};
            if ($assistantId) {
                $configGroups[] = [
                    'employeegroup_id' => $group->id,
                    'employee_id' => $assistantId,
                    'role' => 'ayudante',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insertar todos los registros
        if (!empty($configGroups)) {
            ConfigGroup::insert($configGroups);
        }
    }

    /**
     * Sincronizar zone_shift
     */
    private function syncZoneShift($zoneId, $shiftId)
    {
        // Verificar si ya existe la relación
        $existingZoneShift = ZoneShift::where('zone_id', $zoneId)
            ->where('shift_id', $shiftId)
            ->first();

        if (!$existingZoneShift) {
            ZoneShift::create([
                'zone_id' => $zoneId,
                'shift_id' => $shiftId,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $group = EmployeeGroup::findOrFail($id);
            
            // Eliminar registros relacionados en configgroups
            ConfigGroup::where('employeegroup_id', $group->id)->delete();
            
            $group->delete();

            DB::commit();

            return response()->json(['message' => 'Grupo eliminado exitosamente.'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Error al eliminar el grupo.', 'error' => $th->getMessage()], 500);
        }
    }

    /**
     * Buscar empleados
     */
    public function searchEmployees(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $type = $request->get('type', '');

            $query = Employee::where('estado', 'activo');

            // Filtrar por tipo si se especifica
            if ($type == '1') {
                $query->where('employeetype_id', 1); // Conductores
            } elseif ($type == '2') {
                $query->where('employeetype_id', 2); // Asistentes
            }

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('last_name', 'LIKE', "%{$search}%")
                      ->orWhere('dni', 'LIKE', "%{$search}%")
                      ->orWhereRaw("CONCAT(name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            }

            $employees = $query->limit(10)
                ->get()
                ->map(function($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->name,
                        'last_name' => $employee->last_name,
                        'dni' => $employee->dni,
                        'position' => $employee->employeeType->name ?? 'N/A'
                    ];
                });

            return response()->json($employees);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Verificar disponibilidad de zona y turno
     */
    public function checkZoneShiftAvailability(Request $request)
    {
        try {
            $zoneId = $request->get('zone_id');
            $shiftId = $request->get('shift_id');
            $currentGroupId = $request->get('current_group_id'); // Para edición

            if (!$zoneId || !$shiftId) {
                return response()->json(['available' => true]);
            }

            $query = EmployeeGroup::where('zone_id', $zoneId)
                ->where('shift_id', $shiftId)
                ->where('status', 'active');

            // Excluir el grupo actual si estamos editando
            if ($currentGroupId) {
                $query->where('id', '!=', $currentGroupId);
            }

            $existingGroup = $query->first();

            if ($existingGroup) {
                return response()->json([
                    'available' => false,
                    'message' => "¡ADVERTENCIA! La zona y turno seleccionados ya están asignados al grupo '{$existingGroup->name}'.",
                    'existing_group' => $existingGroup->name
                ]);
            }

            return response()->json(['available' => true]);

        } catch (\Exception $e) {
            return response()->json(['available' => true]);
        }
    }

    /**
     * Verificar si un empleado está disponible
     */
    public function checkEmployeeAvailability(Request $request)
    {
        try {
            $employeeId = $request->get('employee_id');
            $currentGroupId = $request->get('current_group_id'); // Para edición

            if (!$employeeId) {
                return response()->json(['available' => true]);
            }

            $employee = Employee::find($employeeId);
            if (!$employee) {
                return response()->json(['available' => true]);
            }

            // Buscar si el empleado está asignado a algún grupo activo
            $existingAssignment = EmployeeGroup::where('status', 'active')
                ->where(function($query) use ($employeeId) {
                    $query->where('driver_id', $employeeId)
                          ->orWhere('assistant1_id', $employeeId)
                          ->orWhere('assistant2_id', $employeeId)
                          ->orWhere('assistant3_id', $employeeId)
                          ->orWhere('assistant4_id', $employeeId)
                          ->orWhere('assistant5_id', $employeeId);
                });

            // Excluir el grupo actual si estamos editando
            if ($currentGroupId) {
                $existingAssignment->where('id', '!=', $currentGroupId);
            }

            $existingGroup = $existingAssignment->first();

            if ($existingGroup) {
                $role = $this->getEmployeeRole($employeeId, $existingGroup);
                return response()->json([
                    'available' => false,
                    'message' => "¡ADVERTENCIA! {$employee->name} {$employee->last_name} ya está asignado como {$role} en el grupo '{$existingGroup->name}'.",
                    'existing_group' => $existingGroup->name
                ]);
            }

            return response()->json(['available' => true]);

        } catch (\Exception $e) {
            return response()->json(['available' => true]);
        }
    }

    /**
     * Obtener el rol del empleado en el grupo
     */
    private function getEmployeeRole($employeeId, $group)
    {
        if ($group->driver_id == $employeeId) return 'CONDUCTOR';
        if ($group->assistant1_id == $employeeId) return 'AYUDANTE 1';
        if ($group->assistant2_id == $employeeId) return 'AYUDANTE 2';
        if ($group->assistant3_id == $employeeId) return 'AYUDANTE 3';
        if ($group->assistant4_id == $employeeId) return 'AYUDANTE 4';
        if ($group->assistant5_id == $employeeId) return 'AYUDANTE 5';
        
        return 'EMPLEADO';
    }
}