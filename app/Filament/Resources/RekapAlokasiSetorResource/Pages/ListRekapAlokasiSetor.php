<?php

namespace App\Filament\Resources\RekapAlokasiSetorResource\Pages;

use App\Filament\Resources\RekapAlokasiSetorResource;
use Filament\Resources\Pages\ListRecords;

class ListRekapAlokasiSetor extends ListRecords
{
    protected static string $resource = RekapAlokasiSetorResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
