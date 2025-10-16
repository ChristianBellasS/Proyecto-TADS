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
    
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

}

