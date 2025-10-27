<?php

use App\Http\Controllers\Api\Schedule\SchedulingController; //programaciones
use App\Http\Controllers\Api\Schedule\ScheduleController; //turnos o shifts
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\User\UserTypeController;
use App\Http\Controllers\Api\Vehicle\VehicleController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\Api\User\AttendaceController;
use App\Http\Controllers\Api\ZoneCoordController;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas públicas (sin autenticación)
Route::get('/test', [TestController::class, 'test']);
Route::get('/data', [TestController::class, 'getData']);
Route::post('/create', [TestController::class, 'create']);
Route::post('attendances/mark', [AttendaceController::class, 'markAttendance']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [UserController::class, 'store']);

Route::get('/storage', function () {
    Artisan::call('storage:link');
});

// Rutas protegidas (requieren token de autenticación)
Route::middleware('auth:sanctum')->group(function () {
    // Autenticación
    Route::get('/authenticate', [AuthController::class, 'authenticate']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Recursos protegidos
    // Route::apiResource('brand-models', BrandModelController::class);
    // Route::apiResource('brands', BrandController::class);
    Route::apiResource('vehicle-colors', VehicleColorController::class);
    Route::apiResource('user-types', UserTypeController::class);
    Route::apiResource('persona', UserController::class);
    Route::apiResource('schedulings', SchedulingController::class);

    Route::apiResource('vehicles', VehicleController::class);
    Route::apiResource('zones-api', ZoneController::class);
    Route::apiResource('sectors', SectorController::class);
    Route::apiResource('districts', DistrictController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('provinces', ProvinceController::class);
    Route::apiResource('zone-coords', ZoneCoordController::class);
    //Route::apiResource('contracts', ContractController::class);
    //Route::apiResource('vacations', VacationController::class);
    Route::apiResource('vehicle-types', VehicleTypeController::class);
    Route::apiResource('attendances1', AttendaceController::class);
    
    // Ruta para obtener sectores por distrito
    Route::get('sectors', [SectorController::class, 'index']);
});
