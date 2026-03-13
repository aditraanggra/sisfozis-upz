<?php

namespace App\Filament\Exports;

use App\Models\SetorZis;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SetorZisExporter extends Exporter
{
    protected static ?string $model = SetorZis::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('unit.unit_name')
                ->label('Nama UPZ'),
            ExportColumn::make('district.name')
                ->label('Kecamatan'),
            ExportColumn::make('village.name')
                ->label('Desa'),
            ExportColumn::make('trx_date')
                ->label('Tanggal Transaksi'),
            ExportColumn::make('zf_amount_deposit')
                ->label('Setor Zakat Fitrah (Uang)'),
            ExportColumn::make('zf_rice_deposit')
                ->label('Setor Zakat Fitrah (Beras)'),
            ExportColumn::make('zf_rice_sold_amount')
                ->label('Konversi Beras (Rp)'),
            ExportColumn::make('zf_rice_sold_price')
                ->label('Harga Beras/Kg (Rp)'),
            ExportColumn::make('zm_amount_deposit')
                ->label('Setor Zakat Mal (Uang)'),
            ExportColumn::make('ifs_amount_deposit')
                ->label('Setor Infaq Sedekah (Uang)'),
            ExportColumn::make('total_deposit')
                ->label('Total Setor'),
            ExportColumn::make('deposit_destination')
                ->label('Tujuan Setoran'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('validation')
                ->label('Validasi'),
            ExportColumn::make('created_at')
                ->label('Dibuat Pada'),
            ExportColumn::make('updated_at')
                ->label('Diperbarui Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export data Setor ZIS telah selesai dan ' . number_format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
