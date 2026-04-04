<?php

namespace App\Filament\Resources\UnitZisResource\Pages;

use App\Filament\Imports\UnitZisExcelImport;
use App\Filament\Resources\UnitZisResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUnitZis extends ListRecords
{
    protected static string $resource = UnitZisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->use(UnitZisExcelImport::class)
                ->label('Import Excel')
                ->color('success')
                ->visible(fn () => User::currentIsSuperAdmin())
                ->sampleExcel(
                    sampleData: [
                        [
                            'user_id' => 1,
                            'category_id' => 2,
                            'village_id' => 1,
                            'district_id' => 1,
                            'no_sk' => 'SK-001/2025',
                            'unit_name' => 'UPZ Masjid Al-Ikhlas',
                            'no_register' => '3201011',
                            'address' => 'Jl. Contoh No. 1',
                            'unit_leader' => 'Ahmad Soleh',
                            'unit_assistant' => 'Budi Santoso',
                            'unit_finance' => 'Citra Dewi',
                            'operator_phone' => '08123456789',
                            'rice_price' => 15000,
                            'is_verified' => 1,
                            'profile_completion' => 100,
                        ],
                    ],
                    fileName: 'template_unit_zis.xlsx',
                    sampleButtonLabel: 'Download Template',
                ),
            Actions\CreateAction::make(),
        ];
    }
}
