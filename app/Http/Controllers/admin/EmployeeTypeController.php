<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeType;
use Yajra\DataTables\Facades\DataTables;

class EmployeeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $employeetypes = EmployeeType::all();

        if ($request->ajax()) {
            return DataTables::of($employeetypes)
                ->addColumn("edit", function ($employeetype) {
                    return '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $employeetype->id . '"><i class="fas fa-pen"></i></button>';
                })
                ->addColumn("delete", function ($employeetype) {
                    return '<form action="' . route('admin.employeetypes.destroy', $employeetype) . '" method="POST" class="frmDelete">' .
                        csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i></button></form>';
                })
                ->rawColumns(['edit', 'delete'])
                ->make(true);
        } else {
            return view('admin.employeetypes.index', compact('employeetypes'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.employeetypes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validación de los campos
            $request->validate([
                'name' => 'required|unique:employeetype|max:100',
                'description' => 'nullable'
            ]);

            // Crear el nuevo tipo de empleado
            EmployeeType::create([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Tipo de empleado creado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de creación del tipo de empleado.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $employeetype = EmployeeType::find($id);
        return view('admin.employeetypes.edit', compact('employeetype'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $employeetype = EmployeeType::find($id);

            if (!$employeetype) {
                return response()->json(['message' => 'Tipo de empleado no encontrado.'], 404);
            }

            // Validación de los campos
            $request->validate([
                'name' => 'required|unique:employeetype,name,' . $employeetype->id . '|max:100',
                'description' => 'nullable'
            ]);

            // Actualizar el tipo de empleado
            $employeetype->update([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Tipo de empleado actualizado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de actualización del tipo de empleado.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $employeetype = EmployeeType::find($id);
            
            if (!$employeetype) {
                return response()->json(['message' => 'Tipo de empleado no encontrado.'], 404);
            }

            // Verificar si hay empleados asociados
            if ($employeetype->employees()->count() > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar el tipo de empleado porque tiene empleados asociados.'
                ], 422);
            }

            $employeetype->delete();

            return response()->json(['message' => 'Tipo de empleado eliminado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de eliminación del tipo de empleado.'], 500);
        }
    }
}