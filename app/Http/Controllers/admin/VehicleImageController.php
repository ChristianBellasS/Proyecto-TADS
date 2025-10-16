<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleImage;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Storage;

class VehicleImageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Agrupar por vehículo y contar imágenes
            $vehicles = Vehicle::withCount('vehicleImages')
                ->with(['vehicleImages' => function($query) {
                    $query->where('profile', 1)->orWhereNull('profile');
                }])
                ->has('vehicleImages') // Solo vehículos con imágenes
                ->get();

            $data = $vehicles->map(function ($vehicle) {
                $profileImage = $vehicle->vehicleImages->firstWhere('profile', 1) ?? $vehicle->vehicleImages->first();
                
                return [
                    'image' => $profileImage ? 
                        '<img src="' . asset('storage/' . $profileImage->image) . '" 
                                data-vehicle="' . $vehicle->id . '" 
                                class="img-preview" width="70" height="70" style="cursor:pointer; object-fit: cover; border-radius: 5px;">' :
                        '<img src="' . asset('images/no_logo.png') . '" width="70" height="70" style="object-fit: cover; border-radius: 5px;">',
                    'vehicle_name' => $vehicle->name ?? 'Vehículo #' . $vehicle->id,
                    'vehicle_id' => $vehicle->id,
                    'images_count' => '<span class="badge badge-info">' . $vehicle->vehicle_images_count . ' imagen(es)</span>',
                    'profile_set' => $profileImage ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-secondary">No</span>',
                    'created_at' => $vehicle->created_at->format('Y-m-d H:i'),
                    'updated_at' => $vehicle->updated_at->format('Y-m-d H:i'),
                    'actions' => '
                        <div class="btn-group">
                            <button class="btn btn-warning btn-sm btnEditar" data-id="' . $vehicle->id . '" title="Gestionar Imágenes">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btnEliminar" data-id="' . $vehicle->id . '" title="Eliminar Todas las Imágenes">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>',
                ];
            });

            return response()->json(['data' => $data]);
        }

        $vehicles = Vehicle::all();
        return view('admin.vehicleimages.index', compact('vehicles'));
    }

    // 🔹 Devuelve todas las imágenes de un vehículo (para el carrusel) con sus IDs
    public function getImagesByVehicle($vehicle_id)
    {
        $images = VehicleImage::where('vehicle_id', $vehicle_id)->get();

        return response()->json([
            'images' => $images->map(function ($img) {
                return [
                    'id' => $img->id,
                    'url' => asset('storage/' . $img->image),
                    'is_profile' => $img->profile == 1
                ];
            })
        ]);
    }

    // 🔹 Obtener vehículos para select
    public function getVehicles()
    {
        $vehicles = Vehicle::select('id', 'name')->get();
        return response()->json($vehicles);
    }

    // 🔹 Nuevo método para establecer imagen como perfil
    public function setAsProfile(Request $request, $id)
    {
        $image = VehicleImage::findOrFail($id);
        
        // Desactivar todas las imágenes de perfil del mismo vehículo
        VehicleImage::where('vehicle_id', $image->vehicle_id)
                    ->update(['profile' => 0]);
        
        // Activar la imagen seleccionada como perfil
        $image->profile = 1;
        $image->save();

        if ($request->ajax()) {
            return response()->json(['message' => 'Imagen establecida como perfil exitosamente']);
        }

        return redirect()->route('admin.vehicleimages.index');
    }

    public function create()
    {
        $vehicles = Vehicle::all();
        return view('admin.vehicleimages.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'vehicle_id' => 'required|exists:vehicles,id',
            'profile_image_index' => 'required|integer|min:0',
        ]);

        $uploadedImages = [];
        $profileImageIndex = $request->profile_image_index;

        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('vehicleimages', 'public');
            
            // Establecer como perfil si coincide con el índice
            $isProfile = $index == $profileImageIndex ? 1 : 0;

            $vehicleImage = VehicleImage::create([
                'image' => $path,
                'profile' => $isProfile,
                'vehicle_id' => $request->vehicle_id,
            ]);

            $uploadedImages[] = $vehicleImage;
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => count($uploadedImages) . ' imagen(es) registrada(s) exitosamente para el vehículo',
                'images_count' => count($uploadedImages)
            ]);
        }

        return redirect()->route('admin.vehicleimages.index')
                        ->with('success', count($uploadedImages) . ' imagen(es) registrada(s) exitosamente');
    }

    public function edit($id)
    {
        $vehicle = Vehicle::with('vehicleImages')->findOrFail($id);
        $vehicles = Vehicle::all();
        
        return view('admin.vehicleimages.edit', compact('vehicle', 'vehicles'));
    }

    public function update(Request $request, $vehicle_id)
    {
        $request->validate([
            'profile_image_id' => 'nullable|exists:vehicleimages,id',
            'new_images' => 'nullable|array',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'images_to_delete' => 'nullable|string',
        ]);

        // Eliminar imágenes marcadas para eliminación
        if ($request->has('images_to_delete') && !empty($request->images_to_delete)) {
            $imagesToDelete = explode(',', $request->images_to_delete);
            
            foreach ($imagesToDelete as $imageId) {
                $image = VehicleImage::find($imageId);
                if ($image) {
                    // Eliminar archivo físico
                    if ($image->image && Storage::disk('public')->exists($image->image)) {
                        Storage::disk('public')->delete($image->image);
                    }
                    // Eliminar de la base de datos
                    $image->delete();
                }
            }
        }

        // Establecer imagen de perfil si se especificó una existente
        if ($request->has('profile_image_id') && $request->profile_image_id) {
            VehicleImage::where('vehicle_id', $vehicle_id)
                        ->update(['profile' => 0]);
            
            $profileImage = VehicleImage::find($request->profile_image_id);
            if ($profileImage) {
                $profileImage->profile = 1;
                $profileImage->save();
            }
        }

        // Agregar nuevas imágenes si existen
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $image) {
                $path = $image->store('vehicleimages', 'public');
                
                VehicleImage::create([
                    'image' => $path,
                    'profile' => 0, // Las nuevas imágenes no son perfil por defecto
                    'vehicle_id' => $vehicle_id,
                ]);
            }
        }

        if ($request->ajax()) {
            return response()->json(['message' => 'Imágenes actualizadas correctamente']);
        }

        return redirect()->route('admin.vehicleimages.index')
                        ->with('success', 'Imágenes actualizadas correctamente');
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $images = VehicleImage::where('vehicle_id', $id)->get();
        $imagesCount = $images->count();

        // Eliminar todas las imágenes físicas y de la base de datos
        foreach ($images as $image) {
            if ($image->image && Storage::disk('public')->exists($image->image)) {
                Storage::disk('public')->delete($image->image);
            }
            $image->delete();
        }

        return response()->json([
            'message' => 'Todas las imágenes (' . $imagesCount . ') del vehículo fueron eliminadas correctamente',
            'deleted_count' => $imagesCount
        ]);
    }

    // 🔹 Eliminar imagen específica (desde el editor)
    public function destroyImage(Request $request, $id)
    {
        $image = VehicleImage::findOrFail($id);
        $vehicleId = $image->vehicle_id;
        $wasProfile = $image->profile;

        // Eliminar archivo físico
        if ($image->image && Storage::disk('public')->exists($image->image)) {
            Storage::disk('public')->delete($image->image);
        }

        $image->delete();

        // Si era la imagen de perfil, establecer una nueva
        if ($wasProfile) {
            $newProfile = VehicleImage::where('vehicle_id', $vehicleId)->first();
            if ($newProfile) {
                $newProfile->profile = 1;
                $newProfile->save();
            }
        }

        return response()->json([
            'message' => 'Imagen eliminada correctamente',
            'remaining_images' => VehicleImage::where('vehicle_id', $vehicleId)->count()
        ]);
    }
}