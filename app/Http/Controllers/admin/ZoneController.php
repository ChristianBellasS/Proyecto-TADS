<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\District;
use App\Models\ZoneCoord;
use App\Models\Department;


use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index()
    {
        $zones = Zone::with(['district', 'district.province', 'district.province.department'])->get();
        return view('admin.zones.index', compact('zones'));
    }

    public function create()
    {
        $departments = Department::all();
        $districts = District::with('province.department')->get();
        return view('admin.zones.create', compact('departments', 'districts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'district_id' => 'required|exists:districts,id',
            'status' => 'required|in:0,1',
            'coordinates' => 'required|array|min:3',
            'coordinates.*.latitude' => 'required|numeric',
            'coordinates.*.longitude' => 'required|numeric',
        ]);

        $zone = Zone::create([
            'name' => $request->name,
            'description' => $request->description,
            'district_id' => $request->district_id,
            'status' => $request->status
        ]);

        // Guardar coordenadas
        foreach ($request->coordinates as $index => $coord) {
            ZoneCoord::create([
                'zone_id' => $zone->id,
                'latitude' => $coord['latitude'],
                'longitude' => $coord['longitude'],
                'order' => $index
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Zona creada exitosamente'
        ]);
    }

    public function show(Zone $zone)
    {
        return response()->json($zone->load(['coordinates', 'district', 'district.province']));
    }

    public function edit(Zone $zone)
    {
        $departments = Department::all();
        $districts = District::with('province.department')->get();
        $zone->load('coordinates');
        return view('admin.zones.edit', compact('zone', 'departments', 'districts'));
    }

    public function update(Request $request, Zone $zone)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'district_id' => 'required|exists:districts,id',
            'status' => 'required|in:0,1',
            'coordinates' => 'required|array|min:3',
            'coordinates.*.latitude' => 'required|numeric',
            'coordinates.*.longitude' => 'required|numeric',
        ]);

        $zone->update([
            'name' => $request->name,
            'description' => $request->description,
            'district_id' => $request->district_id,
            'status' => $request->status
        ]);

        // Eliminar coordenadas existentes y crear nuevas
        $zone->coordinates()->delete();
        foreach ($request->coordinates as $index => $coord) {
            ZoneCoord::create([
                'zone_id' => $zone->id,
                'latitude' => $coord['latitude'],
                'longitude' => $coord['longitude'],
                'order' => $index
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Zona actualizada exitosamente'
        ]);
    }

    public function destroy(Zone $zone)
    {
        $zone->coordinates()->delete();
        $zone->delete();

        return response()->json([
            'success' => true,
            'message' => 'Zona eliminada exitosamente'
        ]);
    }

    public function getZonesByDistrict($districtId)
    {
        $zones = Zone::where('district_id', $districtId)->get();
        return response()->json($zones);
    }
}
