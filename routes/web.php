<?php

use App\Models\AllocationConfig;
use App\Models\District;
use App\Models\SetorZis;
use App\Models\Village;
use App\Services\AllocationConfigService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route to display PHP configuration information
/* Route::get('/info', function () {
    return phpinfo();
}); */

Route::get('/rekap-zis/{village}/pdf', function (Village $village) {
    $year = request()->query('year', now()->format('Y'));

    // Get all rekap_zis for this village, tahunan period, matching year
    $rekapZis = $village->rekapZis()
        ->where('period', 'tahunan')
        ->whereYear('period_date', $year)
        ->select('unit_id', 'total_zf_muzakki', 'total_zf_rice', 'total_zf_amount', 'total_zm_amount', 'total_zm_muzakki', 'total_ifs_amount', 'total_ifs_munfiq')
        ->get();

    // Fetch rice prices per unit_id from SetorZis
    $unitIds = $rekapZis->pluck('unit_id')->unique();
    $ricePrices = SetorZis::withoutGlobalScopes()
        ->whereIn('unit_id', $unitIds)
        ->whereYear('trx_date', $year)
        ->where('zf_rice_sold_price', '>', 0)
        ->groupBy('unit_id')
        ->selectRaw('unit_id, MAX(zf_rice_sold_price) as rice_price')
        ->pluck('rice_price', 'unit_id');

    // Get Category IDs dynamically
    $dkmCategory = \App\Models\UnitCategory::where('name', 'DKM')->firstOrFail();
    $dkmCategoryId = $dkmCategory->id;

    $desaCategory = \App\Models\UnitCategory::where('name', 'Desa')->firstOrFail();
    $desaCategoryId = $desaCategory->id;

    $kecamatanCategoryIds = \App\Models\UnitCategory::where('name', 'like', '%Kecamatan%')->pluck('id')->toArray();
    $excludedCategoryIds = array_unique(array_merge([$desaCategoryId], $kecamatanCategoryIds));

    // Get ALL DKM units in this village (Everything in this village EXCEPT Desa and Kecamatan units)
    $desaUnitIds = $village->unitzis()->where('category_id', $desaCategoryId)->pluck('id');
    $excludedUnitIds = $village->unitzis()->whereIn('category_id', $excludedCategoryIds)->pluck('id');
    $allDkms = $village->unitzis()->whereNotIn('id', $excludedUnitIds)->orderBy('unit_name')->get();

    // Extract direct collection data (UPZ Desa)
    $directCollectionRekaps = $rekapZis->filter(fn ($rekap) => $desaUnitIds->contains($rekap->unit_id));
    $directPricesWithValues = $directCollectionRekaps
        ->map(fn ($r) => $ricePrices[$r->unit_id] ?? null)
        ->filter(fn ($price) => $price !== null && $price > 0);
    $directAvgRicePrice = $directPricesWithValues->isNotEmpty() ? $directPricesWithValues->avg() : 0;
    $directCollection = collect([
        'total_zf_rice' => $directCollectionRekaps->sum('total_zf_rice'),
        'total_zf_amount' => $directCollectionRekaps->sum('total_zf_amount'),
        'total_zm_amount' => $directCollectionRekaps->sum('total_zm_amount'),
        'total_ifs_amount' => $directCollectionRekaps->sum('total_ifs_amount'),
        'total_zf_muzakki' => $directCollectionRekaps->sum('total_zf_muzakki'),
        'total_zm_muzakki' => $directCollectionRekaps->sum('total_zm_muzakki'),
        'total_ifs_munfiq' => $directCollectionRekaps->sum('total_ifs_munfiq'),
        'avg_rice_price' => $directAvgRicePrice,
    ]);

    // Aggregate rekap per DKM (Everything EXCEPT Desa and Kecamatan units)
    $dkmRekapsOnly = $rekapZis->filter(fn ($rekap) => ! $excludedUnitIds->contains($rekap->unit_id));
    $rekapByDkm = $dkmRekapsOnly->groupBy(fn ($rekap) => $rekap->unit_id)
        ->map(function ($unitRekaps) use ($ricePrices) {
            $avgRicePrice = $unitRekaps->avg(fn ($r) => $ricePrices[$r->unit_id] ?? 0);

            return [
                'total_zf_rice' => $unitRekaps->sum('total_zf_rice'),
                'total_zf_amount' => $unitRekaps->sum('total_zf_amount'),
                'total_zm_amount' => $unitRekaps->sum('total_zm_amount'),
                'total_ifs_amount' => $unitRekaps->sum('total_ifs_amount'),
                'total_zf_muzakki' => $unitRekaps->sum('total_zf_muzakki'),
                'total_zm_muzakki' => $unitRekaps->sum('total_zm_muzakki'),
                'total_ifs_munfiq' => $unitRekaps->sum('total_ifs_munfiq'),
                'avg_rice_price' => $avgRicePrice,
            ];
        });

    // Build summaries for DKMs (only those with transactions)
    $dkmSummaries = $allDkms->map(function ($dkm) use ($rekapByDkm) {
        $data = $rekapByDkm->get($dkm->id, []);

        return collect([
            'unit_name' => $dkm->unit_name,
            'total_zf_rice' => $data['total_zf_rice'] ?? 0,
            'total_zf_amount' => $data['total_zf_amount'] ?? 0,
            'total_zm_amount' => $data['total_zm_amount'] ?? 0,
            'total_ifs_amount' => $data['total_ifs_amount'] ?? 0,
            'total_zf_muzakki' => $data['total_zf_muzakki'] ?? 0,
            'total_zm_muzakki' => $data['total_zm_muzakki'] ?? 0,
            'total_ifs_munfiq' => $data['total_ifs_munfiq'] ?? 0,
            'avg_rice_price' => $data['avg_rice_price'] ?? 0,
        ]);
    })->filter(function ($summary) {
        return $summary['total_zf_rice'] > 0 ||
               $summary['total_zf_amount'] > 0 ||
               $summary['total_zm_amount'] > 0 ||
               $summary['total_ifs_amount'] > 0;
    })->values();

    $allocationService = app(AllocationConfigService::class);
    $periodDate = $year.'-01-01';
    $allocations = [
        'zf' => $allocationService->getAllocation(AllocationConfig::TYPE_ZF, $periodDate),
        'zm' => $allocationService->getAllocation(AllocationConfig::TYPE_ZM, $periodDate),
        'ifs' => $allocationService->getAllocation(AllocationConfig::TYPE_IFS, $periodDate),
    ];

    $pdf = Pdf::loadHtml(
        view('filament.resources.Village-resource.pdf', [
            'record' => $village,
            'directCollection' => $directCollection,
            'dkmSummaries' => $dkmSummaries,
            'year' => $year,
            'allocations' => $allocations,
        ])->render()
    )->setPaper('a4', 'landscape');

    return $pdf->stream('Rekap-ZIS-Per-DKM-Desa-'.str_replace(' ', '-', $village->name).'.pdf');
})->name('village.pdf');

