<?php

namespace App\Filament\Clusters\Dskl\Resources\KurbanResource\Pages;

use App\Filament\Clusters\Dskl\Resources\KurbanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKurban extends EditRecord
{
    protected static string $resource = KurbanResource::class;

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
