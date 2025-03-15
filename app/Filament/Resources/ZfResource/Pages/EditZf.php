<?php

namespace App\Filament\Resources\ZfResource\Pages;

use App\Filament\Resources\ZfResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditZf extends EditRecord
{
    protected static string $resource = ZfResource::class;

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
