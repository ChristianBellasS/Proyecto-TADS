<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeGroup;
use App\Models\Zone;
use App\Models\Shift;
use App\Models\Vehicle;
use App\Models\Employee;
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
            \Log::error('Error en EmployeeGroupController@create: ' . $e->getMessage());
            abort(500, 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            $existingGroup = EmployeeGroup::where('vehicle_id', $request->vehicle_id)
                ->where('status', 'active')
                ->first();

            if ($existingGroup) {
                return response()->json([
                    'message' => 'Error: El vehículo ya está asignado a otro grupo activo.',
                    'error' => 'Vehículo no disponible'
                ], 422);
            }

            EmployeeGroup::create([
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

            return response()->json(['message' => 'Grupo de personal creado exitosamente.'], 200);
        } catch (\Throwable $th) {
            \Log::error('Error en EmployeeGroupController@store: ' . $th->getMessage());
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
            
            // En edición, mostrar todos los vehículos pero deshabilitar el cambio
            $vehicles = Vehicle::all();
            
            $employees = Employee::where('estado', 'activo')->get();
            
            return view('admin.employeegroups.edit', compact('group', 'zones', 'shifts', 'vehicles', 'employees'));
        } catch (\Exception $e) {
            \Log::error('Error en EmployeeGroupController@edit: ' . $e->getMessage());
            abort(404, 'Grupo no encontrado');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
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

            return response()->json(['message' => 'Grupo actualizado exitosamente.'], 200);
        } catch (\Throwable $th) {
            \Log::error('Error en EmployeeGroupController@update: ' . $th->getMessage());
            return response()->json(['message' => 'Error al actualizar el grupo.', 'error' => $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $group = EmployeeGroup::findOrFail($id);
            $group->delete();

            return response()->json(['message' => 'Grupo eliminado exitosamente.'], 200);
        } catch (\Throwable $th) {
            \Log::error('Error en EmployeeGroupController@destroy: ' . $th->getMessage());
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
            \Log::error('Error en EmployeeGroupController@searchEmployees: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}