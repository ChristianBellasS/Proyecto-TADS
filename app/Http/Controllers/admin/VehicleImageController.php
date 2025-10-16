<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleImage;
use Illuminate\Support\Facades\Storage;

class VehicleImageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $images = VehicleImage::where('profile', 1)->get();

            $data = $images->map(function ($img) {
                return [
                    'image' => '<img src="' . asset('storage/' . $img->image) . '" 
                                data-vehicle="' . $img->vehicle_id . '" 
                                class="img-preview" width="70" height="70" style="cursor:pointer;">',
                    'vehicle_id' => $img->vehicle_id,
                    'profile' => $img->profile ? 'Sí' : 'No',
                    'created_at' => $img->created_at->format('Y-m-d H:i'),
                    'updated_at' => $img->updated_at->format('Y-m-d H:i'),
                    'edit' => '<button class="btn btn-warning btn-sm btnEditar" data-id="' . $img->id . '">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>',
                    'delete' => '<form action="' . route('admin.vehicleimages.destroy', $img->id) . '" method="POST" class="frmDelete">'
                                . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>',
                ];
            });

            return response()->json(['data' => $data]);
        }

        return view('admin.vehicleimages.index');
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
        return view('admin.vehicleimages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
            'profile' => 'nullable|integer',
            'vehicle_id' => 'required|integer',
        ]);

        $path = $request->file('image')->store('vehicleimages', 'public');
        $isProfile = $request->profile == 1;

        // 🔹 Si se marca como perfil, desactivar cualquier otra imagen de perfil del mismo vehículo
        if ($isProfile) {
            VehicleImage::where('vehicle_id', $request->vehicle_id)
                        ->where('profile', 1)
                        ->update(['profile' => 0]);
        }

        // 🔹 Crear la nueva imagen (sea perfil o no)
        VehicleImage::create([
            'image' => $path,
            'profile' => $isProfile ? 1 : 0,
            'vehicle_id' => $request->vehicle_id,
        ]);

        if ($request->ajax()) {
            return response()->json(['message' => 'Imagen registrada exitosamente']);
        }

        return redirect()->route('admin.vehicleimages.index');
    }

    public function edit($id)
    {
        $image = VehicleImage::findOrFail($id);
        return view('admin.vehicleimages.edit', compact('image'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'image' => 'nullable|image|max:2048',
            'profile' => 'nullable|integer',
        ]);

        $image = VehicleImage::findOrFail($id);

        if ($request->hasFile('image')) {
            if ($image->image && Storage::disk('public')->exists($image->image)) {
                Storage::disk('public')->delete($image->image);
            }

            $path = $request->file('image')->store('vehicleimages', 'public');
            $image->image = $path;
        }

        if ($request->profile == 1) {
            VehicleImage::where('vehicle_id', $image->vehicle_id)
                        ->where('id', '!=', $image->id)
                        ->update(['profile' => 0]);
        }

        $image->profile = $request->profile ?? 0;
        $image->save();

        if ($request->ajax()) {
            return response()->json(['message' => 'Imagen actualizada correctamente']);
        }

        return redirect()->route('admin.vehicleimages.index');
    }

    public function destroy(Request $request, $id)
    {
        $image = VehicleImage::findOrFail($id);

        if ($image->image && Storage::disk('public')->exists($image->image)) {
            Storage::disk('public')->delete($image->image);
        }

        $image->delete();

        if ($request->ajax()) {
            return response()->json(['message' => 'Imagen eliminada correctamente']);
        }

        return redirect()->route('admin.vehicleimages.index');
    }
}