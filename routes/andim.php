<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\BrandModelController;
use App\Http\Controllers\admin\VehicleTypeController;
use App\Http\Controllers\admin\ColorController;
use App\Http\Controllers\admin\VehicleController;
use App\Http\Controllers\admin\ZoneController;
use App\Http\Controllers\admin\DistrictController;


Route::get('/',[AdminController::class,'index'])->name('admin.index');
Route::resource('brands', BrandController::class)->names('admin.brands');
Route::resource('brandmodel', BrandmodelController::class)->names('admin.models');
Route::resource('vehicletypes', VehicleTypeController::class)->names('admin.vehicletypes');
Route::resource('colors', ColorController::class)->names('admin.colors');
Route::resource('vehicles', VehicleController::class)->names('admin.vehicles');

// Gestión de Zonas
Route::resource('zones', ZoneController::class)->names('admin.zones');
Route::get('zones/{district}/by-district', [ZoneController::class, 'getZonesByDistrict'])->name('zones.by-district');
    
// API para ubicaciones
// Route::get('admin/get-provinces/{department}', [ZoneController::class, 'getProvinces'])->name('admin.get.provinces');
// Route::get('admin/get-districts/{province}', [ZoneController::class, 'getDistricts'])->name('admin.get.districts');
    // API para ubicaciones
Route::get('admin/get-provinces/{department}', [DistrictController::class, 'getProvincesByDepartment'])->name('admin.get.provinces');
Route::get('admin/get-districts/{province}', [DistrictController::class, 'getDistrictsByProvince'])->name('admin.get.districts');


