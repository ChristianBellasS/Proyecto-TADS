<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::prefix('public')->group(function () {
    Route::get('attendances', [AttendanceController::class, 'createPublic'])->name('public.attendances.create');
    Route::post('attendances', [AttendanceController::class, 'storePublic'])->name('public.attendances.store');
    Route::get('attendances/search-employees', [AttendanceController::class, 'searchEmployees'])->name('public.attendances.search-employees');
    Route::get('attendances/day-records', [AttendanceController::class, 'getDayRecords'])
    ->name('public.attendances.day-records');

});