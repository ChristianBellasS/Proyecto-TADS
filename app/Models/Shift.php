<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';
    
    protected $guarded = [];


    // Metodo utilizado para Registrar Grupos de Personal
    public function employeeGroups()
    {
        return $this->hasMany(EmployeeGroup::class);
    }

    // Metodo utilizado para Registrar Programaciones
    public function schedulings()
    {
        return $this->hasMany(Scheduling::class);
    }
}