<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Province;
use App\Models\Department;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $districts = District::with(['province', 'province.department'])->get();
        return view('admin.districts.index', compact('districts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        $provinces = Province::all();
        return view('admin.districts.create', compact('departments', 'provinces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:districts,code',
            'province_id' => 'required|exists:provinces,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        District::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Distrito creado exitosamente'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(District $district)
    {
        return response()->json($district->load(['province', 'province.department']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(District $district)
    {
        $departments = Department::all();
        $provinces = Province::where('department_id', $district->province->department_id)->get();
        return view('admin.districts.edit', compact('district', 'departments', 'provinces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, District $district)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:districts,code,' . $district->id,
            'province_id' => 'required|exists:provinces,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $district->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Distrito actualizado exitosamente'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(District $district)
    {
        // Verificar si el distrito tiene zonas asociadas
        if ($district->zones()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el distrito porque tiene zonas asociadas'
            ], 422);
        }

        $district->delete();

        return response()->json([
            'success' => true,
            'message' => 'Distrito eliminado exitosamente'
        ]);
    }

    /**
     * Obtener distritos por provincia.
     */
    public function getDistrictsByProvince($provinceId)
    {
        $districts = District::where('province_id', $provinceId)->get();
        return response()->json($districts);
    }

    /**
     * Obtener provincias por departamento.
     */
    public function getProvincesByDepartment($departmentId)
    {
        $provinces = Province::where('department_id', $departmentId)->get();
        return response()->json($provinces);
    }
}
