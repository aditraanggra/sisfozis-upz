<?php

namespace App\Filament\Resources\ZmResource\Pages;

use App\Filament\Resources\ZmResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListZms extends ListRecords
{
    protected static string $resource = ZmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->visible(fn() => Auth::user() && Auth::user()->email === 'sa01@sisfoupz.org')
                ->sampleExcel(
                    sampleData: [
                        [
                            'unit_id' => 1,
                            'trx_date' => '2025-03-28',
                            'category_maal' => 'Pendapatan',
                            'muzakki_name' => 'muzakki',
                            'amount' => 250000,
                            'desc' => 'Contoh Transaksi Zakat Mal'
                        ],
                    ],
                    fileName: 'template_zm.xlsx',
                    sampleButtonLabel: 'Download Sample',
                ),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ZmResource\Widgets\ZmOverview::make([
                'tableFilters' => $this->tableFilters,
            ]),
        ];
    }
}
