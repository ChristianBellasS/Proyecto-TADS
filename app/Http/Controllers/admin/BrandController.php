<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand; // Importa el modelo Brand
use Illuminate\Support\Facades\Storage;  // Importa Storage aquí
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;


class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $brands = Brand::all();

        if ($request->ajax()) {

            return DataTables::of($brands)
                ->addColumn("logo", function ($brand) {
                    return '<img src="' . ($brand->logo == '' ? asset('/storage/brand_logo/no_logo.png') : asset($brand->logo)) . '"
                                    width="70px" height="50px">';
                })
                ->addColumn("edit", function ($brand) {
    return '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $brand->id . '"><i class="fas fa-pen"></i></button>';
                })
                ->addColumn("delete", function ($brand) {
                    return '<form action="' . route('admin.brands.destroy', $brand) . '" method="POST" class="frmDelete">' .
                        csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i></button></form>';
                })
                ->rawColumns(['logo', 'edit', 'delete'])
                ->make(true);
        } else {
            return view('admin.brands.index', compact('brands'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $logo = "";

        // Validación de los campos
        $request->validate([
            'name' => 'required|unique:brands'
        ]);

        // Verificar si se subió una imagen de logo
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $image = $request->file('logo')->store('public/brand_logo');
            $logo = Storage::url($image);  // Obtiene la URL pública del archivo
        }

        // Crear la nueva marca
        Brand::create([  // Aquí usamos el modelo Brand
            'name' => $request->name,
            'description' => $request->description,
            'logo' => $logo
        ]);

        return response()->json(['message' => 'Marca creada exitosamente.'], 200);
        } catch (\Throwable $th) {
        return response()->json(['message' => 'Error en el proceso de creación de la marca.'.$th->getMessage()], 500);
        }

        //return redirect()->route('admin.brands.index')->with('action', 'Marca creada exitosamente.');
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
        $brand = Brand::find($id);
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, string $id)
{
    try {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['message' => 'Marca no encontrada.'], 404);
        }

        // Validación de los campos
        $request->validate([
            'name' => 'required|unique:brands,name,' . $brand->id,
            'description' => 'required',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,gif,webp'
        ]);

        $logo = $brand->logo; // Mantener la imagen anterior por defecto

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            if ($logo && Storage::disk('public')->exists(str_replace('/storage', 'public', $logo))) {
                Storage::disk('public')->delete(str_replace('/storage', 'public', $logo));
            }

            $image = $request->file('logo')->store('public/brand_logo');
            $logo = Storage::url($image);
        }

        // Actualizar la marca con los nuevos datos
        $brand->update([
            'name' => $request->name,
            'description' => $request->description,
            'logo' => $logo
        ]);

        return response()->json(['message' => 'Marca actualizada exitosamente.'], 200);
    } catch (\Throwable $th) {
        // Log de error para facilitar la depuración
        Log::error('Error en la actualización de marca: ' . $th->getMessage());
        return response()->json(['message' => 'Error en el proceso de actualización de la marca.'], 500);
    }

        //return redirect()->route('admin.brands.index')->with('action', 'Marca actualizada exitosamente.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::find($id);
        $brand->delete();
        return redirect()->route('admin.brands.index')->with('action', 'Marca eliminada exitosamente.');
    }
}
