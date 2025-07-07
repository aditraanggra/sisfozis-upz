<?php

namespace App\Filament\Resources\ZfResource\Pages;

use App\Filament\Resources\ZfResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListZfs extends ListRecords
{
    protected static string $resource = ZfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->visible(fn() => User::currentIsSuperAdmin())
                ->sampleExcel(
                    sampleData: [
                        ['unit_id' => 1, 'trx_date' => '2025-03-28', 'zf_rice' => 0, 'zf_amount' => 38000, 'total_muzakki' => 1, 'muzakki_name' => 'John Doe', 'desc' => 'Contoh Transaksi'],
                        ['unit_id' => 1, 'trx_date' => '2025-03-29', 'zf_rice' => 0.5, 'zf_amount' => 0, 'total_muzakki' => 1, 'muzakki_name' => 'Jane Doe', 'desc' => 'Contoh Transaksi 2'],
                    ],
                    fileName: 'template_zf.xlsx',
                    sampleButtonLabel: 'Download Sample',
                ),
            //Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ZfResource\Widgets\ZfOverview::class,
        ];
    }
}
