<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $shifts = Shift::all();
            
            return DataTables::of($shifts)
                ->addColumn("edit", fn($shift) =>
                    '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $shift->id . '"><i class="fa-solid fa-pen-to-square"></i></button>'
                )
                ->addColumn("delete", fn($shift) =>
                    '<form action="' . route('admin.shifts.destroy', $shift) . '" method="POST" class="frmDelete">' .
                    csrf_field() . method_field('DELETE') .
                    '<button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button></form>'
                )
                ->editColumn('created_at', function($shift) {
                    return $shift->created_at->format('d/m/Y H:i');
                })
                ->editColumn('updated_at', function($shift) {
                    return $shift->updated_at->format('d/m/Y H:i');
                })
                ->rawColumns(['edit', 'delete'])
                ->make(true);
        }

        return view('admin.shifts.index');
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:shifts',
                'description' => 'nullable|string',
                'hour_in' => 'required',
                'hour_out' => 'required',
            ]);

            Shift::create([
                'name' => $request->name,
                'description' => $request->description,
                'hour_in' => $request->hour_in,
                'hour_out' => $request->hour_out,
            ]);

            if ($request->ajax()) {
                return response()->json(['message' => 'Turno creado exitosamente.'], 200);
            }

            return redirect()->route('admin.shifts.index')->with('success', 'Turno creado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Error de validación.',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $th) {
            Log::error('Error al crear turno: ' . $th->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['message' => 'Error al crear el turno. ' . $th->getMessage()], 500);
            }

            return back()->with('error', 'Error al crear el turno.');
        }
    }

    public function edit(string $id)
    {
        $shift = Shift::findOrFail($id);
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $shift = Shift::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:shifts,name,' . $shift->id,
                'description' => 'nullable|string',
                'hour_in' => 'required',
                'hour_out' => 'required',
            ]);

            $shift->update([
                'name' => $request->name,
                'description' => $request->description,
                'hour_in' => $request->hour_in,
                'hour_out' => $request->hour_out,
            ]);

            if ($request->ajax()) {
                return response()->json(['message' => 'Turno actualizado exitosamente.'], 200);
            }

            return redirect()->route('admin.shifts.index')->with('success', 'Turno actualizado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Error de validación.',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $th) {
            Log::error('Error al actualizar turno: ' . $th->getMessage());
            
            if ($request->ajax()) {
                return response()->json(['message' => 'Error al actualizar el turno. ' . $th->getMessage()], 500);
            }

            return back()->with('error', 'Error al actualizar el turno.');
        }
    }

    public function destroy(string $id)
    {
        try {
            $shift = Shift::findOrFail($id);
            $shift->delete();

            return response()->json(['message' => 'Turno eliminado exitosamente.'], 200);
        } catch (\Throwable $th) {
            Log::error('Error al eliminar turno: ' . $th->getMessage());
            return response()->json(['message' => 'Error al eliminar el turno.'], 500);
        }
    }
}