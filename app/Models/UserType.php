<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    use HasFactory;

    protected $table = 'usertypes';

    protected $fillable = [
        'name',
        'description'
    ];

    // Relación con usuarios
    public function users()
    {
        return $this->hasMany(User::class, 'usertype_id');
    }
}