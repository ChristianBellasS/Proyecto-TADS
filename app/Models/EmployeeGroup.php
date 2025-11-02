<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeGroup extends Model
{
    use HasFactory;

    protected $table = 'employeegroups';

    protected $guarded = [];
    // Relación con zona
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
    // Relación con turno

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
    // Relación con vehículo

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    // Relación con conductor

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }
    // Relaciones con ayudantes

    public function assistant1()
    {
        return $this->belongsTo(Employee::class, 'assistant1_id');
    }

    public function assistant2()
    {
        return $this->belongsTo(Employee::class, 'assistant2_id');
    }

    public function assistant3()
    {
        return $this->belongsTo(Employee::class, 'assistant3_id');
    }

    public function assistant4()
    {
        return $this->belongsTo(Employee::class, 'assistant4_id');
    }

    public function assistant5()
    {
        return $this->belongsTo(Employee::class, 'assistant5_id');
    }

    // Método para obtener todos los ayudantes
    public function getAssistantsAttribute()
    {
        /*
        $assistants = [];
        for ($i = 1; $i <= 5; $i++) {
            $assistant = $this->{"assistant{$i}"};
            if ($assistant) {
                $assistants[] = $assistant;
            }
        }
        return collect($assistants);
        */
        $assistants = collect();
        
        for ($i = 1; $i <= 5; $i++) {
            $assistant = $this->{"assistant{$i}"};
            if ($assistant) {
                $assistants->push($assistant);
            }
        }
        
        return $assistants;
    }

    public function configEmployees()
    {
        return $this->belongsToMany(Employee::class, 'configgroups', 'employeegroup_id', 'employee_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function getConfigDriverAttribute()
    {
        return $this->configEmployees()->wherePivot('role', 'conductor')->first();
    }

    public function getConfigAssistantsAttribute()
    {
        return $this->configEmployees()->wherePivot('role', 'ayudante')->get();
    }

    public function getConfigEmployeesCountAttribute()
    {
        return $this->configEmployees()->count();
    }
    // Nuevo código aquí
        // Obtener todos los empleados del grupo (conductor + ayudantes)
    public function getAllEmployeesAttribute()
    {
        $employees = collect();
        
        if ($this->driver) {
            $employees->push($this->driver);
        }
        
        return $employees->merge($this->assistants);
    }

    // Verificar si el grupo está completo (conductor + al menos 1 ayudante)
    public function getIsCompleteAttribute()
    {
        return $this->driver && $this->assistants->count() >= 1;
    }

    // Scope para grupos activos
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function configGroups()
    {
        return $this->hasMany(ConfigGroup::class, 'employeegroup_id');
    }
}
