<?php

use Illuminate\Support\Facades\Route;
use App\Models\Village;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;

Route::get('/', function () {
    return view('welcome');
});

// Route to display PHP configuration information
/* Route::get('/info', function () {
    return phpinfo();
}); */

Route::get('/rekap-zis/{village}/pdf', function (Village $village) {
    $rekapZis = $village->rekapZis()
        ->where('period', 'tahunan')
        ->whereYear('period_date', 2025)
        ->get();

    $pdf = Pdf::loadHtml(
        Blade::render('filament.resources.Village-resource.pdf', [
            'record' => $village,
            'rekapZis' => $rekapZis,
        ])
    )->setPaper('a4', 'landscape');

    return $pdf->stream('Rekap-ZIS-' . str_replace(' ', '-', $village->name) . '.pdf');
})->name('village.pdf');

Route::get('/rekap-zis/{village}/op', function (Village $village) {
    $rekapZis = $village->rekapZis()
        ->where('period', 'tahunan')
        ->whereYear('period_date', 2025)
        ->get();

    $pdf = Pdf::loadHtml(
        Blade::render('filament.resources.Village-resource.op', [
            'record' => $village,
            'rekapZis' => $rekapZis,
        ])
    )->setPaper('a4', 'portrait');

    return $pdf->stream('Rekap-ZIS-' . str_replace(' ', '-', $village->name) . '.pdf');
})->name('op.pdf');
