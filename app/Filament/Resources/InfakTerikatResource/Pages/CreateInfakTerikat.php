<?php

namespace App\Filament\Resources\InfakTerikatResource\Pages;

use App\Filament\Resources\InfakTerikatResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInfakTerikat extends CreateRecord
{
    protected static string $resource = InfakTerikatResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
