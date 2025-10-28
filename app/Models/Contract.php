<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'contract_type',
        'start_date',
        'end_date',
        'salary',
        'position_id',
        'department_id',
        'vacation_days_per_year',
        'probation_period_months',
        'is_active',
        'termination_reason',
    ];

    // Agregar casts para fechas y booleanos

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'salary' => 'decimal:2'
    ];


    // Relaciones
    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function position() {
        return $this->belongsTo(EmployeeType::class, 'position_id');
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    // Nuevo código aquí
    // Scope para contratos activos
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope para contratos vigentes en una fecha
    public function scopeValidOnDate($query, $date = null)
    {
        $date = $date ?: now();
        
        return $query->where('start_date', '<=', $date)
            ->where(function($q) use ($date) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $date);
            });
    }
}