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
}