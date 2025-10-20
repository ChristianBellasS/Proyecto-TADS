<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Province;
use App\Models\District;

class UbigeoController extends Controller
{
    public function getDepartments()
    {
        return response()->json(Department::select('id', 'name')->orderBy('name')->get());
    }

    public function getProvincesByDepartment($departmentId)
    {
        return response()->json(
            Province::where('department_id', $departmentId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
        );
    }

    public function getDistrictsByProvince($provinceId)
    {
        return response()->json(
            District::where('province_id', $provinceId)
                ->select('id', 'name', 'latitude', 'longitude')
                ->orderBy('name')
                ->get()
        );
    }
}