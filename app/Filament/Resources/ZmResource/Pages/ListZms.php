<?php

namespace App\Filament\Resources\ZmResource\Pages;

use App\Filament\Resources\ZmResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListZms extends ListRecords
{
    protected static string $resource = ZmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ZmResource\Widgets\ZmOverview::class,
        ];
    }
}