Route::get('/rekap-zis/{village}/op', function (Village $village) {
    $year = request()->query('year', now()->format('Y'));

    $rekapZis = $village->rekapZis()
        ->with(['unit' => function ($query) {
            $query->select('id', 'unit_name'); // Assuming these are needed for 'op' view
        }])
        ->where('period', 'tahunan')
        ->where(function ($query) {
            $query->where('total_zf_amount', '>', 0)
                ->orWhere('total_zm_amount', '>', 0)
                ->orWhere('total_ifs_amount', '>', 0);
        })
        ->whereYear('period_date', $year)
        ->select('id', 'unit_id', 'total_zf_amount', 'total_zm_amount', 'total_ifs_amount', 'period_date') // Add other columns needed for 'op' view
        ->get();

    $pdf = Pdf::loadHtml(
        view('filament.resources.Village-resource.op', [
            'record' => $village,
            'rekapZis' => $rekapZis,
            'year' => $year,
        ])->render()
    )->setPaper('a4', 'portrait');

    return $pdf->stream('Rekap-ZIS-'.str_replace(' ', '-', $village->name).'.pdf');
})->name('op.pdf');

Route::get('/rekap-zis/{village}/lpz', function (Village $village) {
    $year = request()->query('year', now()->format('Y'));

    $units = $village->unitzis()->orderBy('unit_name')->get();

    $rekapZis = \App\Models\RekapZis::whereIn('unit_id', $units->pluck('id'))
        ->where('period', 'tahunan')
        ->whereYear('period_date', $year)
        ->select('unit_id', 'total_zf_rice', 'total_zf_amount', 'total_zf_muzakki', 'total_zm_amount', 'total_zm_muzakki', 'total_ifs_amount', 'total_ifs_munfiq')
        ->get()
        ->keyBy('unit_id');

    $rekapPendis = \App\Models\RekapPendis::whereIn('unit_id', $units->pluck('id'))
        ->where('periode', 'tahunan')
        ->whereYear('periode_date', $year)
        ->select('unit_id', 't_pendis_zf_rice', 't_pendis_zf_amount', 't_pm', 't_pendis_zm', 't_pendis_ifs')
        ->get()
        ->keyBy('unit_id');

    $rekapSetor = \App\Models\RekapSetor::whereIn('unit_id', $units->pluck('id'))
        ->where('periode', 'tahunan')
        ->whereYear('periode_date', $year)
        ->select('unit_id', 't_setor_zf_rice', 't_setor_zf_amount', 't_setor_zm', 't_setor_ifs')
        ->get()
        ->keyBy('unit_id');

    $setorZisGrouped = collect();
    \App\Models\SetorZis::withoutGlobalScopes()
        ->whereIn('unit_id', $units->pluck('id'))
        ->whereYear('trx_date', $year)
        ->select('unit_id', 'trx_date', 'zf_amount_deposit', 'zf_rice_deposit', 'zm_amount_deposit', 'ifs_amount_deposit', 'deposit_destination', 'upload')
        ->orderBy('trx_date')
        ->chunk(500, function ($chunk) use ($setorZisGrouped) {
            foreach ($chunk as $item) {
                if (! isset($setorZisGrouped[$item->unit_id])) {
                    $setorZisGrouped[$item->unit_id] = collect();
                }
                $setorZisGrouped[$item->unit_id]->push($item);
            }
        });

    $pdf = Pdf::setOption(['isRemoteEnabled' => true])->loadHtml(
        view('filament.resources.Village-resource.lpz', [
            'village' => $village,
            'units' => $units,
            'year' => $year,
            'rekapZis' => $rekapZis,
            'rekapPendis' => $rekapPendis,
            'rekapSetor' => $rekapSetor,
            'setorZisGrouped' => $setorZisGrouped,
        ])->render()
    )->setPaper('a4', 'portrait');

    return $pdf->stream('LPZ-Desa-'.str_replace(' ', '-', $village->name).'-'.$year.'.pdf');
})->name('village.lpz.pdf');

