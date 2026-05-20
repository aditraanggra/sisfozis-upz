<?php

namespace App\Filament\Resources\LaporanKurbanResource\Pages;

use App\Filament\Resources\LaporanKurbanResource;
use Filament\Resources\Pages\ListRecords;

class ListLaporanKurbans extends ListRecords
{
    protected static string $resource = LaporanKurbanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
