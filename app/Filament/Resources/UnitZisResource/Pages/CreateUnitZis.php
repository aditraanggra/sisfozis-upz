<?php

namespace App\Filament\Resources\UnitZisResource\Pages;

use App\Filament\Resources\UnitZisResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUnitZis extends CreateRecord
{
    protected static string $resource = UnitZisResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
