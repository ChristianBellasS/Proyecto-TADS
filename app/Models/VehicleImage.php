<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleImage extends Model
{
    use HasFactory;
    
    protected $table = 'vehicleimages';

    protected $fillable = [
        'image',
        'profile',
        'vehicle_id',
    ];
    
    protected $casts = [
        'profile' => 'boolean',
    ];
    
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // 🔹 Accesor para la URL completa de la imagen
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : asset('images/no_logo.png');
    }

    // 🔹 Scope para imágenes de perfil
    public function scopeProfile($query)
    {
        return $query->where('profile', true);
    }
}