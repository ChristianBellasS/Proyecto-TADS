<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigGroup extends Model
{
    use HasFactory;

    protected $table = 'configgroups';

    protected $fillable = [
        'employeegroup_id', 'employee_id', 'role'
    ];

    public function employeeGroup()
    {
        return $this->belongsTo(EmployeeGroup::class, 'employeegroup_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}