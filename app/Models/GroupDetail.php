<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupDetail extends Model
{
    use HasFactory;
    protected $table = 'groupdetails'; // 👈 especifica el nombre real


    protected $fillable = ['employee_id', 'scheduling_id', 'role'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scheduling()
    {
        return $this->belongsTo(Scheduling::class);
    }
}