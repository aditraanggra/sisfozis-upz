<?php

namespace App\Filament\Resources\ZmResource\Pages;

use App\Filament\Resources\ZmResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateZm extends CreateRecord
{
    protected static string $resource = ZmResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
