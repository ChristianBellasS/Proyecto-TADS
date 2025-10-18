<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserType;
use Yajra\DataTables\Facades\DataTables;

class UserTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $usertypes = UserType::all();

        if ($request->ajax()) {
            return DataTables::of($usertypes)
                ->addColumn("edit", function ($usertype) {
                    return '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $usertype->id . '"><i class="fas fa-pen"></i></button>';
                })
                ->addColumn("delete", function ($usertype) {
                    return '<form action="' . route('admin.usertypes.destroy', $usertype) . '" method="POST" class="frmDelete">' .
                        csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i></button></form>';
                })
                ->rawColumns(['edit', 'delete'])
                ->make(true);
        } else {
            return view('admin.usertypes.index', compact('usertypes'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.usertypes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validación de los campos
            $request->validate([
                'name' => 'required|unique:usertypes|max:100',
                'description' => 'nullable'
            ]);

            // Crear el nuevo tipo de usuario
            UserType::create([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Tipo de usuario creado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de creación del tipo de usuario.'], 500);
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
        $usertype = UserType::find($id);
        return view('admin.usertypes.edit', compact('usertype'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $usertype = UserType::find($id);

            if (!$usertype) {
                return response()->json(['message' => 'Tipo de usuario no encontrado.'], 404);
            }

            // Validación de los campos
            $request->validate([
                'name' => 'required|unique:usertypes,name,' . $usertype->id . '|max:100',
                'description' => 'nullable'
            ]);

            // Actualizar el tipo de usuario
            $usertype->update([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Tipo de usuario actualizado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de actualización del tipo de usuario.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $usertype = UserType::find($id);
            
            if (!$usertype) {
                return response()->json(['message' => 'Tipo de usuario no encontrado.'], 404);
            }

            // Verificar si hay usuarios asociados
            if ($usertype->users()->count() > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar el tipo de usuario porque tiene usuarios asociados.'
                ], 422);
            }

            $usertype->delete();

            return response()->json(['message' => 'Tipo de usuario eliminado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de eliminación del tipo de usuario.'], 500);
        }
    }
}