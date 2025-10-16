<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'code'];

    public function provinces()
    {
        return $this->hasMany(Province::class);
    }

    public function districts()
    {
        return $this->hasManyThrough(District::class, Province::class);
    }
}
