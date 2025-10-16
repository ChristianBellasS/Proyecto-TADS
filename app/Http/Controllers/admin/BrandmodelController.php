<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BrandModel;
use App\Models\Brand;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class BrandModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $models = BrandModel::with('brand')->get();

        if ($request->ajax()) {
            return DataTables::of($models)
                ->addColumn("brand", function ($model) {
                    return $model->brand->name ?? 'N/A';
                })
                ->addColumn("edit", function ($model) {
                    return '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $model->id . '"><i class="fas fa-pen"></i></button>';
                })
                ->addColumn("delete", function ($model) {
                    return '<form action="' . route('admin.brandmodels.destroy', $model->id) . '" method="POST" class="frmDelete">' . 
                        csrf_field() . method_field('DELETE') . 
                        '<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>';
                })
                ->rawColumns(['edit', 'delete'])
                ->make(true);
        } else {
            return view('admin.brandmodels.index', compact('models'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::all();
        return view('admin.brandmodels.create', compact('brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validar los campos de la solicitud
            $request->validate([
                'name' => 'required|string|max:100',
                'code' => 'required|string|max:100|unique:brandmodels',
                'description' => 'nullable|string',
                'brand_id' => 'required|exists:brands,id'
            ]);

            // Crear el nuevo BrandModel
            BrandModel::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'brand_id' => $request->brand_id
            ]);

            return response()->json(['message' => 'Modelo creado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al crear el modelo.', 'error' => $th->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model = BrandModel::find($id);
        $brands = Brand::all();
        return view('admin.brandmodels.edit', compact('model', 'brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $model = BrandModel::find($id);

            // Validar que el modelo exista
            if (!$model) {
                return response()->json(['message' => 'Modelo no encontrado.'], 404);
            }

            // Validar los campos de la solicitud
            $request->validate([
                'name' => 'required|string|max:100',
                'code' => 'required|string|max:100|unique:brandmodels,code,' . $model->id,
                'description' => 'nullable|string',
                'brand_id' => 'required|exists:brands,id'
            ]);

            // Actualizar el modelo
            $model->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'brand_id' => $request->brand_id
            ]);

            return response()->json(['message' => 'Modelo actualizado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar el modelo.', 'error' => $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $model = BrandModel::find($id);

            // Verificar si el modelo existe
            if (!$model) {
                return response()->json(['message' => 'Modelo no encontrado.'], 404);
            }

            // Eliminar el modelo
            $model->delete();

            return response()->json(['message' => 'Modelo eliminado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al eliminar el modelo.', 'error' => $th->getMessage()], 500);
        }
    }
}