<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeType extends Model
{
    use HasFactory;

    protected $table = 'employeetype';

    protected $fillable = [
        'name',
        'description'
    ];

    // Relación con empleados
    public function employees()
    {
        return $this->hasMany(Employee::class, 'employeetype_id');
    }

    // Scope para buscar por nombre
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }
}