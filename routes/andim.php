<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\BrandModelController;
use App\Http\Controllers\admin\VehicleTypeController;
use App\Http\Controllers\admin\ColorController;
use App\Http\Controllers\admin\VehicleController;
use App\Http\Controllers\admin\VehicleImageController;

Route::get('/',[AdminController::class,'index'])->name('admin.index');
Route::resource('brands', BrandController::class)->names('admin.brands');
Route::resource('brandmodels', BrandmodelController::class)->names('admin.brandmodels');
Route::resource('vehicletypes', VehicleTypeController::class)->names('admin.vehicletypes');
Route::resource('colors', ColorController::class)->names('admin.colors');
Route::resource('vehicles', VehicleController::class)->names('admin.vehicles');
Route::resource('vehicleimages', VehicleImageController::class)->names('admin.vehicleimages');

#FUNCIONES - VEHICLEIMAGES
Route::get('vehicleimages/vehicle/{vehicle_id}', [VehicleImageController::class, 'getImagesByVehicle'])->name('admin.vehicleimages.by_vehicle');
Route::get('admin/vehicleimages/vehicle/{vehicle_id}', [VehicleImageController::class, 'getImagesByVehicle']);
Route::post('vehicleimages/set-profile/{id}', [VehicleImageController::class, 'setAsProfile'])->name('admin.vehicleimages.set_profile');