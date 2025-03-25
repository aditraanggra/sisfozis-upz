<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Services\RekapUnitService;
use Illuminate\Console\Command;

class RebuildRekapUnit extends Command
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
                            {--periode=all : Periode yang ingin dibangun (harian, mingguan, bulanan, tahunan, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild rekap Unit data based on rekap zis data';

    /**
     * Execute the console command.
     *
     * @param RekapUnitService $rekapUnitService
     * @return int
     */
    public function handle(RekapUnitService $rekapUnitService)
    {
        // Ambil opsi yang diberikan
        $unit = $this->option('unit');
        $periode = $this->option('periode');
        $startDate = $this->option('start');
        $endDate = $this->option('end');

        $this->info('Starting rekap Unit rebuild process...');

        // Validasi tanggal
        if ($startDate && !$this->isValidDate($startDate)) {
            $this->error('Format tanggal mulai tidak valid. Gunakan format Y-m-d');
            return 1;
        }

        if ($endDate && !$this->isValidDate($endDate)) {
            $this->error('Format tanggal akhir tidak valid. Gunakan format Y-m-d');
            return 1;
        }

        // Validasi periode
        $allowedPeriodes = ['harian', 'mingguan', 'bulanan', 'tahunan', 'all'];
        if (!in_array($periode, $allowedPeriodes)) {
            $this->error('Periode tidak valid. Pilih: ' . implode(', ', $allowedPeriodes));
            return 1;
        }

        try {
            // Proses rebuild rekap unit
            $result = $rekapUnitService->rebuildRekapUnit(
                $unit,
                $periode,
                $startDate ? Carbon::parse($startDate) : null,
                $endDate ? Carbon::parse($endDate) : null
            );

            $this->info('Proses rebuild rekap unit selesai.');

            return 0;
        } catch (\Exception $e) {
            $this->error('Gagal melakukan rebuild: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Validasi format tanggal
     *
     * @param string $date
     * @return bool
     */
    private function isValidDate($date)
    {
        return \DateTime::createFromFormat('Y-m-d', $date) !== false;
    }
}
