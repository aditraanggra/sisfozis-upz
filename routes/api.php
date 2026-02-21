<?php

use App\Http\Controllers\Api\AllocationConfigController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DistributionController;
use App\Http\Controllers\Api\DonationBoxController;
use App\Http\Controllers\Api\FidyahController;
use App\Http\Controllers\Api\IfsController;
use App\Http\Controllers\Api\SetorZisController;
use App\Http\Controllers\Api\UnitZisController;
use App\Http\Controllers\Api\ZfController;
use App\Http\Controllers\Api\ZfPaymentTypeController;
use App\Http\Controllers\Api\ZmController;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(Authenticate::using('sanctum'));

// Ambil data Kecamatan
Route::get('/kecamatan', [\App\Http\Controllers\Api\DistrictController::class, 'index']);
// Route::apiResource('/kecamatan', \App\Http\Controllers\Api\DistrictController::class);

// Ambil data Desa
Route::get('/desa', [\App\Http\Controllers\Api\VillageController::class, 'index']);
// Route::apiResource('/desa', \App\Http\Controllers\Api\VillageController::class);

// Ambil data UPZ

// Jenis Pembayaran Zakat Fitrah (public read, auth for CUD)
Route::get('/zf-payment-types', [ZfPaymentTypeController::class, 'index']);
Route::get('/zf-payment-types/{zfPaymentType}', [ZfPaymentTypeController::class, 'show']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route::apiResource('/unit-zis', \App\Http\Controllers\Api\UnitZisController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/unit-zis/{id}', [\App\Http\Controllers\Api\UnitZisController::class, 'update']);

    Route::apiResources([
        'zf' => ZfController::class,
        'zm' => ZmController::class,
        'ifs' => IfsController::class,
        'fidyah' => FidyahController::class,
        'kotak_amal' => DonationBoxController::class,
        'pendis' => DistributionController::class,
        'setor' => SetorZisController::class,
        'unit-zis' => UnitZisController::class,
    ]);



    // Jenis Pembayaran ZF (CUD - admin only)
    Route::post('/zf-payment-types', [ZfPaymentTypeController::class, 'store']);
    Route::put('/zf-payment-types/{zfPaymentType}', [ZfPaymentTypeController::class, 'update']);
    Route::delete('/zf-payment-types/{zfPaymentType}', [ZfPaymentTypeController::class, 'destroy']);

    // Allocation Config (konfigurasi persentase alokasi ZIS)
    Route::apiResource('allocation-configs', AllocationConfigController::class);
    Route::get('allocation-configs-active', [AllocationConfigController::class, 'getActive']);

    // Menggunakan prefix '/rekap/' untuk semua endpoint
    Route::prefix('rekap')->group(function () {
        // RekapZis Routes
        Route::get('zis', [App\Http\Controllers\RekapZisController::class, 'index']);
        Route::get('zis/{rekapZis}', [App\Http\Controllers\RekapZisController::class, 'show']);
        Route::get('zis-summary', [App\Http\Controllers\RekapZisController::class, 'summary']);
        Route::get('zis-monthly', [App\Http\Controllers\RekapZisController::class, 'monthlyStats']);

        // RekapAlokasi Routes
        Route::get('alokasi', [App\Http\Controllers\RekapAlokasiController::class, 'index']);
        Route::get('alokasi/{rekapAlokasi}', [App\Http\Controllers\RekapAlokasiController::class, 'show']);
        Route::get('alokasi-summary', [App\Http\Controllers\RekapAlokasiController::class, 'summary']);
        Route::get('alokasi-monthly', [App\Http\Controllers\RekapAlokasiController::class, 'monthlyStats']);

        // RekapPendis Routes
        Route::get('pendis', [App\Http\Controllers\RekapPendisController::class, 'index']);
        Route::get('pendis/{rekapPendis}', [App\Http\Controllers\RekapPendisController::class, 'show']);
        Route::get('pendis-summary', [App\Http\Controllers\RekapPendisController::class, 'summary']);
        Route::get('pendis-monthly', [App\Http\Controllers\RekapPendisController::class, 'monthlyStats']);
        Route::get('pendis-distribution', [App\Http\Controllers\RekapPendisController::class, 'distributionStats']);

        // RekapHakAmil Routes
        Route::get('hak-amil', [App\Http\Controllers\RekapHakAmilController::class, 'index']);
        Route::get('hak-amil/{rekapHakAmil}', [App\Http\Controllers\RekapHakAmilController::class, 'show']);
        Route::get('hak-amil-summary', [App\Http\Controllers\RekapHakAmilController::class, 'summary']);
        Route::get('hak-amil-monthly', [App\Http\Controllers\RekapHakAmilController::class, 'monthlyStats']);
        Route::get('hak-amil-distribution', [App\Http\Controllers\RekapHakAmilController::class, 'distributionStats']);

        // RekapSetor Routes
        Route::get('setor', [App\Http\Controllers\RekapSetorController::class, 'index']);
        Route::get('setor/{rekapSetor}', [App\Http\Controllers\RekapSetorController::class, 'show']);

        // Consolidated ZIS Report (single endpoint for PDF report generation)
        Route::get('zis-report', [App\Http\Controllers\ZisReportController::class, 'report']);

        // Alokasi Report (updated allocation data per ZIS type)
        Route::get('alokasi-report', [App\Http\Controllers\AlokasiReportController::class, 'report']);
    });
});
