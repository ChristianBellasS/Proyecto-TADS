<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneShift extends Model
{
    use HasFactory;

    protected $table = 'zone_shift';

    protected $fillable = ['zone_id', 'shift_id'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}