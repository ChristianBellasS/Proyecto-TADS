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
    use App\Http\Controllers\admin\EmployeeGroupController;
    use App\Http\Controllers\admin\SchedulingController;
    use App\Http\Controllers\admin\ShiftController;
    use App\Http\Controllers\admin\MassSchedulingController;
    use App\Http\Controllers\admin\SchedulingChangeController;

    // use App\Http\Controllers\AttendanceController;
    use App\Http\Controllers\admin\AttendanceController;
    use App\Http\Controllers\PublicAttendanceController;
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
    Route::resource('employeegroups', EmployeeGroupController::class)->names('admin.employeegroups');
    // -------------------
    // 🔹 CONTRATOS
    // -------------------
    Route::get('contracts/get-employees-by-position', [ContractController::class, 'getEmployeesByPosition'])
        ->name('admin.contracts.get-employees-by-position');

    Route::get('contracts/get-departments', [ContractController::class, 'getDepartments'])
        ->name('admin.contracts.get-departments');

    Route::resource('contracts', ContractController::class)->names('admin.contracts');

    Route::get('/admin/contracts/search-employees', [ContractController::class, 'searchEmployees'])
        ->name('admin.contracts.search-employees');
    Route::get('/admin/contracts/check-employee-contracts', [ContractController::class, 'checkEmployeeContracts'])
        ->name('admin.contracts.check-employee-contracts');
    Route::get('/admin/contracts/check-last-temporal', [ContractController::class, 'checkLastTemporalContract'])
        ->name('admin.contracts.check-last-temporal');
    Route::get('/admin/contracts/get-all-employees', [ContractController::class, 'getAllEmployees'])
        ->name('admin.contracts.get-all-employees');

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
    Route::get('/admin/attendances/day-records', [AttendanceController::class, 'getDayRecords'])->name('admin.attendances.get-day-records');
    // Route::get('/admin/employees/{id}', [EmployeeController::class, 'show'])->name('admin.employees.show');


    // Gestión de Vacaciones
    Route::resource('vacations', VacationController::class)->names('admin.vacations');

    // Rutas adicionales para vacaciones
    Route::post('vacations/{vacation}/approve', [VacationController::class, 'approve'])->name('admin.vacations.approve');
    Route::post('vacations/{vacation}/reject', [VacationController::class, 'reject'])->name('admin.vacations.reject');
    Route::post('vacations/{vacation}/cancel', [VacationController::class, 'cancel'])->name('admin.vacations.cancel');
    Route::get('vacations/{employee}/available-days', [VacationController::class, 'getAvailableDays'])->name('admin.vacations.available-days');





    // Ruta para mostrar el formulario de asistencia
    // Route::view('/asistencia', 'attendance')->name('attendance.form');

    // Ruta para procesar el formulario cuando el usuario hace clic en "Registrar Asistencia"
    // Route::post('/registrar-asistencia', [AttendanceController::class, 'register'])->name('attendance.register');


    // Nuevo cambio de contratos
    // Ruta para mostrar el formulario de asistencia
    Route::view('/asistencia', 'attendance')->name('attendance.form');

    // Ruta para procesar el formulario cuando el usuario hace clic en "Registrar Asistencia"
    Route::post('/registrar-asistencia', [AttendanceController::class, 'register'])->name('attendance.register');


    // Agregar estas rutas dentro del grupo de admin/contracts
    Route::get('admin/contracts/get-all-employees', [ContractController::class, 'getAllEmployees'])->name('admin.contracts.get-all-employees');
    // Route::get('admin/contracts/get-departments', [ContractController::class, 'getDepartments'])->name('admin.contracts.get-departments');
    // Route::get('contracts/check-last-temporal', [ContractController::class, 'checkLastTemporalContract'])->name('admin.contracts.check-last-temporal');


    // Rutas para buscar empleados en el modulo de turnos
    Route::get('admin/employeegroups/search/employees', [EmployeeGroupController::class, 'searchEmployees'])->name('admin.employeegroups.search.employees');
    Route::get('admin/employeegroups/check-employee', [EmployeeGroupController::class, 'checkEmployeeAvailability'])->name('admin.employeegroups.check-employee');
    Route::get('admin/employeegroups/check-zone-shift', [EmployeeGroupController::class, 'checkZoneShiftAvailability'])->name('admin.employeegroups.check-zone-shift');

    // PROGRAMACIÓN

    Route::get('/scheduling', [SchedulingController::class, 'index'])->name('admin.scheduling.index');
    Route::get('/scheduling/create', [SchedulingController::class, 'create'])->name('admin.scheduling.create');
    Route::post('/scheduling/store', [SchedulingController::class, 'store'])->name('admin.scheduling.store');

    // Agregue esta ruta para datos diarios
    Route::get('scheduling/daily', [SchedulingController::class, 'daily'])->name('admin.scheduling.daily');
    //

    // Agregue esta nueva ruta 
    Route::delete('scheduling/{id}', [SchedulingController::class, 'destroy'])->name('admin.scheduling.destroy');

    //

    Route::get('/scheduling/daily-data', [SchedulingController::class, 'dailyData'])
        ->name('admin.scheduling.daily-data');

    // Mostrar el formulario de edición (AJAX)
    Route::get('/admin/scheduling/{id}/edit', [SchedulingController::class, 'edit'])
        ->name('admin.scheduling.edit');

    // Guardar los cambios (PUT)
    Route::put('/admin/scheduling/{id}', [SchedulingController::class, 'update'])
        ->name('admin.scheduling.update');

    // Rutas para búsqueda de grupos de personal
    Route::get('/scheduling/search-employee-groups', [SchedulingController::class, 'searchEmployeeGroups'])
        ->name('admin.scheduling.search-employee-groups');

    Route::post('scheduling/check-availability', [SchedulingController::class, 'checkAvailability'])->name('admin.scheduling.check-availability');
    // Comente esta ruta duplicada
    // Route::get('/admin/scheduling/group-data/{groupId}', [SchedulingController::class, 'getGroupData']);
    Route::get('scheduling/group-data/{groupId}', [SchedulingController::class, 'getGroupData'])->name('admin.scheduling.group-data');
    //
    // Ruta para obtener datos de un grupo específico
    Route::get('/scheduling/group-data/{groupId}', [SchedulingController::class, 'getGroupData'])
        ->name('admin.scheduling.group-data');

    // Otras rutas de scheduling
    Route::get('/scheduling/zone-data/{zone}', [SchedulingController::class, 'getZoneData'])->name('admin.scheduling.zone-data');
    // Route::post('/scheduling/check-availability', [SchedulingController::class, 'checkAvailability'])->name('admin.scheduling.check-availability');
    Route::post('/scheduling/bulk-update', [SchedulingController::class, 'bulkUpdate'])->name('admin.scheduling.bulk-update');
    Route::post('/scheduling/check-availability', [SchedulingController::class, 'checkAvailability'])->name('admin.scheduling.check-availability');
    // Route::post('/scheduling/bulk-update', [SchedulingController::class, 'bulkUpdate'])->name('admin.scheduling.bulk-update');

    //TURNOS
    Route::resource('shifts', ShiftController::class)->names('admin.shifts');

    // Route::post('scheduling/check-availability', [SchedulingController::class, 'checkAvailability'])->name('admin.scheduling.check-availability');
    // Comente esta ruta duplicada
    // Route::get('/admin/scheduling/group-data/{groupId}', [SchedulingController::class, 'getGroupData']);
    // Route::get('scheduling/group-data/{groupId}', [SchedulingController::class, 'getGroupData'])->name('admin.scheduling.group-data');
    // Otras rutas de scheduling
    // Route::get('/scheduling/zone-data/{zone}', [SchedulingController::class, 'getZoneData'])->name('admin.scheduling.zone-data');
    // Route::post('/scheduling/check-availability', [SchedulingController::class, 'checkAvailability'])->name('admin.scheduling.check-availability');
    // Route::post('/scheduling/bulk-update', [SchedulingController::class, 'bulkUpdate'])->name('admin.scheduling.bulk-update');
    // Route::post('/scheduling/check-availability', [SchedulingController::class, 'checkAvailability'])->name('admin.scheduling.check-availability');
    // Route::post('/scheduling/bulk-update', [SchedulingController::class, 'bulkUpdate'])->name('admin.scheduling.bulk-update');

    //
    Route::get('/scheduling/search-available-assistants', [SchedulingController::class, 'searchAvailableAssistants'])
        ->name('admin.scheduling.search-available-assistants');

    Route::get('/scheduling/search-available-drivers', [SchedulingController::class, 'searchAvailableDrivers'])
        ->name('admin.scheduling.search-available-drivers');

    Route::get('/scheduling/group-details/{groupId}', [SchedulingController::class, 'getGroupDetails'])
        ->name('admin.scheduling.group-details');

    //TURNOS
    // Route::resource('shifts', ShiftController::class)->names('admin.shifts');


    // Programación masiva
    Route::get('mass-scheduling', [MassSchedulingController::class, 'index'])->name('admin.mass-scheduling.index');
    Route::post('/mass-scheduling/validate', [MassSchedulingController::class, 'validateMassScheduling'])->name('admin.mass-scheduling.validate');
    Route::post('/mass-scheduling/store', [MassSchedulingController::class, 'storeMassScheduling'])->name('admin.mass-scheduling.store');
    Route::post('/mass-scheduling/validate-employee', [MassSchedulingController::class, 'validateEmployeeAvailability'])->name('admin.mass-scheduling.validate-employee');
    Route::get('/mass-scheduling/available-employees', [MassSchedulingController::class, 'getAvailableEmployees'])->name('admin.mass-scheduling.available-employees');

    // HISTORIAL DE CAMBIOS DE PROGRAMACIÓN 
    
    Route::prefix('scheduling')->group(function () {
        // Mostrar formulario de cambios
        Route::get('/{id}/changes', [SchedulingChangeController::class, 'showChangesForm'])
            ->name('admin.scheduling.changes-form');

        // Aplicar cambios
        Route::post('/{id}/apply-changes', [SchedulingChangeController::class, 'applyChanges'])
            ->name('admin.scheduling.apply-changes');

        // Obtener historial de cambios
        Route::get('/{id}/change-history', [SchedulingChangeController::class, 'getChangeHistory'])
            ->name('admin.scheduling.change-history');

        // Validar cambio individual
        Route::post('/{id}/validate-change', [SchedulingChangeController::class, 'validateChange'])
            ->name('admin.scheduling.validate-change');

        // Validar todos los cambios juntos
        Route::post('/{id}/validate-all-changes', [SchedulingChangeController::class, 'validateAllChanges'])
            ->name('admin.scheduling.validate-all-changes');
    });