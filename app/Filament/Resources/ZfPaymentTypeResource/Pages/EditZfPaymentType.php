<?php

namespace App\Filament\Resources\ZfPaymentTypeResource\Pages;

use App\Filament\Resources\ZfPaymentTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditZfPaymentType extends EditRecord
{
    protected static string $resource = ZfPaymentTypeResource::class;

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
