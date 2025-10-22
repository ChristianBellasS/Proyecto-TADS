<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\Department;
use App\Models\EmployeeType;
use Yajra\DataTables\Facades\DataTables;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $contracts = Contract::with(['employee', 'position', 'department'])->get();

        if ($request->ajax()) {
            return DataTables::of($contracts)
                ->addColumn("employee_name", function ($contract) {
                    return $contract->employee->full_name;
                })
                ->addColumn("department_name", function ($contract) {
                    return $contract->department->name ?? 'N/A';
                })
                ->addColumn("position_name", function ($contract) {
                    return $contract->position->name ?? 'N/A';
                })
                ->addColumn("salary_formatted", function ($contract) {
                    return 'S/ ' . number_format($contract->salary, 2);
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
        } else {
            return view('admin.contracts.index', compact('contracts'));
        }
    }

    public function create()
    {
        $employees = [];
        $positions = EmployeeType::all();
        $departments = [];

        return view('admin.contracts.create', compact('employees', 'positions', 'departments'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'contract_type' => 'required|string|max:100',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'salary' => 'required|numeric|min:0',
                'position_id' => 'required|exists:employeetype,id',
                'department_id' => 'required|exists:departments,id',
                'vacation_days_per_year' => 'required|integer|min:0',
                'probation_period_months' => 'required|integer|min:0',
                'is_active' => 'required|boolean',
                'termination_reason' => 'nullable|string',
            ]);

            Contract::create($validated);

            return response()->json(['message' => 'Contrato creado correctamente.'], 200);
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
        $employees = Employee::where('estado', 'activo')
                             ->where('employeetype_id', $contract->position_id)
                             ->get();
        $positions = EmployeeType::all();
        $departments = Department::all();

        return view('admin.contracts.edit', compact('contract', 'employees', 'positions', 'departments'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $contract = Contract::findOrFail($id);

            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'contract_type' => 'required|string|max:100',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'salary' => 'required|numeric|min:0',
                'position_id' => 'required|exists:employeetype,id',
                'department_id' => 'required|exists:departments,id',
                'vacation_days_per_year' => 'required|integer|min:0',
                'probation_period_months' => 'required|integer|min:0',
                'is_active' => 'required|boolean',
                'termination_reason' => 'nullable|string',
            ]);

            $contract->update($validated);

            return response()->json(['message' => 'Contrato actualizado correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de actualización del contrato: ' . $th->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $contract = Contract::findOrFail($id);
            
            // En lugar de eliminar, solo desactivar el contrato
            $contract->update(['is_active' => false]);

            return response()->json(['message' => 'Contrato desactivado correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de desactivación del contrato: ' . $th->getMessage()], 500);
        }
    }


    public function getEmployeesByPosition(Request $request)
    {
        $positionId = $request->get('position_id');
        
        $employees = Employee::where('estado', 'activo')
                             ->where('employeetype_id', $positionId)
                             ->get(['id', 'name', 'last_name'])
                             ->map(function($employee) {
                                 return [
                                     'id' => $employee->id,
                                     'full_name' => $employee->full_name
                                 ];
                             });
        
        return response()->json($employees);
    }

    public function getDepartments()
    {
        $departments = Department::all(['id', 'name']);
        return response()->json($departments);
    }
}