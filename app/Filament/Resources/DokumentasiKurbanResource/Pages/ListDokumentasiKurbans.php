<?php

namespace App\Filament\Resources\DokumentasiKurbanResource\Pages;

use App\Filament\Resources\DokumentasiKurbanResource;
use Filament\Resources\Pages\ListRecords;

class ListDokumentasiKurbans extends ListRecords
{
    protected static string $resource = DokumentasiKurbanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
