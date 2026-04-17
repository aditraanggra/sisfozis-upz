<?php

namespace App\Filament\Resources\LpzResource\Pages;

use App\Filament\Exports\LpzExporter;
use App\Filament\Imports\LpzExcelImport;
use App\Filament\Resources\LpzResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLpzs extends ListRecords
{
    protected static string $resource = LpzResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->use(LpzExcelImport::class)
                ->color('success')
                ->visible(fn () => User::currentIsSuperAdmin())
                ->sampleExcel(
                    sampleData: [
                        [
                            'unit_id' => 1,
                            'trx_date' => '2025-03-28',
                            'lpz_year' => 2025,
                            'form101' => '',
                            'form102' => '',
                            'lpz' => '',
                        ],
                    ],
                    fileName: 'template_lpz.xlsx',
                    exportClass: \App\Exports\LpzSampleExport::class,
                    sampleButtonLabel: 'Download Template',
                ),
            Actions\ExportAction::make()
                ->exporter(LpzExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
