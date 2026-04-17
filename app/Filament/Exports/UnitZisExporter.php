<?php

namespace App\Filament\Exports;

use App\Models\UnitZis;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UnitZisExporter extends Exporter
{
    protected static ?string $model = UnitZis::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('user.name')
                ->label('Operator'),
            ExportColumn::make('category.name')
                ->label('Unit Kerja'),
            ExportColumn::make('district.name')
                ->label('Kecamatan'),
            ExportColumn::make('village.name')
                ->label('Desa'),
            ExportColumn::make('no_sk')
                ->label('Nomor SK'),
            ExportColumn::make('unit_name')
                ->label('Nama Unit'),
            ExportColumn::make('no_register')
                ->label('Nomor Register'),
            ExportColumn::make('address')
                ->label('Alamat'),
            ExportColumn::make('unit_leader')
                ->label('Ketua'),
            ExportColumn::make('unit_assistant')
                ->label('Sekretaris'),
            ExportColumn::make('unit_finance')
                ->label('Bendahara'),
            ExportColumn::make('operator_phone')
                ->label('Nomor Telepon Operator'),
            ExportColumn::make('rice_price')
                ->label('Harga Beras'),
            ExportColumn::make('is_verified')
                ->label('Terverifikasi')
                ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
            ExportColumn::make('profile_completion')
                ->label('Indeks Profil'),
            ExportColumn::make('created_at')
                ->label('Dibuat Pada'),
            ExportColumn::make('updated_at')
                ->label('Diubah Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor daftar UPZ Anda telah selesai dan ' . number_format($export->successful_rows) . ' ' . str('baris')->plural($export->successful_rows) . ' telah diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('baris')->plural($failedRowsCount) . ' gagal diekspor.';
        }

        return $body;
    }
}
