<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RekapHakAmilService;
use App\Models\UnitZis;
use Carbon\Carbon;

class RebuildRekapHakAmil extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amil:rebuild 
                            {--unit=all : ID unit atau "all" untuk semua unit} 
                            {--start= : Tanggal mulai format Y-m-d} 
                            {--end= : Tanggal akhir format Y-m-d}
                            {--periode=all : Periode yang ingin dibangun (harian, mingguan, bulanan, tahunan, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membangun ulang tabel rekapitulasi Hak Amil untuk periode tertentu';

    /**
     * Execute the console command.
     *
     * @param RekapHakAmilService $rekapitulasiService
     * @return int
     */
    public function handle(RekapHakAmilService $rekapitulasiHakAmil)
    {
        $unitOption = $this->option('unit');
        $startDate = $this->option('start') ? Carbon::parse($this->option('start')) : Carbon::now()->subMonth();
        $endDate = $this->option('end') ? Carbon::parse($this->option('end')) : Carbon::now();
        $periode = $this->option('periode');

        $this->info("Membangun ulang rekapitulasi dari {$startDate->format('Y-m-d')} sampai {$endDate->format('Y-m-d')}");

        // Tentukan unit yang akan dihitung
        $units = $unitOption === 'all'
            ? UnitZis::all()
            : UnitZis::where('id', $unitOption)->get();

        if ($units->isEmpty()) {
            $this->error("Unit tidak ditemukan!");
            return 1;
        }

        $bar = $this->output->createProgressBar(count($units) * $startDate->diffInDays($endDate) + 1);
        $bar->start();

        foreach ($units as $unit) {
            $this->info("\nMemproses Unit: {$unit->name}");

            // Loop melalui setiap hari dalam rentang tanggal
            $currentDate = clone $startDate;
            while ($currentDate <= $endDate) {
                // Update rekapitulasi harian
                if ($periode === 'all' || $periode === 'harian') {
                    $rekapitulasiHakAmil->updateDailyRekapHakAmil($currentDate, $unit->id);
                }

                // Pada hari terakhir bulan, update rekapitulasi bulanan
                if (($periode === 'all' || $periode === 'bulanan') && $currentDate->day === $currentDate->daysInMonth) {
                    $rekapitulasiHakAmil->updateMonthlyRekapHakAmil($currentDate->month, $currentDate->year, $unit->id);
                }

                // Pada hari terakhir tahun, update rekapitulasi tahunan
                if (($periode === 'all' || $periode === 'tahunan') && $currentDate->month === 12 && $currentDate->day === 31) {
                    $rekapitulasiHakAmil->updateYearlyRekapHakAmil($currentDate->year, $unit->id);
                }

                $currentDate->addDay();
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Rekapitulasi berhasil dibangun ulang!');

        return 0;
    }
}
