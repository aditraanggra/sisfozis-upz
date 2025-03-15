<?php

namespace App\Filament\Resources\IfsResource\Pages;

use App\Filament\Resources\IfsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIfs extends ListRecords
{
    protected static string $resource = IfsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            IfsResource\Widgets\IfsOverview::class,
        ];
    }
}
