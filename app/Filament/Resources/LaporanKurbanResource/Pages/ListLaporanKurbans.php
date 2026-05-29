<?php

namespace App\Filament\Resources\LaporanKurbanResource\Pages;

use App\Filament\Exports\LaporanKurbanExporter;
use App\Filament\Imports\LaporanKurbanExcelImport;
use App\Filament\Resources\LaporanKurbanResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporanKurbans extends ListRecords
{
    protected static string $resource = LaporanKurbanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->use(LaporanKurbanExcelImport::class)
                ->additionalData(['user_id' => auth()->id()])
                ->color('success')
                ->visible(fn () => User::currentIsSuperAdmin())
                ->sampleExcel(
                    sampleData: [
                        [
                            'unit_id' => 1,
                            'tahun_hijriah' => '1446',
                            'tahun_masehi' => 2025,
                            'jml_sapi' => 0,
                            'jml_kerbau' => 0,
                            'jml_kambing' => 0,
                            'jml_domba' => 0,
                            'total_ekor' => 0,
                            'total_shohibul' => 0,
                            'shohibul_dikoreksi' => 'Tidak',
                            'alasan_koreksi' => '',
                            'jml_paket_daging' => 0,
                            'jml_penerima' => 0,
                            'kelompok_penerima' => 'Fakir,Miskin',
                            'catatan' => '',
                            'status' => 'draft',
                        ],
                    ],
                    fileName: 'template_laporan_kurban.xlsx',
                    exportClass: \App\Exports\LaporanKurbanSampleExport::class,
                    sampleButtonLabel: 'Download Template',
                ),
            Actions\ExportAction::make()
                ->exporter(LaporanKurbanExporter::class),
        ];
    }
}
