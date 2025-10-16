<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleType;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class VehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vehicleTypes = VehicleType::all();

        if ($request->ajax()) {
            return DataTables::of($vehicleTypes)
                ->addColumn("edit", function ($vehicleType) {
                    return '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $vehicleType->id . '"><i class="fas fa-pen"></i></button>';
                })
                ->addColumn("delete", function ($vehicleType) {
                    return '<form action="' . route('admin.vehicletypes.destroy', $vehicleType) . '" method="POST" class="frmDelete">' .
                        csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i></button></form>';
                })
                ->rawColumns(['edit', 'delete'])
                ->make(true);
        } else {
            return view('admin.vehicletypes.index', compact('vehicleTypes'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.vehicletypes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validación de los campos
            $request->validate([
                'name' => 'required|unique:vehicletypes'
            ]);

            // Crear el nuevo tipo de vehículo
            VehicleType::create([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Tipo de vehículo creado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de creación del tipo de vehículo. '.$th->getMessage()], 500);
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
        $vehicleType = VehicleType::find($id);
        return view('admin.vehicletypes.edit', compact('vehicleType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $vehicleType = VehicleType::find($id);

            if (!$vehicleType) {
                return response()->json(['message' => 'Tipo de vehículo no encontrado.'], 404);
            }

            // Validación de los campos
            $request->validate([
                'name' => 'required|unique:vehicletypes,name,' . $vehicleType->id,
                'description' => 'nullable'
            ]);

            // Actualizar el tipo de vehículo
            $vehicleType->update([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Tipo de vehículo actualizado exitosamente.'], 200);
        } catch (\Throwable $th) {
            Log::error('Error en la actualización de tipo de vehículo: ' . $th->getMessage());
            return response()->json(['message' => 'Error en el proceso de actualización del tipo de vehículo.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vehicleType = VehicleType::find($id);
        $vehicleType->delete();
        return redirect()->route('admin.vehicletypes.index')->with('action', 'Tipo de vehículo eliminado exitosamente.');
    }
}