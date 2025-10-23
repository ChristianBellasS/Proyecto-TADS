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
                'termination_reason' => 'nullable|string',
            ]);

            // Validar restricción de contratos temporales
            if ($validated['contract_type'] === 'Temporal') {
                $lastContract = Contract::where('employee_id', $validated['employee_id'])
                    ->where('contract_type', 'Temporal')
                    ->orderBy('end_date', 'desc')
                    ->first();

                if ($lastContract && $lastContract->end_date) {
                    $endDate = Carbon::parse($lastContract->end_date);
                    $minStartDate = $endDate->copy()->addMonths(2);
                    $requestedStartDate = Carbon::parse($validated['start_date']);

                    if ($requestedStartDate->lt($minStartDate)) {
                        return response()->json([
                            'message' => 'No se puede crear un contrato temporal. Debe esperar al menos 2 meses desde la finalización del último contrato temporal.',
                            'last_end_date' => $endDate->format('d/m/Y'),
                            'min_start_date' => $minStartDate->format('d/m/Y')
                        ], 422);
                    }
                }

                // Validar que los contratos temporales tengan fecha de fin
                if (!$validated['end_date']) {
                    return response()->json([
                        'message' => 'Los contratos temporales deben tener una fecha de finalización.'
                    ], 422);
                }
            }

            $validated['is_active'] = $request->has('is_active') ? 1 : 0;
            $validated['vacation_days_per_year'] = $validated['vacation_days_per_year'] ?? 0;
            $validated['probation_period_months'] = $validated['probation_period_months'] ?? 0;

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
                'termination_reason' => 'nullable|string',
            ]);

            // Validar restricción de contratos temporales (solo si cambia el tipo o las fechas)
            if ($validated['contract_type'] === 'Temporal') {
                $lastContract = Contract::where('employee_id', $validated['employee_id'])
                    ->where('contract_type', 'Temporal')
                    ->where('id', '!=', $id) // Excluir el contrato actual
                    ->orderBy('end_date', 'desc')
                    ->first();

                if ($lastContract && $lastContract->end_date) {
                    $endDate = Carbon::parse($lastContract->end_date);
                    $minStartDate = $endDate->copy()->addMonths(2);
                    $requestedStartDate = Carbon::parse($validated['start_date']);

                    if ($requestedStartDate->lt($minStartDate)) {
                        return response()->json([
                            'message' => 'No se puede actualizar el contrato temporal. Debe esperar al menos 2 meses desde la finalización del último contrato temporal.',
                            'last_end_date' => $endDate->format('d/m/Y'),
                            'min_start_date' => $minStartDate->format('d/m/Y')
                        ], 422);
                    }
                }

                // Validar que los contratos temporales tengan fecha de fin
                if (!$validated['end_date']) {
                    return response()->json([
                        'message' => 'Los contratos temporales deben tener una fecha de finalización.'
                    ], 422);
                }
            }

            $validated['is_active'] = $request->has('is_active') ? 1 : 0;
            $validated['vacation_days_per_year'] = $validated['vacation_days_per_year'] ?? 0;
            $validated['probation_period_months'] = $validated['probation_period_months'] ?? 0;

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

            $lastContract = Contract::where('employee_id', $employeeId)
                ->where('contract_type', 'Temporal')
                ->orderBy('end_date', 'desc')
                ->first();

            if ($lastContract && $lastContract->end_date) {
                $endDate = Carbon::parse($lastContract->end_date);
                $minStartDate = $endDate->copy()->addMonths(2);
                $now = Carbon::now();

                return response()->json([
                    'has_temporal_contract' => true,
                    'last_end_date' => $endDate->format('Y-m-d'),
                    'min_start_date' => $minStartDate->format('Y-m-d'),
                    'can_create' => $now->gte($minStartDate),
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
}