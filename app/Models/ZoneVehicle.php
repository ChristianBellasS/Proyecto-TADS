<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneVehicle extends Model
{
    use HasFactory;

    protected $table = 'zone_vehicle';

    protected $fillable = ['zone_id', 'vehicle_id'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}