<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeeType;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $employees = Employee::with('employeeType')->get();

        if ($request->ajax()) {
            return DataTables::of($employees)
                ->addColumn("profile_photo", function ($employee) {
                    // Verificar si existe la foto en profile_photos
                    if ($employee->profile_photo_path && Storage::disk('public')->exists($employee->profile_photo_path)) {
                        $photoUrl = asset('storage/' . $employee->profile_photo_path);
                    } else {
                        $photoUrl = asset('storage/profile_photos/no_logo.png');
                    }
                    
                    return '<img src="' . $photoUrl . '" width="50" height="50" class="rounded-circle" style="object-fit: cover;">';
                })
                ->addColumn("employee_type", function ($employee) {
                    return $employee->employeeType->name ?? 'N/A';
                })
                ->addColumn("estado", function ($employee) {
                    $badge = $employee->estado == 'activo' 
                        ? '<span class="badge badge-success">Activo</span>'
                        : '<span class="badge badge-danger">Inactivo</span>';
                    return $badge;
                })
                ->addColumn("edit", function ($employee) {
                    return '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $employee->id . '"><i class="fas fa-pen"></i></button>';
                })
                ->addColumn("delete", function ($employee) {
                    return '<form action="' . route('admin.employees.destroy', $employee) . '" method="POST" class="frmDelete">' .
                        csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i></button></form>';
                })
                ->rawColumns(['profile_photo', 'estado', 'edit', 'delete'])
                ->make(true);
        } else {
            return view('admin.employees.index', compact('employees'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employeetypes = EmployeeType::all();
        return view('admin.employees.create', compact('employeetypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'dni' => 'required|string|unique:employees|max:8',
                'birthdate' => 'required|date|before:-18 years',
                'license' => 'nullable|string|max:255',
                'address' => 'required|string|min:10|max:255',
                'telefono' => 'nullable|string|max:15',
                'email' => 'required|email|unique:employees',
                'password' => 'required|string|min:6',
                'estado' => 'required|in:activo,inactivo',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'employeetype_id' => 'required|exists:employeetype,id'
            ]);

            // Preparar los datos del empleado
            $employeeData = [
                'name' => $request->name,
                'last_name' => $request->last_name,
                'dni' => $request->dni,
                'birthdate' => $request->birthdate,
                'license' => $request->license,
                'address' => $request->address,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'estado' => $request->estado,
                'employeetype_id' => $request->employeetype_id,
            ];

            // Manejar la foto de perfil antes de crear el empleado
            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $employeeData['profile_photo_path'] = $path;
            }

            $employee = Employee::create($employeeData);

            return response()->json(['message' => 'Empleado creado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de creación del empleado: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $employee = Employee::find($id);
        $employeetypes = EmployeeType::all();
        return view('admin.employees.edit', compact('employee', 'employeetypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json(['message' => 'Empleado no encontrado.'], 404);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'dni' => 'required|string|max:8|unique:employees,dni,' . $employee->id,
                'birthdate' => 'required|date|before:-18 years',
                'license' => 'nullable|string|max:255',
                'address' => 'required|string|min:10|max:255',
                'telefono' => 'nullable|string|max:15',
                'email' => 'required|email|unique:employees,email,' . $employee->id,
                'password' => 'nullable|string|min:6',
                'estado' => 'required|in:activo,inactivo',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'employeetype_id' => 'required|exists:employeetype,id'
            ]);

            $updateData = [
                'name' => $request->name,
                'last_name' => $request->last_name,
                'dni' => $request->dni,
                'birthdate' => $request->birthdate,
                'license' => $request->license,
                'address' => $request->address,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'estado' => $request->estado,
                'employeetype_id' => $request->employeetype_id,
            ];

            if ($request->password) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Manejar la foto de perfil
            if ($request->hasFile('profile_photo')) {
                // Eliminar la foto anterior si existe
                $this->deleteProfilePhoto($employee);
                
                // Guardar la nueva foto
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $updateData['profile_photo_path'] = $path;
            }

            $employee->update($updateData);

            return response()->json(['message' => 'Empleado actualizado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de actualización del empleado: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $employee = Employee::find($id);
            
            if (!$employee) {
                return response()->json(['message' => 'Empleado no encontrado.'], 404);
            }

            // Eliminar la foto de perfil si existe
            $this->deleteProfilePhoto($employee);
            
            $employee->delete();

            return response()->json(['message' => 'Empleado eliminado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de eliminación del empleado: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Eliminar la foto de perfil
     */
    private function deleteProfilePhoto($employee)
    {
        if ($employee->profile_photo_path && Storage::disk('public')->exists($employee->profile_photo_path)) {
            Storage::disk('public')->delete($employee->profile_photo_path);
        }
    }
}