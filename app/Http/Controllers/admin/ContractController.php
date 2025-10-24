<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\Department;
use App\Models\EmployeeType;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $contracts = Contract::with(['employee', 'position', 'department'])->get();

            return DataTables::of($contracts)
                ->addColumn("employee_name", function ($contract) {
                    return $contract->employee->full_name;
                })
                ->addColumn("contract_type", function ($contract) {
                    return $contract->contract_type;
                })
                ->addColumn("start_date", function ($contract) {
                    return $contract->start_date;
                })
                ->addColumn("end_date", function ($contract) {
                    return $contract->end_date ?? '—';
                })
                ->addColumn("salary", function ($contract) {
                    return 'S/ ' . number_format($contract->salary, 2);
                })
                ->addColumn("department_name", function ($contract) {
                    return $contract->department->name ?? 'N/A';
                })
                ->addColumn("position_name", function ($contract) {
                    return $contract->position->name ?? 'N/A';
                })
                ->addColumn("is_active_badge", function ($contract) {
                    $badge = $contract->is_active
                        ? '<span class="badge badge-success">Activo</span>'
                        : '<span class="badge badge-danger">Inactivo</span>';
                    return $badge;
                })
                ->addColumn("edit", function ($contract) {
                    return '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $contract->id . '"><i class="fa-solid fa-pen-to-square"></i></button>';
                })
                ->addColumn("delete", function ($contract) {
                    return '<form action="' . route('admin.contracts.destroy', $contract) . '" method="POST" class="frmDelete">' .
                        csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fa-solid fa-trash"></i></button></form>';
                })
                ->rawColumns(['is_active_badge', 'edit', 'delete'])
                ->make(true);
        }

        return view('admin.contracts.index');
    }

    public function create()
    {
        return view('admin.contracts.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'position_id' => 'required|exists:employeetype,id',
                'contract_type' => 'required|in:Permanente,Nombrado,Temporal',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'salary' => 'required|numeric|min:0',
                'department_id' => 'required|exists:departments,id',
                'vacation_days_per_year' => 'nullable|integer|min:0',
                'probation_period_months' => 'nullable|integer|min:0',
                'is_active' => 'boolean',
                'termination_reason' => 'nullable|string|required_if:is_active,0',
            ]);

            $validated['is_active'] = $request->has('is_active') ? 1 : 0;
            $validated['vacation_days_per_year'] = $validated['vacation_days_per_year'] ?? 0;
            $validated['probation_period_months'] = $validated['probation_period_months'] ?? 0;

            // CORRECCIÓN: Para contratos Permanentes/Nombrados, forzar end_date como null
            if (in_array($validated['contract_type'], ['Permanente', 'Nombrado'])) {
                $validated['end_date'] = null;
            }

            // Validar que los contratos temporales tengan fecha de fin
            if ($validated['contract_type'] === 'Temporal' && !$validated['end_date']) {
                return response()->json([
                    'message' => 'Contrato temporal incompleto',
                    'details' => 'Los contratos temporales deben tener una fecha de finalización.'
                ], 422);
            }

            // Validar si ya existe un contrato activo para este empleado
            $activeContract = Contract::where('employee_id', $validated['employee_id'])
                ->where('is_active', 1)
                ->first();

            if ($activeContract) {
                return response()->json([
                    'message' => 'Contrato activo existente',
                    'details' => 'No se puede crear un nuevo contrato porque el empleado ya tiene un contrato activo.',
                    'conflicting_contract' => [
                        'type' => $activeContract->contract_type,
                        'start_date' => Carbon::parse($activeContract->start_date)->format('d/m/Y'),
                        'end_date' => $activeContract->end_date ? Carbon::parse($activeContract->end_date)->format('d/m/Y') : 'Permanente',
                        'is_active' => $activeContract->is_active
                    ]
                ], 422);
            }

            // Para contratos TEMPORALES: validar regla de 2 meses desde el último contrato temporal
            if ($validated['contract_type'] === 'Temporal') {
                $temporalValidation = $this->validateTemporalContract($validated);
                if ($temporalValidation !== true) {
                    return response()->json($temporalValidation, 422);
                }
            }

            // Si el contrato está inactivo y no tiene motivo de terminación
            if ($validated['is_active'] == 0 && empty($validated['termination_reason'])) {
                return response()->json([
                    'message' => 'El motivo de terminación es obligatorio cuando el contrato está inactivo.'
                ], 422);
            }

            // Usar transacción para asegurar consistencia
            DB::beginTransaction();

            try {
                // Si el nuevo contrato será activo, desactivar todos los demás contratos del empleado
                if ($validated['is_active'] == 1) {
                    Contract::where('employee_id', $validated['employee_id'])
                        ->where('is_active', 1)
                        ->update([
                            'is_active' => 0,
                            'termination_reason' => 'Contrato reemplazado por uno nuevo'
                        ]);
                }

                // Crear el nuevo contrato
                Contract::create($validated);

                DB::commit();

                return response()->json(['message' => 'Contrato creado correctamente.'], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de creación del contrato: ' . $th->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $contract = Contract::with(['employee', 'position', 'department'])->findOrFail($id);
        return view('admin.contracts.templates.show', compact('contract'));
    }

    public function edit(string $id)
    {
        $contract = Contract::with(['employee', 'position', 'department'])->findOrFail($id);
        return view('admin.contracts.edit', compact('contract'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $contract = Contract::findOrFail($id);

            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'position_id' => 'required|exists:employeetype,id',
                'contract_type' => 'required|in:Permanente,Nombrado,Temporal',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'salary' => 'required|numeric|min:0',
                'department_id' => 'required|exists:departments,id',
                'vacation_days_per_year' => 'nullable|integer|min:0',
                'probation_period_months' => 'nullable|integer|min:0',
                'is_active' => 'boolean',
                'termination_reason' => 'nullable|string|required_if:is_active,0',
            ]);

            $validated['is_active'] = $request->has('is_active') ? 1 : 0;
            $validated['vacation_days_per_year'] = $validated['vacation_days_per_year'] ?? 0;
            $validated['probation_period_months'] = $validated['probation_period_months'] ?? 0;

            // CORRECCIÓN: Para contratos Permanentes/Nombrados, forzar end_date como null
            if (in_array($validated['contract_type'], ['Permanente', 'Nombrado'])) {
                $validated['end_date'] = null;
            }

            // Validar que los contratos temporales tengan fecha de fin
            if ($validated['contract_type'] === 'Temporal' && !$validated['end_date']) {
                return response()->json([
                    'message' => 'Contrato temporal incompleto',
                    'details' => 'Los contratos temporales deben tener una fecha de finalización.'
                ], 422);
            }

            // Validar si ya existe un contrato activo para este empleado (excluyendo el actual)
            $activeContract = Contract::where('employee_id', $validated['employee_id'])
                ->where('id', '!=', $id)
                ->where('is_active', 1)
                ->first();

            if ($activeContract) {
                return response()->json([
                    'message' => 'Contrato activo existente',
                    'details' => 'No se puede activar este contrato porque el empleado ya tiene otro contrato activo.',
                    'conflicting_contract' => [
                        'type' => $activeContract->contract_type,
                        'start_date' => Carbon::parse($activeContract->start_date)->format('d/m/Y'),
                        'end_date' => $activeContract->end_date ? Carbon::parse($activeContract->end_date)->format('d/m/Y') : 'Permanente',
                        'is_active' => $activeContract->is_active
                    ]
                ], 422);
            }

            // Para contratos TEMPORALES: validar regla de 2 meses desde el último contrato temporal
            if ($validated['contract_type'] === 'Temporal') {
                $temporalValidation = $this->validateTemporalContract($validated, $id);
                if ($temporalValidation !== true) {
                    return response()->json($temporalValidation, 422);
                }
            }

            // Si el contrato está inactivo y no tiene motivo de terminación
            if ($validated['is_active'] == 0 && empty($validated['termination_reason'])) {
                return response()->json([
                    'message' => 'El motivo de terminación es obligatorio cuando el contrato está inactivo.'
                ], 422);
            }

            // Usar transacción para asegurar consistencia
            DB::beginTransaction();

            try {
                // Si el contrato actualizado será activo, desactivar todos los demás contratos del empleado
                if ($validated['is_active'] == 1) {
                    Contract::where('employee_id', $validated['employee_id'])
                        ->where('id', '!=', $id) // Excluir el contrato actual
                        ->where('is_active', 1)
                        ->update([
                            'is_active' => 0,
                            'termination_reason' => 'Contrato reemplazado por uno actualizado'
                        ]);
                }

                // Actualizar el contrato
                $contract->update($validated);

                DB::commit();

                return response()->json(['message' => 'Contrato actualizado correctamente.'], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de actualización del contrato: ' . $th->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $contract->update(['is_active' => false]);

            return response()->json(['message' => 'Contrato desactivado correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de desactivación del contrato: ' . $th->getMessage()], 500);
        }
    }

    public function getAllEmployees()
    {
        try {
            $employees = Employee::where('estado', 'activo')
                                 ->get(['id', 'name', 'last_name', 'employeetype_id'])
                                 ->map(function($employee) {
                                     return [
                                         'id' => $employee->id,
                                         'full_name' => $employee->full_name,
                                         'position_id' => $employee->employeetype_id
                                     ];
                                 });

            return response()->json($employees);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al cargar empleados: ' . $th->getMessage()], 500);
        }
    }

    public function getDepartments()
    {
        try {
            $departments = Department::all(['id', 'name']);
            return response()->json($departments);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al cargar departamentos: ' . $th->getMessage()], 500);
        }
    }

    public function checkLastTemporalContract(Request $request)
    {
        try {
            $employeeId = $request->input('employee_id');
            $contractType = $request->input('contract_type');

            // Solo validar para contratos temporales
            if ($contractType !== 'Temporal') {
                return response()->json([
                    'has_temporal_contract' => false,
                    'can_create' => true,
                    'skip_validation' => true
                ]);
            }

            $lastContract = Contract::where('employee_id', $employeeId)
                ->where('contract_type', 'Temporal')
                ->orderBy('end_date', 'desc')
                ->first();

            if ($lastContract && $lastContract->end_date) {
                $endDate = Carbon::parse($lastContract->end_date);
                $minStartDate = $endDate->copy()->addMonths(2);
                $proposedStartDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;

                return response()->json([
                    'has_temporal_contract' => true,
                    'last_end_date' => $endDate->format('Y-m-d'),
                    'min_start_date' => $minStartDate->format('Y-m-d'),
                    'can_create' => $proposedStartDate ? $proposedStartDate->gte($minStartDate) : false,
                    'last_end_date_formatted' => $endDate->format('d/m/Y'),
                    'min_start_date_formatted' => $minStartDate->format('d/m/Y')
                ]);
            }

            return response()->json([
                'has_temporal_contract' => false,
                'can_create' => true
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al verificar contratos: ' . $th->getMessage()], 500);
        }
    }

    public function searchEmployees(Request $request)
    {
        try {
            $search = $request->input('q', '');
            
            if (strlen($search) < 2) {
                return response()->json(['results' => []]);
            }

            $employees = Employee::where('estado', 'activo')
                ->where(function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('dni', 'like', "%{$search}%");
                })
                ->with(['employeeType' => function($query) {
                    $query->select('id', 'name');
                }])
                ->limit(20)
                ->get(['id', 'name', 'last_name', 'dni', 'employeetype_id'])
                ->map(function($employee) {
                    return [
                        'id' => $employee->id,
                        'text' => $employee->name . ' ' . $employee->last_name,
                        'dni' => $employee->dni,
                        'position_name' => $employee->employeeType->name ?? 'N/A',
                        'position_id' => $employee->employeetype_id
                    ];
                });

            return response()->json(['results' => $employees]);
        } catch (\Throwable $th) {
            return response()->json(['results' => []]);
        }
    }

    private function validateTemporalContract($validated, $excludeId = null)
    {
        $employeeId = $validated['employee_id'];
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // Validar restricción de 2 meses entre contratos temporales
        $lastTemporalContract = Contract::where('employee_id', $employeeId)
            ->where('contract_type', 'Temporal')
            ->when($excludeId, function($query) use ($excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->orderBy('end_date', 'desc')
            ->first();

        if ($lastTemporalContract && $lastTemporalContract->end_date) {
            $lastEndDate = Carbon::parse($lastTemporalContract->end_date);
            $minStartDate = $lastEndDate->copy()->addMonths(2);

            if ($startDate->lt($minStartDate)) {
                return [
                    'message' => 'Período de espera mínimo de 2 meses requerido para contrato temporal',
                    'details' => "No se puede crear un contrato temporal. Debe esperar al menos 2 meses desde la finalización del último contrato temporal.",
                    'last_end_date' => $lastEndDate->format('d/m/Y'),
                    'min_start_date' => $minStartDate->format('d/m/Y')
                ];
            }
        }

        return true;
    }

    public function checkEmployeeContracts(Request $request)
    {
        try {
            $employeeId = $request->input('employee_id');
            $contractType = $request->input('contract_type');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $excludeId = $request->input('exclude_id');

            if (!$employeeId) {
                return response()->json([
                    'has_contracts' => false,
                    'has_active_contracts' => false,
                    'contracts' => [],
                    'validation_result' => 'no_employee'
                ]);
            }

            $contracts = Contract::where('employee_id', $employeeId)
                ->when($excludeId, function($query) use ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                })
                ->orderBy('start_date', 'desc')
                ->get(['id', 'contract_type', 'start_date', 'end_date', 'is_active']);

            $activeContracts = $contracts->where('is_active', 1);
            $hasActiveContracts = $activeContracts->isNotEmpty();

            // Validación principal: No permitir crear contrato si ya hay uno activo
            if ($hasActiveContracts) {
                return response()->json([
                    'has_contracts' => true,
                    'has_active_contracts' => true,
                    'contracts' => $contracts->map(function($contract) {
                        $startDate = Carbon::parse($contract->start_date);
                        $endDate = $contract->end_date ? Carbon::parse($contract->end_date) : null;
                        
                        return [
                            'id' => $contract->id,
                            'type' => $contract->contract_type,
                            'start_date' => $startDate->format('d/m/Y'),
                            'end_date' => $endDate ? $endDate->format('d/m/Y') : 'Permanente',
                            'is_active' => $contract->is_active,
                            'status' => $contract->is_active ? 'Activo' : 'Inactivo'
                        ];
                    }),
                    'validation_result' => 'has_active_contract',
                    'message' => 'No se puede crear un nuevo contrato porque el empleado ya tiene un contrato activo.'
                ]);
            }

            // Para contratos TEMPORALES: validar regla de 2 meses
            if ($contractType === 'Temporal' && $startDate) {
                $lastTemporalContract = Contract::where('employee_id', $employeeId)
                    ->where('contract_type', 'Temporal')
                    ->when($excludeId, function($query) use ($excludeId) {
                        $query->where('id', '!=', $excludeId);
                    })
                    ->orderBy('end_date', 'desc')
                    ->first();

                if ($lastTemporalContract && $lastTemporalContract->end_date) {
                    $lastEndDate = Carbon::parse($lastTemporalContract->end_date);
                    $minStartDate = $lastEndDate->copy()->addMonths(2);
                    $proposedStartDate = Carbon::parse($startDate);

                    if ($proposedStartDate->lt($minStartDate)) {
                        return response()->json([
                            'has_contracts' => true,
                            'has_active_contracts' => false,
                            'contracts' => $contracts->map(function($contract) {
                                $startDate = Carbon::parse($contract->start_date);
                                $endDate = $contract->end_date ? Carbon::parse($contract->end_date) : null;
                                
                                return [
                                    'id' => $contract->id,
                                    'type' => $contract->contract_type,
                                    'start_date' => $startDate->format('d/m/Y'),
                                    'end_date' => $endDate ? $endDate->format('d/m/Y') : 'Permanente',
                                    'is_active' => $contract->is_active,
                                    'status' => $contract->is_active ? 'Activo' : 'Inactivo'
                                ];
                            }),
                            'validation_result' => 'temporal_wait_period',
                            'message' => 'Debe esperar al menos 2 meses desde la finalización del último contrato temporal.',
                            'last_end_date' => $lastEndDate->format('d/m/Y'),
                            'min_start_date' => $minStartDate->format('d/m/Y')
                        ]);
                    }
                }
            }

            // Para contratos PERMANENTES/NOMBRADOS: no hay restricción adicional
            return response()->json([
                'has_contracts' => $contracts->isNotEmpty(),
                'has_active_contracts' => false,
                'contracts' => $contracts->map(function($contract) {
                    $startDate = Carbon::parse($contract->start_date);
                    $endDate = $contract->end_date ? Carbon::parse($contract->end_date) : null;
                    
                    return [
                        'id' => $contract->id,
                        'type' => $contract->contract_type,
                        'start_date' => $startDate->format('d/m/Y'),
                        'end_date' => $endDate ? $endDate->format('d/m/Y') : 'Permanente',
                        'is_active' => $contract->is_active,
                        'status' => $contract->is_active ? 'Activo' : 'Inactivo'
                    ];
                }),
                'validation_result' => 'can_create'
            ]);

        } catch (\Throwable $th) {
            \Log::error('Error en checkEmployeeContracts: ' . $th->getMessage(), [
                'employee_id' => $request->input('employee_id'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date')
            ]);
            
            return response()->json([
                'has_contracts' => false,
                'has_active_contracts' => false,
                'contracts' => [],
                'validation_result' => 'error',
                'error' => 'Error al verificar contratos'
            ], 500);
        }
    }
}