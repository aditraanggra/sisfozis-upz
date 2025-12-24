<?php

namespace App\Console\Commands;

use App\Services\RekapUnitService;

/**
 * Command to rebuild Unit recapitulation data.
 * 
 * Extends BaseRebuildCommand to leverage standardized interface and
 * common functionality for all rebuild operations.
 * 
 * @see Requirements 5.1
 */
class RebuildRekapUnit extends BaseRebuildCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unit:rebuild 
                            {--unit=all : ID unit atau "all" untuk semua unit}
                            {--start= : Tanggal mulai format Y-m-d}
                            {--end= : Tanggal akhir format Y-m-d}
                            {--periode=all : Periode (harian, bulanan, tahunan, all)}
                            {--chunk-size=50 : Jumlah unit per batch}
                            {--queue : Jalankan sebagai background job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild rekap Unit data based on rekap zis data';

    /**
     * The fully qualified class name of the rekap service to use.
     *
     * @var string
     */
    protected string $serviceClass = RekapUnitService::class;

    /**
     * Human-readable name of the rekap type for display purposes.
     *
     * @var string
     */
    protected string $rekapType = 'Rekapitulasi Unit';
}
