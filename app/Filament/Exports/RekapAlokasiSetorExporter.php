<?php

namespace App\Filament\Exports;

use App\Models\RekapAlokasi;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class RekapAlokasiSetorExporter extends Exporter
{
    protected static ?string $model = RekapAlokasi::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('unit.unit_name')
                ->label('Nama UPZ'),
            ExportColumn::make('unit.district.name')
                ->label('Kecamatan'),
            ExportColumn::make('unit.village.name')
                ->label('Desa'),
            ExportColumn::make('periode_date')
                ->label('Tahun')
                ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('Y') : '-'),
            ExportColumn::make('total_setor_zf_amount')
                ->label('Alokasi ZF (Uang)'),
            ExportColumn::make('total_setor_zf_rice')
                ->label('Alokasi ZF (Beras kg)'),
            ExportColumn::make('total_setor_zm')
                ->label('Alokasi ZM (Uang)'),
            ExportColumn::make('total_setor_ifs')
                ->label('Alokasi IFS (Uang)'),
            ExportColumn::make('sudah_setor_zf_amount')
                ->label('Sudah Setor ZF (Uang)'),
            ExportColumn::make('sudah_setor_zf_rice')
                ->label('Sudah Setor ZF (Beras kg)'),
            ExportColumn::make('sudah_setor_zm')
                ->label('Sudah Setor ZM (Uang)'),
            ExportColumn::make('sudah_setor_ifs')
                ->label('Sudah Setor IFS (Uang)'),
            ExportColumn::make('sisa_zf_amount')
                ->label('Sisa ZF (Uang)'),
            ExportColumn::make('sisa_zf_rice')
                ->label('Sisa ZF (Beras kg)'),
            ExportColumn::make('sisa_zm')
                ->label('Sisa ZM (Uang)'),
            ExportColumn::make('sisa_ifs')
                ->label('Sisa IFS (Uang)'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export data Rekap Alokasi Setor telah selesai dan ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
