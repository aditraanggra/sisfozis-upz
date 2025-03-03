<?php

namespace App\Filament\Resources\UnitZisResource\Pages;

use App\Filament\Resources\UnitZisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUnitZis extends EditRecord
{
    protected static string $resource = UnitZisResource::class;

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
