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
    // Relación con turno

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    // Agregar relación con detalles del grupo
    // Relación con detalles del grupo
    public function groupDetails()
    {
        return $this->hasMany(GroupDetail::class);
    }

    // public function groupDetails()
    // {
    //     return $this->hasMany(GroupDetail::class);
    // }

    /*
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_groups', 'scheduling_id', 'employee_id');
    }
    */
    // Modifique este método para usar la tabla group_details
    // Relación con empleados a través de groupDetails
    public function employees()
    {
        // return $this->belongsToMany(Employee::class, 'group_details', 'scheduling_id', 'employee_id')
        return $this->belongsToMany(Employee::class, 'groupdetails', 'scheduling_id', 'employee_id')

            ->withPivot('role')
            ->withTimestamps();
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
    // Agregue estos métodos para obtener conductor y ayudantes
    // Obtener conductor
    public function getDriverAttribute()
    {
        return $this->groupDetails()
            ->where('role', 'conductor')
            ->first()
            ->employee ?? null;
    }

    // Obtener ayudantes
    public function getAssistantsAttribute()
    {
        return $this->groupDetails()
            ->where('role', 'ayudante')
            ->get()
            ->map(function ($detail) {
                return $detail->employee;
            })
            ->filter();
    }

    // Validar si puede iniciar recorrido
    public function canStartRoute()
    {
        // Verificar estado
        if ($this->status !== 'programado') {
            return [
                'can_start' => false,
                'error' => 'La programación no está en estado programado'
            ];
        }

        // Verificar vehículo
        if (!$this->vehicle || $this->vehicle->status !== 'active') {
            return [
                'can_start' => false,
                'error' => 'El vehículo no está disponible'
            ];
        }

        // Verificar empleados
        foreach ($this->groupDetails as $detail) {
            if (!$detail->employee) {
                return [
                    'can_start' => false,
                    'error' => 'Falta información de personal'
                ];
            }

            $validation = $detail->employee->canBeScheduled($this->date);
            if (!$validation['can_be_scheduled']) {
                return [
                    'can_start' => false,
                    'error' => "{$detail->employee->full_name}: {$validation['error']}"
                ];
            }
        }

        return [
            'can_start' => true,
            'error' => null
        ];
    }
    /*
    // Métodos de validación adicionales
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
    */
    // Scope para programaciones en una fecha
    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    // Scope para programaciones en un rango de fechas
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Scope para programaciones por estado
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    // Fin de la clase

    // Historial de cambios
    public function changes()
    {
        return $this->hasMany(SchedulingChange::class);
    }

    // Registrar cambios en el historial
    public function logChange($changedBy, $changeType, $reason, $oldValues, $newValues)
    {
        return $this->changes()->create([
            'changed_by' => $changedBy,
            'change_type' => $changeType,
            'reason' => $reason,
            'old_values' => $oldValues,
            'new_values' => $newValues
        ]);
    }

    // Obtener historial de cambios ordenado
    public function getChangeHistory()
    {
        return $this->changes()
            ->with('changedBy')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
