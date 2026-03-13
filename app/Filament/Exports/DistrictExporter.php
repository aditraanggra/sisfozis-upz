<?php

namespace App\Filament\Exports;

use App\Models\District;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Contracts\Database\Query\Builder;

class DistrictExporter extends Exporter
{
    protected static ?string $model = District::class;

    public static function getColumns(): array
    {
        return [
            // Basic District Information
            ExportColumn::make('name')
                ->label('Nama Kecamatan'),

            // Total ZIS Amounts
            ExportColumn::make('total_zis')
                ->label('Total ZIS')
                ->getStateUsing(function (District $record, \Filament\Actions\Exports\Exporter $exporter) {
                    $tahun = $exporter->getOptions()['tahun'] ?? date('Y');
                    $periodDate = $tahun . '-01-01';
                    return $record->rekapZis
                        ->where('period', 'tahunan')
                        ->where('period_date', $periodDate)
                        ->sum('total_zf_amount') +
                        $record->rekapZis
                        ->where('period', 'tahunan')
                        ->where('period_date', $periodDate)
                        ->sum('total_zm_amount') +
                        $record->rekapZis
                        ->where('period', 'tahunan')
                        ->where('period_date', $periodDate)
                        ->sum('total_ifs_amount');
                }),

            // Zakat Fitrah (Rice)
            ExportColumn::make('total_zf_rice')
                ->label('Zakat Fitrah (Beras)')
                ->getStateUsing(function (District $record, \Filament\Actions\Exports\Exporter $exporter) {
                    $tahun = $exporter->getOptions()['tahun'] ?? date('Y');
                    $periodDate = $tahun . '-01-01';
                    return $record->rekapZis
                        ->where('period', 'tahunan')
                        ->where('period_date', $periodDate)
                        ->sum('total_zf_rice');
                }),

            // Zakat Fitrah (Amount)
            ExportColumn::make('total_zf_amount')
                ->label('Zakat Fitrah (Uang)')
                ->getStateUsing(function (District $record, \Filament\Actions\Exports\Exporter $exporter) {
                    $tahun = $exporter->getOptions()['tahun'] ?? date('Y');
                    $periodDate = $tahun . '-01-01';
                    return $record->rekapZis
                        ->where('period', 'tahunan')
                        ->where('period_date', $periodDate)
                        ->sum('total_zf_amount');
                }),

            // Zakat Mal
            ExportColumn::make('total_zm_amount')
                ->label('Zakat Mal')
                ->getStateUsing(function (District $record, \Filament\Actions\Exports\Exporter $exporter) {
                    $tahun = $exporter->getOptions()['tahun'] ?? date('Y');
                    $periodDate = $tahun . '-01-01';
                    return $record->rekapZis
                        ->where('period', 'tahunan')
                        ->where('period_date', $periodDate)
                        ->sum('total_zm_amount');
                }),

            // Infak Sedekah
            ExportColumn::make('total_ifs_amount')
                ->label('Infak Sedekah')
                ->getStateUsing(function (District $record, \Filament\Actions\Exports\Exporter $exporter) {
                    $tahun = $exporter->getOptions()['tahun'] ?? date('Y');
                    $periodDate = $tahun . '-01-01';
                    return $record->rekapZis
                        ->where('period', 'tahunan')
                        ->where('period_date', $periodDate)
                        ->sum('total_ifs_amount');
                }),

            // Muzakki Zakat Fitrah
            ExportColumn::make('total_zf_muzakki')
                ->label('Muzakki Zakat Fitrah')
                ->getStateUsing(function (District $record, \Filament\Actions\Exports\Exporter $exporter) {
                    $tahun = $exporter->getOptions()['tahun'] ?? date('Y');
                    $periodDate = $tahun . '-01-01';
                    return $record->rekapZis
                        ->where('period', 'tahunan')
                        ->where('period_date', $periodDate)
                        ->sum('total_zf_muzakki');
                }),

            // Muzakki Zakat Mal
            ExportColumn::make('total_zm_muzakki')
                ->label('Muzakki Zakat Mal')
                ->getStateUsing(function (District $record, \Filament\Actions\Exports\Exporter $exporter) {
                    $tahun = $exporter->getOptions()['tahun'] ?? date('Y');
                    $periodDate = $tahun . '-01-01';
                    return $record->rekapZis
                        ->where('period', 'tahunan')
                        ->where('period_date', $periodDate)
                        ->sum('total_zm_muzakki');
                }),

            // Munfiq (Infak Sedekah Contributors)
            ExportColumn::make('total_ifs_munfiq')
                ->label('Munfiq')
                ->getStateUsing(function (District $record, \Filament\Actions\Exports\Exporter $exporter) {
                    $tahun = $exporter->getOptions()['tahun'] ?? date('Y');
                    $periodDate = $tahun . '-01-01';
                    return $record->rekapZis
                        ->where('period', 'tahunan')
                        ->where('period_date', $periodDate)
                        ->sum('total_ifs_munfiq');
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your district export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
