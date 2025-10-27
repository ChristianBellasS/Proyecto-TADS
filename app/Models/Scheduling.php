<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scheduling extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'shift_id', 'vehicle_id', 'date', 'status', 'notes'];

    protected $casts = [
        'date' => 'date'
    ];

    public function group()
    {
        return $this->belongsTo(EmployeeGroup::class, 'group_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // public function groupDetails()
    // {
    //     return $this->hasMany(GroupDetail::class);
    // }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_groups', 'scheduling_id', 'employee_id');
    }


    // Métodos de validación
    public function hasEmployeeOnVacation()
    {
        return $this->employees()->whereHas('vacations', function ($query) {
            $query->where('status', 'Approved')
                ->whereDate('start_date', '<=', $this->date)
                ->whereDate('end_date', '>=', $this->date);
        })->exists();
    }

    public function hasEmployeeWithoutContract()
    {
        return $this->employees()->whereDoesntHave('contracts', function ($query) {
            $query->where('is_active', true)
                ->whereDate('start_date', '<=', $this->date)
                ->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $this->date);
                });
        })->exists();
    }
}
