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
use App\Http\Controllers\admin\UbigeoController;
use App\Http\Controllers\admin\UserTypeController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\EmployeeController;
use App\Http\Controllers\admin\EmployeeTypeController;
use App\Http\Controllers\admin\AttendanceController;

Route::get('/',[AdminController::class,'index'])->name('admin.index');
Route::resource('brands', BrandController::class)->names('admin.brands');
Route::resource('brandmodels', BrandModelController::class)->names('admin.brandmodels');
Route::resource('vehicletypes', VehicleTypeController::class)->names('admin.vehicletypes');
Route::resource('colors', ColorController::class)->names('admin.colors');
Route::resource('vehicles', VehicleController::class)->names('admin.vehicles');
Route::resource('usertypes', UserTypeController::class)->names('admin.usertypes');
Route::resource('users', UserController::class)->names('admin.users');
Route::resource('ubigeo', UbigeoController::class)->names('admin.ubigeo');
Route::resource('employeetypes', EmployeeTypeController::class)->names('admin.employeetypes');
Route::resource('employees', EmployeeController::class)->names('admin.employees');

// Gestión de Zonas
Route::resource('zones', ZoneController::class)->names('admin.zones');

// Rutas adicionales de Zonas
Route::get('zones/by-district/{districtId}', [ZoneController::class, 'getZonesByDistrict'])
    ->name('admin.zones.byDistrict');

Route::get('admin/get-departments', [UbigeoController::class, 'getDepartments'])
    ->name('admin.get.departments');

Route::get('admin/get-provinces/{department}', [UbigeoController::class, 'getProvincesByDepartment'])
    ->name('admin.get.provinces');

Route::get('admin/get-districts/{province}', [UbigeoController::class, 'getDistrictsByProvince'])
    ->name('admin.get.districts');

Route::resource('vehicleimages', VehicleImageController::class)->names('admin.vehicleimages');

// Rutas de Imágenes dentro de Vehículos
Route::get('vehicles/{id}/manage-images', [VehicleController::class, 'manageImages'])->name('admin.vehicles.manage-images');
Route::post('vehicles/{id}/store-images', [VehicleController::class, 'storeImages'])->name('admin.vehicles.store-images');
Route::post('vehicles/set-profile/{id}', [VehicleController::class, 'setAsProfile'])->name('admin.vehicles.set-profile');
Route::delete('vehicles/delete-image/{id}', [VehicleController::class, 'destroyImage'])->name('admin.vehicles.delete-image');
Route::get('vehicles/images-by-vehicle/{vehicle_id}', [VehicleController::class, 'getImagesByVehicle'])->name('admin.vehicles.images-by-vehicle');

#FUNCIONES - VEHICLES
Route::get('vehicles/get-models/{brandId}', [VehicleController::class, 'getModelsByBrand'])->name('admin.vehicles.get-models');

// Asistencias
Route::resource('attendances', AttendanceController::class)->names('admin.attendances');
Route::get('/admin/employees/search', [AttendanceController::class, 'searchEmployees'])->name('admin.employees.search');