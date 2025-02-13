<?php

namespace App\Filament\Resources\UnitCategoryResource\Pages;

use App\Filament\Resources\UnitCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUnitCategories extends ListRecords
{
    protected static string $resource = UnitCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
