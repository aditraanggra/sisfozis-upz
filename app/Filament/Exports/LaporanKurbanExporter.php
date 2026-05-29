<?php

namespace App\Filament\Exports;

use App\Models\LaporanKurban;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class LaporanKurbanExporter extends Exporter
{
    protected static ?string $model = LaporanKurban::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('unit.unit_name')->label('Nama UPZ'),
            ExportColumn::make('unit.district.name')->label('Kecamatan'),
            ExportColumn::make('unit.village.name')->label('Desa'),
            ExportColumn::make('tahun_hijriah')->label('Tahun Hijriah'),
            ExportColumn::make('tahun_masehi')->label('Tahun Masehi'),
            ExportColumn::make('jml_sapi')->label('Sapi'),
            ExportColumn::make('jml_kerbau')->label('Kerbau'),
            ExportColumn::make('jml_kambing')->label('Kambing'),
            ExportColumn::make('jml_domba')->label('Domba'),
            ExportColumn::make('total_ekor')->label('Total Ekor'),
            ExportColumn::make('total_shohibul')->label('Shohibul'),
            ExportColumn::make('shohibul_dikoreksi')
                ->label('Shohibul Dikoreksi')
                ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
            ExportColumn::make('alasan_koreksi')->label('Alasan Koreksi'),
            ExportColumn::make('jml_paket_daging')->label('Paket Daging'),
            ExportColumn::make('jml_penerima')->label('Penerima'),
            ExportColumn::make('kelompok_penerima')
                ->label('Kelompok Penerima')
                ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('no_referensi')->label('No Referensi'),
            ExportColumn::make('catatan')->label('Catatan'),
            ExportColumn::make('created_at')->label('Dibuat Pada'),
            ExportColumn::make('updated_at')->label('Diubah Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your laporan kurban export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
