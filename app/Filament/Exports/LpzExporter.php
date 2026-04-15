<?php

namespace App\Filament\Exports;

use App\Models\Lpz;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class LpzExporter extends Exporter
{
    protected static ?string $model = Lpz::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('unit.unit_name')->label('Nama UPZ'),
            ExportColumn::make('unit.district.name')->label('Kecamatan'),
            ExportColumn::make('unit.village.name')->label('Desa'),
            ExportColumn::make('trx_date')->label('Tanggal Laporan'),
            ExportColumn::make('lpz_year')->label('Tahun'),
            ExportColumn::make('form101')
                ->label('Form 101')
                ->formatStateUsing(fn ($state) => $state ? \App\Filament\Resources\LpzResource::getCloudinaryDownloadUrl($state) : '-'),
            ExportColumn::make('form102')
                ->label('Form 102')
                ->formatStateUsing(fn ($state) => $state ? \App\Filament\Resources\LpzResource::getCloudinaryDownloadUrl($state) : '-'),
            ExportColumn::make('lpz')
                ->label('LPZ')
                ->formatStateUsing(fn ($state) => $state ? \App\Filament\Resources\LpzResource::getCloudinaryDownloadUrl($state) : '-'),
            ExportColumn::make('created_at')->label('Dibuat Pada'),
            ExportColumn::make('updated_at')->label('Diubah Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your lpz export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
