<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(Authenticate::using('sanctum'));

//Ambil data Kecamatan
//Route::get('/kecamatan', [\App\Http\Controllers\Api\DistrictController::class, 'index']);
Route::apiResource('/kecamatan', \App\Http\Controllers\Api\DistrictController::class);

//Ambil data Desa
//Route::get('/desa', [\App\Http\Controllers\Api\VillageController::class, 'index']);
Route::apiResource('/desa', \App\Http\Controllers\Api\VillageController::class);

//Ambil data UPZ
Route::apiResource('/unit-zis', \App\Http\Controllers\Api\UnitZisController::class);
