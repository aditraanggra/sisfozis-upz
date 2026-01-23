<?php

namespace App\Filament\Resources\IfsResource\Pages;

use App\Filament\Resources\IfsResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListIfs extends ListRecords
{
    protected static string $resource = IfsResource::class;

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
                            'muzakki_name' => 'muzakki',
                            'amount' => 250000,
                            'desc' => 'Contoh Transaksi Zakat Mal'
                        ],
                    ],
                    fileName: 'template_ifs.xlsx',
                    sampleButtonLabel: 'Download Sample',
                ),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            IfsResource\Widgets\IfsOverview::make([
                'tableFilters' => $this->tableFilters,
            ]),
        ];
    }
}
