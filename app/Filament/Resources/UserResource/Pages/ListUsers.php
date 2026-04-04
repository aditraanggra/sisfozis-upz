<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Imports\UserExcelImport;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->use(UserExcelImport::class)
                ->label('Import Excel')
                ->color('success')
                ->visible(fn () => User::currentIsSuperAdmin())
                ->sampleExcel(
                    sampleData: [
                        [
                            'name' => 'Ahmad Soleh',
                            'email' => 'ahmad.soleh@example.com',
                            'password' => 'password123',
                            'district_id' => 1,
                            'village_id' => 1,
                        ],
                    ],
                    fileName: 'template_user.xlsx',
                    sampleButtonLabel: 'Download Template',
                ),
            Actions\CreateAction::make(),
        ];
    }
}
