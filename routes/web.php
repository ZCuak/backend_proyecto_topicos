<?php

use App\Http\Controllers\Api\User\AttendaceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Vehicle\VehicleColorController;
use App\Http\Controllers\Api\Vehicle\BrandModelController;
use App\Http\Controllers\Api\Vehicle\BrandController;
use App\Http\Controllers\Api\Vehicle\VehicleTypeController;
use App\Http\Controllers\Api\SectorController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\ZoneCoordController;
use App\Http\Controllers\Web\SchedulingController; //programaciones
use App\Http\Controllers\Api\Schedule\ScheduleController; //turnos o shifts
use App\Http\Controllers\Api\User\UserTypeController;
use App\Http\Controllers\Api\Vehicle\VehicleController;
use App\Http\Controllers\Api\Schedule\EmployeeGroupController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\Web\ContractController;
use App\Http\Controllers\Web\VacationController;
use App\Http\Controllers\Web\ZoneController;
use App\Models\EmployeeGroup;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/mark-attendance', function () {
    return view('attendances.mark');
})->name('attendance.mark.view');
Route::post('/mark-attendance', [AttendaceController::class, 'markAttendance'])->name('attendance.mark');

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('welcome'); // O tu dashboard principal
    })->name('dashboard');


    Route::resource('personal', UserController::class);
    Route::resource('vehiclecolors', VehicleColorController::class);
    Route::resource('brand-models', BrandModelController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('zones', ZoneController::class);
    Route::resource('vehicles', VehicleController::class);
    // Rutas para programación masiva
    Route::get('schedulings/calendar', [SchedulingController::class, 'calendar'])->name('schedulings.calendar');
    Route::get('schedulings/{id}/details', [SchedulingController::class, 'getSchedulingDetails'])->name('schedulings.details');
    Route::get('schedulings/create-massive', [SchedulingController::class, 'createMassive'])->name('schedulings.create-massive');
    Route::post('schedulings/store-massive', [SchedulingController::class, 'storeMassive'])->name('schedulings.store-massive');
    // Endpoint separado para validar programación masiva sin crear registros
    Route::post('schedulings/validate-massive', [SchedulingController::class, 'validateMassive'])->name('schedulings.validate-massive');
    // Endpoint para actualizar configuración de un grupo desde la validación masiva
    Route::post('schedulings/update-group', [SchedulingController::class, 'updateGroupFromValidation'])->name('schedulings.update-group');
// Rutas para edición masiva
Route::get('schedulings/edit-massive', [SchedulingController::class, 'editMassive'])
    ->name('schedulings.edit-massive');
Route::post('schedulings/update-massive', [SchedulingController::class, 'updateMassive'])
    ->name('schedulings.update-massive');
Route::post('/schedulings/fetch-massive', [SchedulingController::class, 'fetchMassive'])
    ->name('schedulings.fetch-massive');

    Route::resource('schedulings', SchedulingController::class);

    Route::resource('vacations', VacationController::class);
    Route::resource('contracts', ContractController::class);

    // Ruta AJAX para obtener sectores por distrito
    Route::get('zones/sectors/{districtId}', [ZoneController::class, 'getSectorsByDistrict'])->name('zones.sectors');

    // Ruta AJAX para obtener modelos por marca
    Route::get('vehicles/models/{brandId}', [VehicleController::class, 'getModelsByBrand'])->name('vehicles.models');

    // Rutas AJAX para manejo de imágenes
    Route::post('vehicles/{vehicleId}/images/{imageId}/profile', [VehicleController::class, 'setProfileImage'])->name('vehicles.images.profile');
    Route::delete('vehicles/{vehicleId}/images/{imageId}', [VehicleController::class, 'deleteImage'])->name('vehicles.images.delete');

    Route::resource('usertypes', UserTypeController::class);
    Route::resource('vehicletypes', VehicleTypeController::class);
    Route::resource('schedules', ScheduleController::class);
    Route::resource('attendances', AttendaceController::class);
    Route::resource('groups', EmployeeGroupController::class);
    Route::get('historyChanges/', [HistoryController::class, 'index'])->name('history.index');
});
