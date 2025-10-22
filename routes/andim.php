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
use App\Http\Controllers\admin\EmployeeTypeController;
use App\Http\Controllers\admin\EmployeeController;
use App\Http\Controllers\admin\ContractController;

// use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\admin\AttendanceController;
use App\Http\Controllers\admin\VacationController;


// Página principal
Route::get('/', [AdminController::class, 'index'])->name('admin.index');

// Recursos principales
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

// -------------------
// 🔹 CONTRATOS
// -------------------
Route::get('contracts/get-employees-by-position', [ContractController::class, 'getEmployeesByPosition'])
    ->name('admin.contracts.get-employees-by-position');

Route::get('contracts/get-departments', [ContractController::class, 'getDepartments'])
    ->name('admin.contracts.get-departments');

Route::resource('contracts', ContractController::class)->names('admin.contracts');

// -------------------
// 🔹 ZONAS
// -------------------
Route::resource('zones', ZoneController::class)->names('admin.zones');

Route::get('zones/by-district/{districtId}', [ZoneController::class, 'getZonesByDistrict'])
    ->name('admin.zones.byDistrict');

// -------------------
// 🔹 UBIGEO (Departamentos, Provincias, Distritos)
// -------------------
Route::get('admin/get-departments', [UbigeoController::class, 'getDepartments'])
    ->name('admin.get.departments');

Route::get('admin/get-provinces/{department}', [UbigeoController::class, 'getProvincesByDepartment'])
    ->name('admin.get.provinces');

Route::get('admin/get-districts/{province}', [UbigeoController::class, 'getDistrictsByProvince'])
    ->name('admin.get.districts');

// -------------------
// 🔹 VEHÍCULOS / IMÁGENES
// -------------------
Route::resource('vehicleimages', VehicleImageController::class)->names('admin.vehicleimages');

Route::get('zones/{zone}/map', [ZoneController::class, 'mapView'])->name('admin.zones.mapView');


Route::get('vehicles/{id}/manage-images', [VehicleController::class, 'manageImages'])->name('admin.vehicles.manage-images');
Route::post('vehicles/{id}/store-images', [VehicleController::class, 'storeImages'])->name('admin.vehicles.store-images');
Route::post('vehicles/set-profile/{id}', [VehicleController::class, 'setAsProfile'])->name('admin.vehicles.set-profile');
Route::delete('vehicles/delete-image/{id}', [VehicleController::class, 'destroyImage'])->name('admin.vehicles.delete-image');
Route::get('vehicles/images-by-vehicle/{vehicle_id}', [VehicleController::class, 'getImagesByVehicle'])->name('admin.vehicles.images-by-vehicle');

// Modelos por marca
Route::get('vehicles/get-models/{brandId}', [VehicleController::class, 'getModelsByBrand'])->name('admin.vehicles.get-models');

// -------------------
// 🔹 ASISTENCIAS
// -------------------
Route::resource('attendances', AttendanceController::class)->names('admin.attendances');
Route::get('/admin/employees/search', [AttendanceController::class, 'searchEmployees'])->name('admin.employees.search');


// Gestión de Vacaciones
Route::resource('vacations', VacationController::class)->names('admin.vacations');
    
// Rutas adicionales para vacaciones
Route::post('vacations/{vacation}/approve', [VacationController::class, 'approve'])->name('admin.vacations.approve');
Route::post('vacations/{vacation}/reject', [VacationController::class, 'reject'])->name('admin.vacations.reject');
Route::post('vacations/{vacation}/cancel', [VacationController::class, 'cancel'])->name('admin.vacations.cancel');
Route::get('vacations/{employee}/available-days', [VacationController::class, 'getAvailableDays'])->name('admin.vacations.available-days');





// Ruta para mostrar el formulario de asistencia
Route::view('/asistencia', 'attendance')->name('attendance.form');

// Ruta para procesar el formulario cuando el usuario hace clic en "Registrar Asistencia"
Route::post('/registrar-asistencia', [AttendanceController::class, 'register'])->name('attendance.register');