<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\Vehicle\VehicleColorController;
use App\Http\Controllers\Api\Vehicle\BrandModelController;
use App\Http\Controllers\Api\Vehicle\BrandController;
use App\Http\Controllers\Api\Vehicle\VehicleTypeController;
use App\Http\Controllers\Api\Zones\ZoneController;
use App\Http\Controllers\Api\SectorController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\Schedule\ContractController;
use App\Http\Controllers\Api\Schedule\VacationController;
use App\Http\Controllers\Api\User\AttendaceController;
use App\Http\Controllers\Api\ZoneCoordController;
use App\Http\Controllers\Api\Schedule\SchedulingController; //programaciones
use App\Http\Controllers\Api\Schedule\ScheduleController; //turnos o shifts
use App\Http\Controllers\Api\User\UserTypeController;
use App\Http\Controllers\Api\Vehicle\VehicleController;
use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('welcome'); // O tu dashboard principal
    })->name('dashboard');


    Route::resource('personal', UserController::class);
    Route::resource('brand-models', BrandModelController::class);

});
