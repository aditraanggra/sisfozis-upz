<?php

use App\Models\District;
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
        ->where(function ($query) {
            $query->where('total_zf_amount', '>', 0)
                ->orWhere('total_zm_amount', '>', 0)
                ->orWhere('total_ifs_amount', '>', 0);
        })
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
        ->where(function ($query) {
            $query->where('total_zf_amount', '>', 0)
                ->orWhere('total_zm_amount', '>', 0)
                ->orWhere('total_ifs_amount', '>', 0);
        })
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

Route::get('/rekap-zis/{district}/report', function (District $district) {
    $rekapZis = $district->rekapZis()
        ->where('period', 'tahunan')
        ->where('category_id', 4)
        ->where(function ($query) {
            $query->where('total_zf_amount', '>', 0)
                ->orWhere('total_zm_amount', '>', 0)
                ->orWhere('total_ifs_amount', '>', 0);
        })
        ->whereYear('period_date', 2025)
        ->get();

    $pdf = Pdf::loadHtml(
        Blade::render('filament.resources.district-resource.report', [
            'record' => $district,
            'rekapZis' => $rekapZis,
        ])
    )->setPaper('a4', 'landscape');

    return $pdf->stream('Rekap-ZIS-' . str_replace(' ', '-', $district->name) . '.pdf');
})->name('report.pdf');
