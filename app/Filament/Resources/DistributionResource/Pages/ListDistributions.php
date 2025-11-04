<?php

namespace App\Filament\Resources\DistributionResource\Pages;

use App\Filament\Resources\DistributionResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDistributions extends ListRecords
{
    protected static string $resource = DistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->visible(fn() => User::currentIsSuperAdmin())
                ->sampleExcel(
                    sampleData: [
                        [
                            'unit_id' => 1, 
                            'trx_date' => '2025-03-28', 
                            'mustahik_name' => 'Nama Mustahik', 
                            'nik' => '1234567890123456', 
                            'fund_type' => 'Zakat', 
                            'asnaf' => 'Fakir', 
                            'program' => 'Bantuan Pendidikan', 
                            'total_rice' => 5, 
                            'total_amount' => 500000, 
                            'beneficiary' => 1, 
                            'desc' => 'Contoh Pendistribusian'
                        ],
                    ],
                    fileName: 'template_pendistribusian.xlsx',
                    sampleButtonLabel: 'Download Sample',
                ),
            //Actions\CreateAction::make(),
        ];
    }
}
