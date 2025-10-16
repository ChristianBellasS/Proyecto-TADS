<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicles';
    
    protected $guarded = [];

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
}
