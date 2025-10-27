<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'district_id', 'status'];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function coordinates()
    {
        return $this->hasMany(ZoneCoord::class)->orderBy('order');
    }

    public function province()
    {
        return $this->hasOneThrough(Province::class, District::class);
    }

    public function department()
    {
        return $this->hasOneThrough(Department::class, Province::class, 'id', 'id', 'province_id', 'department_id');
    }

    // Metodo utilizado para Registrar Grupos de Personal
    public function employeeGroups()
    {
        return $this->hasMany(EmployeeGroup::class);
    }

    // para programación

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'zone_vehicle', 'zone_id', 'vehicle_id');
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'zone_shift', 'zone_id', 'shift_id');
    }

    // Método para precargar datos
    public function getProgrammingData()
    {
        return [
            'vehicles' => $this->vehicles()->where('status', true)->get(),
            'shifts' => $this->shifts,
            'groups' => $this->employeeGroups()->where('status', true)->get()
        ];
    }
}
