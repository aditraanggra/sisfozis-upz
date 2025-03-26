<?php

namespace App\Filament\Resources\RekapZisResource\Pages;

use App\Filament\Resources\RekapZisResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRekapZis extends CreateRecord
{
    protected static string $resource = RekapZisResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
