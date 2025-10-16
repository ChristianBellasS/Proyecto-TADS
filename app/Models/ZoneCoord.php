<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneCoord extends Model
{
    use HasFactory;

    protected $fillable = ['zone_id', 'latitude', 'longitude', 'order'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
    
}
