<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Brand;
use App\Models\BrandModel;
use App\Models\VehicleType;
use App\Models\Color;
use App\Models\VehicleImage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vehicles = Vehicle::with(['brand', 'brandModel', 'type', 'color', 'vehicleImages'])->get();

        if ($request->ajax()) {
            return DataTables::of($vehicles)
                ->addColumn("brand", function ($vehicle) {
                    return $vehicle->brand->name ?? 'N/A';
                })
                ->addColumn("model", function ($vehicle) {
                    return $vehicle->brandModel->name ?? 'N/A';
                })
                ->addColumn("type", function ($vehicle) {
                    return $vehicle->type->name ?? 'N/A';
                })
                ->addColumn("color", function ($vehicle) {
                    $colorCode = $vehicle->color->code ?? '#CCCCCC';
                    $colorName = $vehicle->color->name ?? 'N/A';
                    return '<div style="display: flex; align-items: center;">
                                <div style="width: 20px; height: 20px; background-color: ' . $colorCode . '; border: 1px solid #ccc; border-radius: 3px; margin-right: 8px;"></div>
                                ' . $colorName . '
                            </div>';
                })
                ->addColumn("status_badge", function ($vehicle) {
                    $badge = $vehicle->status == 1 ? 
                        '<span class="badge badge-success">Activo</span>' : 
                        '<span class="badge badge-danger">Inactivo</span>';
                    return $badge;
                })
                ->addColumn("images", function ($vehicle) {
                    $imagesCount = $vehicle->vehicleImages->count();
                    
                    // SOLUCIÓN: Obtener la imagen de perfil correctamente
                    $profileImage = null;
                    if ($vehicle->vehicleImages->count() > 0) {
                        $profileImageObj = $vehicle->vehicleImages->where('profile', 1)->first();
                        if (!$profileImageObj) {
                            $profileImageObj = $vehicle->vehicleImages->first();
                        }
                        $profileImage = $profileImageObj->image_url;
                    } else {
                        // Imagen por defecto
                        $profileImage = '/storage/vehicle_images/no_logo.png';
                    }
                    
                    return '<div class="d-flex align-items-center">
                                <img src="' . $profileImage . '" 
                                    class="img-thumbnail vehicle-image-preview" 
                                    data-vehicle="' . $vehicle->id . '" 
                                    width="60" height="120" 
                                    style="cursor: pointer; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                            </div>';
                })
                ->addColumn("actions", function ($vehicle) {
                    return '
                        <div class="btn-group" style="display: flex; gap: 0.5rem; align-items: center; justify-content: center;">
                            <button class="btn btn-info btn-sm btnImages" data-id="' . $vehicle->id . '" title="Gestionar Imágenes" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-images"></i>
                            </button>
                            <button class="btn btn-warning btn-sm btnEditar" data-id="' . $vehicle->id . '" title="Editar" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form action="' . route('admin.vehicles.destroy', $vehicle->id) . '" method="POST" class="frmDelete d-inline" style="margin: 0;">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>';
                })
                ->rawColumns(['color', 'status_badge', 'images', 'actions'])
                ->make(true);
        } else {
            return view('admin.vehicles.index', compact('vehicles'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::all();
        $models = BrandModel::all();
        $types = VehicleType::all();
        $colors = Color::all();
        
        return view('admin.vehicles.create', compact('brands', 'models', 'types', 'colors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validación de los campos
            $request->validate([
                'name' => 'required|string|max:100',
                'code' => 'required|string|max:100|unique:vehicles',
                'plate' => 'required|string|max:20|unique:vehicles',
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'load_capacity' => 'required|numeric|min:0',
                'status' => 'required|in:0,1',
                'brand_id' => 'required|exists:brands,id',
                'model_id' => 'required|exists:brandmodels,id',
                'type_id' => 'required|exists:vehicletypes,id',
                'color_id' => 'required|exists:colors,id',
                'description' => 'nullable|string'
            ]);

            // Crear el nuevo vehículo
            Vehicle::create([
                'name' => $request->name,
                'code' => $request->code,
                'plate' => strtoupper($request->plate),
                'year' => $request->year,
                'load_capacity' => $request->load_capacity,
                'description' => $request->description,
                'status' => $request->status,
                'brand_id' => $request->brand_id,
                'model_id' => $request->model_id,
                'type_id' => $request->type_id,
                'color_id' => $request->color_id
            ]);

            return response()->json(['message' => 'Vehículo creado exitosamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el proceso de creación del vehículo. '.$th->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vehicle = Vehicle::find($id);
        $brands = Brand::all();
        $models = BrandModel::all();
        $types = VehicleType::all();
        $colors = Color::all();
        
        return view('admin.vehicles.edit', compact('vehicle', 'brands', 'models', 'types', 'colors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $vehicle = Vehicle::find($id);

            if (!$vehicle) {
                return response()->json(['message' => 'Vehículo no encontrado.'], 404);
            }

            // Validación de los campos
            $request->validate([
                'name' => 'required|string|max:100',
                'code' => 'required|string|max:100|unique:vehicles,code,' . $vehicle->id,
                'plate' => 'required|string|max:20|unique:vehicles,plate,' . $vehicle->id,
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'load_capacity' => 'required|numeric|min:0',
                'status' => 'required|in:0,1',
                'brand_id' => 'required|exists:brands,id',
                'model_id' => 'required|exists:brandmodels,id',
                'type_id' => 'required|exists:vehicletypes,id',
                'color_id' => 'required|exists:colors,id',
                'description' => 'nullable|string'
            ]);

            // Actualizar el vehículo
            $vehicle->update([
                'name' => $request->name,
                'code' => $request->code,
                'plate' => strtoupper($request->plate),
                'year' => $request->year,
                'load_capacity' => $request->load_capacity,
                'description' => $request->description,
                'status' => $request->status,
                'brand_id' => $request->brand_id,
                'model_id' => $request->model_id,
                'type_id' => $request->type_id,
                'color_id' => $request->color_id
            ]);

            return response()->json(['message' => 'Vehículo actualizado exitosamente.'], 200);
        } catch (\Throwable $th) {
            Log::error('Error en la actualización de vehículo: ' . $th->getMessage());
            return response()->json(['message' => 'Error en el proceso de actualización del vehículo.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vehicle = Vehicle::find($id);
        
        // Eliminar todas las imágenes asociadas
        foreach ($vehicle->vehicleImages as $image) {
            if ($image->image && Storage::disk('public')->exists($image->image)) {
                Storage::disk('public')->delete($image->image);
            }
            $image->delete();
        }
        
        $vehicle->delete();
        return redirect()->route('admin.vehicles.index')->with('action', 'Vehículo eliminado exitosamente.');
    }

    public function getModelsByBrand($brandId)
    {
        try {
            \Log::info("Solicitando modelos para marca ID: " . $brandId);
            
            // Verificar que la marca existe
            $brand = Brand::find($brandId);
            if (!$brand) {
                \Log::warning("Marca no encontrada ID: " . $brandId);
                return response()->json([], 404);
            }
            
            $models = BrandModel::where('brand_id', $brandId)->get();
            
            \Log::info("Modelos encontrados para marca {$brandId} ({$brand->name}): " . $models->count());
            
            return response()->json($models);
            
        } catch (\Throwable $th) {
            \Log::error("Error al obtener modelos para marca {$brandId}: " . $th->getMessage());
            return response()->json(['error' => 'Error al cargar los modelos: ' . $th->getMessage()], 500);
        }
    }

    // 🔹 NUEVOS MÉTODOS PARA IMÁGENES

    public function manageImages($id)
    {
        $vehicle = Vehicle::with('vehicleImages')->findOrFail($id);
        return view('admin.vehicles.manage-images', compact('vehicle'));
    }

    public function storeImages(Request $request, $id)
{
    $request->validate([
        'images' => 'sometimes|array',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        'profile_image_id' => 'nullable|exists:vehicleimages,id',
        'images_to_delete' => 'nullable|string',
    ]);

    try {
        $vehicle = Vehicle::findOrFail($id);

        // 1. Eliminar imágenes marcadas para eliminación
        if ($request->has('images_to_delete') && !empty($request->images_to_delete)) {
            $imagesToDelete = explode(',', $request->images_to_delete);
            
            foreach ($imagesToDelete as $imageId) {
                $image = VehicleImage::find($imageId);
                if ($image && $image->vehicle_id == $vehicle->id) {
                    // Eliminar archivo físico
                    if ($image->image && Storage::disk('public')->exists($image->image)) {
                        Storage::disk('public')->delete($image->image);
                    }
                    // Eliminar de la base de datos
                    $image->delete();
                }
            }
        }

        // 2. Establecer imagen de perfil
        if ($request->has('profile_image_id') && $request->profile_image_id) {
            VehicleImage::where('vehicle_id', $vehicle->id)
                        ->update(['profile' => 0]);
            
            $profileImage = VehicleImage::find($request->profile_image_id);
            if ($profileImage && $profileImage->vehicle_id == $vehicle->id) {
                $profileImage->profile = 1;
                $profileImage->save();
            }
        }

        // 3. Agregar nuevas imágenes
        $newImagesCount = 0;
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('vehicle_images', 'public');
                
                // Verificar si es la primera imagen y no hay perfil establecido
                $isProfile = ($newImagesCount === 0 && !$request->has('profile_image_id'));
                
                VehicleImage::create([
                    'image' => $path,
                    'profile' => $isProfile ? 1 : 0,
                    'vehicle_id' => $vehicle->id,
                ]);
                
                $newImagesCount++;
            }
        }

        $message = 'Imágenes actualizadas correctamente';
        if ($newImagesCount > 0) {
            $message .= '. ' . $newImagesCount . ' nueva(s) imagen(es) agregada(s)';
        }

        return response()->json([
            'message' => $message,
            'images_count' => $vehicle->vehicleImages()->count()
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error al guardar las imágenes: ' . $e->getMessage()
        ], 500);
    }
}
    public function setAsProfile(Request $request, $id)
    {
        $image = VehicleImage::findOrFail($id);
        
        // Desactivar todas las imágenes de perfil del mismo vehículo
        VehicleImage::where('vehicle_id', $image->vehicle_id)
                    ->update(['profile' => 0]);
        
        // Activar la imagen seleccionada como perfil
        $image->profile = 1;
        $image->save();

        return response()->json(['message' => 'Imagen establecida como perfil exitosamente']);
    }

    public function destroyImage($id)
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

    public function getImagesByVehicle($vehicle_id)
    {
        $images = VehicleImage::where('vehicle_id', $vehicle_id)->get();

        return response()->json([
            'images' => $images->map(function ($img) {
                return [
                    'id' => $img->id,
                    'url' => $img->image_url,
                    'is_profile' => $img->profile == 1
                ];
            })
        ]);
    }
}