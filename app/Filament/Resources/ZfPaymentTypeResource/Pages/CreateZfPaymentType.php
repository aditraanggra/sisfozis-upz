<?php

namespace App\Filament\Resources\ZfPaymentTypeResource\Pages;

use App\Filament\Resources\ZfPaymentTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateZfPaymentType extends CreateRecord
{
    protected static string $resource = ZfPaymentTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
