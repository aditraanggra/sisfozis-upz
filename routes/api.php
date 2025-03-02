<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DistributionController;
use App\Http\Controllers\Api\DonationBoxController;
use App\Http\Controllers\Api\FidyahController;
use App\Http\Controllers\Api\IfsController;
use App\Http\Controllers\Api\ZfController;
use App\Http\Controllers\Api\ZmController;

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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::apiResource('/unit-zis', \App\Http\Controllers\Api\UnitZisController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResources([
        'zf' => ZfController::class,
        'zm' => ZmController::class,
        'ifs' => IfsController::class,
        'fidyah' => FidyahController::class,
        'kotak_amal' => DonationBoxController::class,
        'pendis' => DistributionController::class,
    ]);
});
