<?php

namespace App\Filament\Resources\ZmResource\Pages;

use App\Filament\Resources\ZmResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditZm extends EditRecord
{
    protected static string $resource = ZmResource::class;

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
