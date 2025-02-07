<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UnitZisController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(Authenticate::using('sanctum'));

//Ambil data Kecamatan
Route::get('/kecamatan', [\App\Http\Controllers\Api\DistrictController::class, 'index']);

//Ambil data Desa
Route::get('/desa', [\App\Http\Controllers\Api\VillageController::class, 'index']);
