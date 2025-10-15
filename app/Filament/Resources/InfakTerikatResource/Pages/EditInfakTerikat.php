<?php

namespace App\Filament\Resources\InfakTerikatResource\Pages;

use App\Filament\Resources\InfakTerikatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInfakTerikat extends EditRecord
{
    protected static string $resource = InfakTerikatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
