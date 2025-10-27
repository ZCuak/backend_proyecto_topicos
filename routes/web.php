<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Web\ZoneController;
use App\Http\Controllers\Web\VehicleController;
use App\Http\Controllers\Web\SchedulingController;
use App\Http\Controllers\Api\User\UserTypeController;
use App\Http\Controllers\Api\Vehicle\VehicleTypeController;
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
    Route::resource('zones', ZoneController::class);
    Route::resource('vehicles', VehicleController::class);
    Route::resource('schedulings', SchedulingController::class);
    
    // Ruta AJAX para obtener sectores por distrito
    Route::get('zones/sectors/{districtId}', [ZoneController::class, 'getSectorsByDistrict'])->name('zones.sectors');
    
    // Ruta AJAX para obtener modelos por marca
    Route::get('vehicles/models/{brandId}', [VehicleController::class, 'getModelsByBrand'])->name('vehicles.models');
    
    // Rutas AJAX para manejo de imágenes
    Route::post('vehicles/{vehicleId}/images/{imageId}/profile', [VehicleController::class, 'setProfileImage'])->name('vehicles.images.profile');
    Route::delete('vehicles/{vehicleId}/images/{imageId}', [VehicleController::class, 'deleteImage'])->name('vehicles.images.delete');
    
    Route::resource('usertypes', UserTypeController::class);
    Route::resource('vehicletypes', VehicleTypeController::class);

});
