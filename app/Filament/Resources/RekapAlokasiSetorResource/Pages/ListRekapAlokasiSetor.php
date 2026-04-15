<?php

namespace App\Filament\Resources\RekapAlokasiSetorResource\Pages;

use App\Filament\Resources\RekapAlokasiSetorResource;
use App\Filament\Exports\RekapAlokasiSetorExporter;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListRekapAlokasiSetor extends ListRecords
{
    protected static string $resource = RekapAlokasiSetorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(RekapAlokasiSetorExporter::class)
                ->icon('heroicon-o-arrow-down-tray')
                ->label('Export Excel'),
        ];
    }
}
