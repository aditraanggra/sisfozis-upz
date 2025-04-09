<?php

namespace App\Filament\Resources\SetorZisResource\Pages;

use App\Filament\Resources\SetorZisResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSetorZis extends CreateRecord
{
    protected static string $resource = SetorZisResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
