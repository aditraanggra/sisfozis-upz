<?php

namespace App\Filament\Clusters\Dskl\Resources\KurbanResource\Pages;

use App\Filament\Clusters\Dskl\Resources\KurbanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKurban extends CreateRecord
{
    protected static string $resource = KurbanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
