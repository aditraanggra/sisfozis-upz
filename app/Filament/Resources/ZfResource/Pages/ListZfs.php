<?php

namespace App\Filament\Resources\ZfResource\Pages;

use App\Filament\Resources\ZfResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListZfs extends ListRecords
{
    protected static string $resource = ZfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ZfResource\Widgets\ZfOverview::class,
        ];
    }
}
