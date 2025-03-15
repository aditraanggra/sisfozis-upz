<?php

namespace App\Filament\Resources\ZfResource\Pages;

use App\Filament\Resources\ZfResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateZf extends CreateRecord
{
    protected static string $resource = ZfResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
