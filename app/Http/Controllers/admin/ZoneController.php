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
        // Obtener todas las zonas con sus coordenadas para mostrarlas en el mapa
        $zones = Zone::with('coordinates')->get();
        // Preparar JSON seguro para la vista (lat, lng como float)
        $zonesJson = $zones->map(function($z) {
            return [
                'id' => $z->id,
                'name' => $z->name,
                'coords' => $z->coordinates->map(function($c) {
                    return [(float)$c->latitude, (float)$c->longitude];
                })->toArray()
            ];
        })->toJson();

        return view('admin.zones.create', compact('departments', 'districts', 'zones','zonesJson'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'district_id' => 'required|exists:districts,id',
            'average_waste' => 'nullable|numeric|min:0',
            'status' => 'required|in:0,1',
            'coordinates' => 'required|array|min:3',
            'coordinates.*.latitude' => 'required|numeric',
            'coordinates.*.longitude' => 'required|numeric',
        ]);

        $zone = Zone::create([
            'name' => $request->name,
            'description' => $request->description,
            'district_id' => $request->district_id,
            'average_waste' => $request->average_waste,
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
            'message' => 'Zona creada exitosamente',
            'zone_id' => $zone->id
        ]);
    }

    public function show(Zone $zone)
    {
        return response()->json($zone->load(['coordinates', 'district', 'district.province', 'district.province.department']));
    }

    public function edit(Zone $zone)
    {
        $departments = Department::all();
        $districts = District::with('province.department')->get();
        $zone->load('coordinates');
        // Obtener todas las zonas con sus coordenadas para mostrarlas en el mapa
        // 🔹 Cargar la zona actual con coordenadas
        $zone->load('coordinates');
        // 🔹 Cargar otras zonas para mostrarlas en el mapa
        $zones = Zone::with('coordinates')
        ->where('id', '!=', $zone->id)
        ->get();

        $zonesJson = $zones->map(function($z) {
            return [
                'id' => $z->id,
                'name' => $z->name,
                'coords' => $z->coordinates->map(function($c) {
                    return [(float)$c->latitude, (float)$c->longitude];
                })->toArray()
            ];
        })->toJson();

        return view('admin.zones.edit', compact('zone', 'departments', 'districts', 'zones', 'zonesJson'));
    }

    public function update(Request $request, Zone $zone)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'district_id' => 'required|exists:districts,id',
            'average_waste' => 'nullable|numeric|min:0',
            'status' => 'required|in:0,1',
            'coordinates' => 'required|array|min:3',
            'coordinates.*.latitude' => 'required|numeric',
            'coordinates.*.longitude' => 'required|numeric',
        ]);

        $zone->update([
            'name' => $request->name,
            'description' => $request->description,
            'district_id' => $request->district_id,
            'average_waste' => $request->average_waste,
            'status' => $request->status
        ]);

        // Eliminar coordenadas existentes y crear nuevas
        $zone->coordinates()->delete();
        foreach ($request->coordinates as $index => $coord) {
            ZoneCoord::create([
                'zone_id' => $zone->id,
                'latitude' => $coord['latitude'],
                'longitude' => $coord['longitude'],
                // 'order' => $indexPrimero, desde tu controlador de Laravel, cuando cargues el formulario, necesitas pasar los polígonos existentes (todas las zonas, menos la actual si estás editando una).
                'order' => $index, // ✅ Este es el correcto


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
        try {
            // Cargar el distrito con sus datos de ubicación
            $district = District::with('province.department')->find($districtId);
            $zones = Zone::with(['coordinates', 'district.province.department'])
                ->where('district_id', $districtId)
                ->where('status', true)
                ->get();

            // Formatear la respuesta
            $formattedZones = $zones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'description' => $zone->description,
                    'status' => $zone->status,
                    'coordinates' => $zone->coordinates->map(function ($coord) {
                        return [
                            'latitude' => (float) $coord->latitude,
                            'longitude' => (float) $coord->longitude,
                            'order' => $coord->order
                        ];
                    })->toArray()
                ];
            });

            return response()->json([
                'zones' => $formattedZones,
                'district' => $district ? [
                    'id' => $district->id,
                    'name' => $district->name,
                    'province' => $district->province->name ?? '',
                    'department' => $district->province->department->name ?? ''
                ] : null
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Vista del mapa general de zonas con filtros
     */
    public function viewMap()
    {
        $departments = Department::all();
        return view('admin.zones.map', compact('departments'));
    }

    /**
     * API: Devuelve las zonas (con coordenadas) según los filtros seleccionados
     */
    public function getZonesMapData(Request $request)
    {
        $query = Zone::with(['coordinates', 'district.province.department']);

        // Filtros jerárquicos
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        } elseif ($request->filled('province_id')) {
            $query->whereHas('district.province', function ($q) use ($request) {
                $q->where('id', $request->province_id);
            });
        } elseif ($request->filled('department_id')) {
            $query->whereHas('district.province.department', function ($q) use ($request) {
                $q->where('id', $request->department_id);
            });
        }

        $zones = $query->get()->map(function ($zone) {
            return [
                'id' => $zone->id,
                'name' => $zone->name,
                'district' => $zone->district->name ?? '',
                'province' => $zone->district->province->name ?? '',
                'department' => $zone->district->province->department->name ?? '',
                'coordinates' => $zone->coordinates->map(fn($c) => [
                    'lat' => $c->latitude,
                    'lng' => $c->longitude
                ]),
            ];
        });

        return response()->json($zones);
    }


    public function mapView(Zone $zone)
    {
        $zone->load(['coordinates', 'district.province.department']);

        $zoneData = [
            'id' => $zone->id,
            'name' => $zone->name,
            'description' => $zone->description,
            'status' => $zone->status,
            'district' => $zone->district->name ?? '',
            'province' => $zone->district->province->name ?? '',
            'department' => $zone->district->province->department->name ?? '',
            'coordinates' => $zone->coordinates->map(fn($c) => [
                'lat' => (float) $c->latitude,
                'lng' => (float) $c->longitude
            ])->toArray(),
        ];

        return view('admin.zones.map_single', compact('zoneData'));
    }

}
