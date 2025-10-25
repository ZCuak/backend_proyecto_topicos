<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\User\UserController;
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
    Route::resource('usertypes', UserTypeController::class);
    Route::resource('vehicletypes', VehicleTypeController::class);

});
