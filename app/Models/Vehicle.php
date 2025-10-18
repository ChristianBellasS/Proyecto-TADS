<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicles';
    
    protected $fillable = [
        'name',
        'code',
        'plate',
        'year',
        'load_capacity',
        'fuel_capacity',
        'compaction_capacity',
        'people_capacity',
        'description',
        'status',
        'brand_id',
        'model_id',
        'type_id',
        'color_id'
    ];

    // Relaciones
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function brandModel()
    {
        return $this->belongsTo(BrandModel::class, 'model_id');
    }

    public function type()
    {
        return $this->belongsTo(VehicleType::class, 'type_id');
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function vehicleImages()
    {
        return $this->hasMany(VehicleImage::class);
    }

    // Accesor para la imagen de perfil
    public function getProfileImageAttribute()
    {
        $profileImage = $this->vehicleImages()->where('profile', 1)->first();
        return $profileImage ? $profileImage->image_url : asset('images/no_logo.png');
    }

    // Accesor para el estado
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? 'Activo' : 'Inactivo';
    }

    // Accesor para la placa formateada
    public function getFormattedPlateAttribute()
    {
        $plate = strtoupper($this->plate);
        if (preg_match('/^([A-Z0-9]{2})([A-Z0-9]{4})$/', $plate, $matches)) {
            return $matches[1] . '-' . $matches[2];
        } elseif (preg_match('/^([A-Z0-9]{3})([A-Z0-9]{3})$/', $plate, $matches)) {
            return $matches[1] . '-' . $matches[2];
        }
        return $plate;
    }

    // Contar imágenes
    public function getImagesCountAttribute()
    {
        return $this->vehicleImages()->count();
    }

    // Verificar si tiene imágenes
    public function getHasImagesAttribute()
    {
        return $this->vehicleImages()->exists();
    }
}