<?php

namespace App\Filament\Resources\InfakTerikatResource\Pages;

use App\Filament\Resources\InfakTerikatResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInfakTerikats extends ListRecords
{
    protected static string $resource = InfakTerikatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->visible(fn() => User::currentIsSuperAdmin())
                ->sampleExcel(
                    sampleData: [
                        [
                            'unit_id' => 1, 
                            'program_id' => 1, 
                            'trx_date' => '2025-03-28', 
                            'munfiq_name' => 'Nama Munfiq', 
                            'amount' => 500000, 
                            'desc' => 'Contoh Infak Terikat'
                        ],
                    ],
                    fileName: 'template_infak_terikat.xlsx',
                    sampleButtonLabel: 'Download Sample',
                ),
            //Actions\CreateAction::make(),
        ];
    }
}
