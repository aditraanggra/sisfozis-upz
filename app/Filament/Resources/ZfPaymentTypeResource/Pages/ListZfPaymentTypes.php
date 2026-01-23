<?php

namespace App\Filament\Resources\ZfPaymentTypeResource\Pages;

use App\Filament\Resources\ZfPaymentTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListZfPaymentTypes extends ListRecords
{
    protected static string $resource = ZfPaymentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
