<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Color;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $colors = Color::all();

        if ($request->ajax()) {
            return DataTables::of($colors)
                ->addColumn("color_preview", function ($color) {
                    return '<div style="width: 50px; height: 30px; background-color: ' . $color->code . '; border: 1px solid #ccc; border-radius: 4px;"></div>';
                })
                ->addColumn("edit", function ($color) {
                    return '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $color->id . '"><i class="fas fa-pen"></i></button>';
                })
                ->addColumn("delete", function ($color) {
                    return '<form action="' . route('admin.colors.destroy', $color) . '" method="POST" class="frmDelete">' .
                        csrf_field() . method_field('DELETE') . '<button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i></button></form>';
                })
                ->rawColumns(['color_preview', 'edit', 'delete'])
                ->make(true);
        } else {
            return view('admin.colors.index', compact('colors'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.colors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validación de los campos
            $request->validate([
                'name' => 'required|unique:colors',
                'code' => 'required|regex:/^#[a-fA-F0-9]{6}$/'
            ]);

            // Crear el nuevo color
            Color::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Color creado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de creación del color. '.$th->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $color = Color::find($id);
        return view('admin.colors.edit', compact('color'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $color = Color::find($id);

            if (!$color) {
                return response()->json(['message' => 'Color no encontrado.'], 404);
            }

            // Validación de los campos
            $request->validate([
                'name' => 'required|unique:colors,name,' . $color->id,
                'code' => 'required|regex:/^#[a-fA-F0-9]{6}$/',
                'description' => 'nullable'
            ]);

            // Actualizar el color
            $color->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Color actualizado exitosamente.'], 200);
        } catch (\Throwable $th) {
            Log::error('Error en la actualización de color: ' . $th->getMessage());
            return response()->json(['message' => 'Error en el proceso de actualización del color.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $color = Color::find($id);
        $color->delete();
        return redirect()->route('admin.colors.index')->with('action', 'Color eliminado exitosamente.');
    }
}