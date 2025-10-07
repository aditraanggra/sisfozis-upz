<?php

namespace App\Filament\Clusters\Dskl\Resources\FidyahResource\Pages;

use App\Filament\Clusters\Dskl\Resources\FidyahResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFidyah extends CreateRecord
{
    protected static string $resource = FidyahResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
