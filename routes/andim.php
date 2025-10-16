<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\BrandModelController;
use App\Http\Controllers\admin\VehicleTypeController;
use App\Http\Controllers\admin\ColorController;

Route::get('/',[AdminController::class,'index'])->name('admin.index');
Route::resource('brands', BrandController::class)->names('admin.brands');
Route::resource('brandmodel', BrandmodelController::class)->names('admin.models');
Route::resource('vehicletypes', VehicleTypeController::class)->names('admin.vehicletypes');
Route::resource('colors', ColorController::class)->names('admin.colors');