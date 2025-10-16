<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'province_id', 'latitude', 'longitude'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }
    
}