Route::get('/rekap-zis/{district}/rekap-desa', function (District $district) {
    $year = request()->query('year', now()->format('Y'));

    // Get all rekap_zis for this district, tahunan period, matching year (no filter on non-zero)
    $rekapZis = $district->rekapZis()
        ->with(['unit' => function ($query) {
            $query->select('id', 'category_id', 'village_id');
        }])
        ->where('period', 'tahunan')
        ->whereYear('period_date', $year)
        ->select('id', 'unit_id', 'total_zf_rice', 'total_zf_amount', 'total_zm_amount', 'total_ifs_amount', 'total_zf_muzakki', 'total_zm_muzakki', 'total_ifs_munfiq')
        ->get();

    // Fetch rice sold amounts per unit_id from SetorZis
    $unitIds = $rekapZis->pluck('unit_id')->unique();
    $riceSoldAmounts = SetorZis::withoutGlobalScopes()
        ->whereIn('unit_id', $unitIds)
        ->whereYear('trx_date', $year)
        ->groupBy('unit_id')
        ->selectRaw('unit_id, SUM(zf_rice_sold_amount) as total_sold_amount')
        ->pluck('total_sold_amount', 'unit_id');

    // Get ALL villages in this district
    $allVillages = $district->villages()->orderBy('name')->get();

    $upzCategoryId = \App\Models\UnitCategory::where('name', 'UPZ Kecamatan')->value('id');

    // Extract direct collection data (UPZ Kecamatan)
    $directCollectionRekaps = $rekapZis->filter(fn ($rekap) => $rekap->unit?->category_id == $upzCategoryId);
    $directZfRiceSoldAmount = $directCollectionRekaps->sum(fn ($r) => $riceSoldAmounts[$r->unit_id] ?? 0);
    $directCollection = collect([
        'total_zf_rice' => $directCollectionRekaps->sum('total_zf_rice'),
        'total_zf_amount' => $directCollectionRekaps->sum('total_zf_amount'),
        'total_zm_amount' => $directCollectionRekaps->sum('total_zm_amount'),
        'total_ifs_amount' => $directCollectionRekaps->sum('total_ifs_amount'),
        'total_zf_muzakki' => $directCollectionRekaps->sum('total_zf_muzakki'),
        'total_zm_muzakki' => $directCollectionRekaps->sum('total_zm_muzakki'),
        'total_ifs_munfiq' => $directCollectionRekaps->sum('total_ifs_munfiq'),
        'zf_rice_sold_amount' => $directZfRiceSoldAmount,
    ]);

    // Aggregate rekap per village (exclude UPZ Kecamatan)
    $villageRekapsOnly = $rekapZis->filter(fn ($rekap) => $rekap->unit?->category_id != $upzCategoryId);
    $rekapByVillage = $villageRekapsOnly->groupBy(fn ($rekap) => $rekap->unit?->village_id)
        ->map(function ($villageRekaps) use ($riceSoldAmounts) {
            $zfRiceSoldAmount = $villageRekaps->sum(fn ($r) => $riceSoldAmounts[$r->unit_id] ?? 0);

            return [
                'total_zf_rice' => $villageRekaps->sum('total_zf_rice'),
                'total_zf_amount' => $villageRekaps->sum('total_zf_amount'),
                'total_zm_amount' => $villageRekaps->sum('total_zm_amount'),
                'total_ifs_amount' => $villageRekaps->sum('total_ifs_amount'),
                'total_zf_muzakki' => $villageRekaps->sum('total_zf_muzakki'),
                'total_zm_muzakki' => $villageRekaps->sum('total_zm_muzakki'),
                'total_ifs_munfiq' => $villageRekaps->sum('total_ifs_munfiq'),
                'zf_rice_sold_amount' => $zfRiceSoldAmount,
            ];
        });

    // Build summaries for ALL villages (including those without transactions)
    $villageSummaries = $allVillages->map(function ($village) use ($rekapByVillage) {
        $data = $rekapByVillage->get($village->id, []);

        return collect([
            'village_name' => $village->name,
            'total_zf_rice' => $data['total_zf_rice'] ?? 0,
            'total_zf_amount' => $data['total_zf_amount'] ?? 0,
            'total_zm_amount' => $data['total_zm_amount'] ?? 0,
            'total_ifs_amount' => $data['total_ifs_amount'] ?? 0,
            'total_zf_muzakki' => $data['total_zf_muzakki'] ?? 0,
            'total_zm_muzakki' => $data['total_zm_muzakki'] ?? 0,
            'total_ifs_munfiq' => $data['total_ifs_munfiq'] ?? 0,
            'zf_rice_sold_amount' => $data['zf_rice_sold_amount'] ?? 0,
        ]);
    })->values();

    $allocationService = app(AllocationConfigService::class);
    $periodDate = $year.'-01-01';
    $allocations = [
        'zf' => $allocationService->getAllocation(AllocationConfig::TYPE_ZF, $periodDate),
        'zm' => $allocationService->getAllocation(AllocationConfig::TYPE_ZM, $periodDate),
        'ifs' => $allocationService->getAllocation(AllocationConfig::TYPE_IFS, $periodDate),
    ];

    $pdf = Pdf::loadHtml(
        view('filament.resources.district-resource.rekap-desa', [
            'record' => $district,
            'directCollection' => $directCollection,
            'villageSummaries' => $villageSummaries,
            'year' => $year,
            'allocations' => $allocations,
        ])->render()
    )->setPaper('a4', 'landscape');

    return $pdf->stream('Rekap-ZIS-Per-Desa-Kec-'.str_replace(' ', '-', $district->name).'.pdf');
})->name('district.rekap-desa.pdf');

Route::get('/rekap-zis/{district}/report', function (District $district) {
    $year = request()->query('year', now()->format('Y'));

    $rekapZis = $district->rekapZis()
        ->with(['unit' => function ($query) {
            $query->select('id', 'unit_name');
        }])
        ->where('period', 'tahunan')
        ->where('category_id', 4)
        ->where(function ($query) {
            $query->where('total_zf_amount', '>', 0)
                ->orWhere('total_zm_amount', '>', 0)
                ->orWhere('total_ifs_amount', '>', 0);
        })
        ->whereYear('period_date', $year)
        ->select('id', 'unit_id', 'total_zf_rice', 'total_zf_amount', 'total_zm_amount', 'total_ifs_amount', 'total_zf_muzakki', 'total_zm_muzakki', 'total_ifs_munfiq')
        ->get();

    $pdf = Pdf::loadHtml(
        view('filament.resources.district-resource.report', [
            'record' => $district,
            'rekapZis' => $rekapZis,
            'year' => $year,
        ])->render()
    )->setPaper('a4', 'landscape');

    return $pdf->stream('Rekap ZIS Se-Kecamatan '.str_replace(' ', '-', $district->name).'.pdf');
})->name('report.pdf');
