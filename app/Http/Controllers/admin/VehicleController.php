<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Brand;
use App\Models\BrandModel;
use App\Models\VehicleType;
use App\Models\Color;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vehicles = Vehicle::with(['brand', 'brandModel', 'type', 'color'])->get();

        if ($request->ajax()) {
            return DataTables::of($vehicles)
                ->addColumn("brand", function ($vehicle) {
                    return $vehicle->brand->name ?? 'N/A';
                })
                ->addColumn("model", function ($vehicle) {
                    return $vehicle->brandModel->name ?? 'N/A';
                })
                ->addColumn("type", function ($vehicle) {
                    return $vehicle->type->name ?? 'N/A';
                })
                ->addColumn("color", function ($vehicle) {
                    $colorCode = $vehicle->color->code ?? '#CCCCCC';
                    $colorName = $vehicle->color->name ?? 'N/A';
                    return '<div style="display: flex; align-items: center;">
                                <div style="width: 20px; height: 20px; background-color: ' . $colorCode . '; border: 1px solid #ccc; border-radius: 3px; margin-right: 8px;"></div>
                                ' . $colorName . '
                            </div>';
                })
                ->addColumn("status_badge", function ($vehicle) {
                    $badge = $vehicle->status == 1 ? 
                        '<span class="badge badge-success">Activo</span>' : 
                        '<span class="badge badge-danger">Inactivo</span>';
                    return $badge;
                })
                ->addColumn("edit", function ($vehicle) {
                    return '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $vehicle->id . '"><i class="fas fa-pen"></i></button>';
                })
                ->addColumn("delete", function ($vehicle) {
                    return '<form action="' . route('admin.vehicles.destroy', $vehicle->id) . '" method="POST" class="frmDelete">' . 
                        csrf_field() . method_field('DELETE') . 
                        '<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>';
                })
                ->rawColumns(['color', 'status_badge', 'edit', 'delete'])
                ->make(true);
        } else {
            return view('admin.vehicles.index', compact('vehicles'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::all();
        $models = BrandModel::all();
        $types = VehicleType::all();
        $colors = Color::all();
        
        return view('admin.vehicles.create', compact('brands', 'models', 'types', 'colors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validación de los campos
            $request->validate([
                'name' => 'required|string|max:100',
                'code' => 'required|string|max:100|unique:vehicles',
                'plate' => 'required|string|max:20|unique:vehicles',
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'load_capacity' => 'required|numeric|min:0',
                'status' => 'required|in:0,1',
                'brand_id' => 'required|exists:brands,id',
                'model_id' => 'required|exists:brandmodels,id',
                'type_id' => 'required|exists:vehicletypes,id',
                'color_id' => 'required|exists:colors,id',
                'description' => 'nullable|string'
            ]);

            // Crear el nuevo vehículo
            Vehicle::create([
                'name' => $request->name,
                'code' => $request->code,
                'plate' => strtoupper($request->plate),
                'year' => $request->year,
                'load_capacity' => $request->load_capacity,
                'description' => $request->description,
                'status' => $request->status,
                'brand_id' => $request->brand_id,
                'model_id' => $request->model_id,
                'type_id' => $request->type_id,
                'color_id' => $request->color_id
            ]);

            return response()->json(['message' => 'Vehículo creado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de creación del vehículo. '.$th->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vehicle = Vehicle::find($id);
        $brands = Brand::all();
        $models = BrandModel::all();
        $types = VehicleType::all();
        $colors = Color::all();
        
        return view('admin.vehicles.edit', compact('vehicle', 'brands', 'models', 'types', 'colors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $vehicle = Vehicle::find($id);

            if (!$vehicle) {
                return response()->json(['message' => 'Vehículo no encontrado.'], 404);
            }

            // Validación de los campos
            $request->validate([
                'name' => 'required|string|max:100',
                'code' => 'required|string|max:100|unique:vehicles,code,' . $vehicle->id,
                'plate' => 'required|string|max:20|unique:vehicles,plate,' . $vehicle->id,
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'load_capacity' => 'required|numeric|min:0',
                'status' => 'required|in:0,1',
                'brand_id' => 'required|exists:brands,id',
                'model_id' => 'required|exists:brandmodels,id',
                'type_id' => 'required|exists:vehicletypes,id',
                'color_id' => 'required|exists:colors,id',
                'description' => 'nullable|string'
            ]);

            // Actualizar el vehículo
            $vehicle->update([
                'name' => $request->name,
                'code' => $request->code,
                'plate' => strtoupper($request->plate),
                'year' => $request->year,
                'load_capacity' => $request->load_capacity,
                'description' => $request->description,
                'status' => $request->status,
                'brand_id' => $request->brand_id,
                'model_id' => $request->model_id,
                'type_id' => $request->type_id,
                'color_id' => $request->color_id
            ]);

            return response()->json(['message' => 'Vehículo actualizado exitosamente.'], 200);
        } catch (\Throwable $th) {
            Log::error('Error en la actualización de vehículo: ' . $th->getMessage());
            return response()->json(['message' => 'Error en el proceso de actualización del vehículo.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vehicle = Vehicle::find($id);
        $vehicle->delete();
        return redirect()->route('admin.vehicles.index')->with('action', 'Vehículo eliminado exitosamente.');
    }
}