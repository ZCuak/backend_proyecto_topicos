<?php

use App\Http\Controllers\Api\Schedule\ScheduleController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\User\UserTypeController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\Vehicle\VehicleColorController;
use App\Http\Controllers\Api\Vehicle\BrandModelController;
use App\Http\Controllers\Api\Vehicle\BrandController;

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

// Rutas de prueba - CORS configurado globalmente
Route::get('/test', [TestController::class, 'test']);
Route::get('/data', [TestController::class, 'getData']);
Route::post('/create', [TestController::class, 'create']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/authenticate', [AuthController::class, 'authenticate']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

});

Route::apiResource('brand-models', BrandModelController::class);
Route::apiResource('brands', BrandController::class);
Route::apiResource('vehicle-colors', VehicleColorController::class);
Route::apiResource('user-types', UserTypeController::class);
Route::apiResource('persona', UserController::class);
Route::apiResource('schedules', ScheduleController::class);