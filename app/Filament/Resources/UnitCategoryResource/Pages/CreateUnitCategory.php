<?php

namespace App\Filament\Resources\UnitCategoryResource\Pages;

use App\Filament\Resources\UnitCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUnitCategory extends CreateRecord
{
    protected static string $resource = UnitCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
