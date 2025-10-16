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

use App\Http\Controllers\admin\VehicleImageController;

Route::get('/',[AdminController::class,'index'])->name('admin.index');
Route::resource('brands', BrandController::class)->names('admin.brands');
Route::resource('brandmodels', BrandModelController::class)->names('admin.brandmodels');
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


Route::resource('vehicleimages', VehicleImageController::class)->names('admin.vehicleimages');

#FUNCIONES - VEHICLEIMAGES
Route::get('vehicleimages/vehicle/{vehicle_id}', [VehicleImageController::class, 'getImagesByVehicle'])->name('admin.vehicleimages.by_vehicle');
Route::get('admin/vehicleimages/vehicle/{vehicle_id}', [VehicleImageController::class, 'getImagesByVehicle']);
Route::post('vehicleimages/set-profile/{id}', [VehicleImageController::class, 'setAsProfile'])->name('admin.vehicleimages.set_profile');

#FUNCIONES - VEHICLES
Route::get('/admin/vehicles/get-models/{brandId}', [VehicleController::class, 'getModelsByBrand'])->name('admin.vehicles.get-models');