<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BrandModel; // Importa el modelo BrandModel
use App\Models\Brand; // Importa el modelo Brand
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;  // Importa Storage aquí
use Illuminate\Support\Facades\Log;

class BrandmodelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $models = BrandModel::select(
            'brandmodels.id',
            'brandmodels.name as name',
            'b.name as brandname',
            'brandmodels.description as description',
            'brandmodels.code as code',
            'brandmodels.created_at as created_at',
            'brandmodels.updated_at as updated_at'
        )
        ->join('brands as b', 'brandmodels.brand_id', '=', 'b.id');

        if ($request->ajax()) {
            return DataTables::of($models)
                ->addColumn("edit", function ($model) {
                    return '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $model->id . '"><i class="fas fa-pen"></i></button>';
                })
                ->addColumn("delete", function ($model) {
                    return '<form action="' . route('admin.models.destroy', $model->id) . '" method="POST" class="frmDelete">' . 
                        csrf_field() . method_field('DELETE') . 
                        '<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>';
                })
                ->rawColumns(['logo', 'edit', 'delete'])
                ->make(true);
        } else {
            return view('admin.models.index', compact('models'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::all()->pluck('name', 'id');
        return view('admin.models.create', compact('brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validar los campos de la solicitud
            $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'brand_id' => 'required|exists:brands,id'
            ]);

            // Crear el nuevo BrandModel
            BrandModel::create($request->all());

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
        $brands = Brand::all()->pluck('name', 'id');
        return view('admin.models.edit', compact('model', 'brands'));
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
                'name' => 'required|string',
                'description' => 'nullable|string',
                'brand_id' => 'required|exists:brands,id'
            ]);

            // Actualizar el modelo
            $model->update($request->all());

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
