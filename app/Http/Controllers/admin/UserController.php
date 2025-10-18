<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserType;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::with('userType')->get();

        if ($request->ajax()) {
            return DataTables::of($users)
                ->addColumn("profile_photo", function ($user) {
                    // Verificar si existe la foto en profile_photos
                    if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                        $photoUrl = asset('storage/' . $user->profile_photo_path);
                    } else {
                        $photoUrl = asset('storage/profile_photos/no_logo.png');
                    }
                    
                    return '<img src="' . $photoUrl . '" width="50" height="50" class="rounded-circle" style="object-fit: cover;">';
                })
                ->addColumn("user_type", function ($user) {
                    return $user->userType->name ?? 'N/A';
                })
                ->addColumn("estado", function ($user) {
                    $badge = $user->estado == 'activo' 
                        ? '<span class="badge badge-success">Activo</span>'
                        : '<span class="badge badge-danger">Inactivo</span>';
                    return $badge;
                })
                ->addColumn("edit", function ($user) {
                    return '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $user->id . '"><i class="fas fa-pen"></i></button>';
                })
                ->addColumn("delete", function ($user) {
                    return '<form action="' . route('admin.users.destroy', $user) . '" method="POST" class="frmDelete">' .
                        csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i></button></form>';
                })
                ->rawColumns(['profile_photo', 'estado', 'edit', 'delete'])
                ->make(true);
        } else {
            return view('admin.users.index', compact('users'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $usertypes = UserType::all();
        return view('admin.users.create', compact('usertypes'));
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
                'dni' => 'required|string|unique:users|max:8',
                'birthdate' => 'required|date|before:-18 years',
                'license' => 'nullable|string|max:255',
                'address' => 'required|string|min:10|max:255',
                'telefono' => 'nullable|string|max:15', // VALIDACIÓN AGREGADA
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'estado' => 'required|in:activo,inactivo',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'usertype_id' => 'required|exists:usertypes,id'
            ]);

            // Preparar los datos del usuario
            $userData = [
                'name' => $request->name,
                'last_name' => $request->last_name,
                'dni' => $request->dni,
                'birthdate' => $request->birthdate,
                'license' => $request->license,
                'address' => $request->address,
                'telefono' => $request->telefono, // CAMPO AGREGADO
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'estado' => $request->estado,
                'usertype_id' => $request->usertype_id,
            ];

            // Manejar la foto de perfil antes de crear el usuario
            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $userData['profile_photo_path'] = $path;
            }

            $user = User::create($userData);

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
        $user = User::find($id);
        $usertypes = UserType::all();
        return view('admin.users.edit', compact('user', 'usertypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'Empleado no encontrado.'], 404);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'dni' => 'required|string|max:8|unique:users,dni,' . $user->id,
                'birthdate' => 'required|date|before:-18 years',
                'license' => 'nullable|string|max:255',
                'address' => 'required|string|min:10|max:255',
                'telefono' => 'nullable|string|max:15', // VALIDACIÓN AGREGADA
                'email' => 'required|email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:6',
                'estado' => 'required|in:activo,inactivo',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'usertype_id' => 'required|exists:usertypes,id'
            ]);

            $updateData = [
                'name' => $request->name,
                'last_name' => $request->last_name,
                'dni' => $request->dni,
                'birthdate' => $request->birthdate,
                'license' => $request->license,
                'address' => $request->address,
                'telefono' => $request->telefono, // CAMPO AGREGADO
                'email' => $request->email,
                'estado' => $request->estado,
                'usertype_id' => $request->usertype_id,
            ];

            if ($request->password) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Manejar la foto de perfil
            if ($request->hasFile('profile_photo')) {
                // Eliminar la foto anterior si existe
                $this->deleteProfilePhoto($user);
                
                // Guardar la nueva foto
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $updateData['profile_photo_path'] = $path;
            }

            $user->update($updateData);

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
            $user = User::find($id);
            
            if (!$user) {
                return response()->json(['message' => 'Empleado no encontrado.'], 404);
            }

            // Eliminar la foto de perfil si existe
            $this->deleteProfilePhoto($user);
            
            $user->delete();

            return response()->json(['message' => 'Empleado eliminado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de eliminación del empleado: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Eliminar la foto de perfil
     */
    private function deleteProfilePhoto($user)
    {
        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }
    }
}