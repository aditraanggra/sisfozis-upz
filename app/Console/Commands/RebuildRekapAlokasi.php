<?php

namespace App\Console\Commands;

use App\Services\RekapAlokasiService;
use Illuminate\Console\Command;

class RebuildRekapAlokasi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alokasi:rebuild {unit_id? : The unit ID to rebuild} {period? : The period to rebuild}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild rekap alokasi data based on rekap zis data';

    /**
     * Execute the console command.
     *
     * @param RekapAlokasiService $rekapAlokasiService
     * @return int
     */
    public function handle(RekapAlokasiService $rekapAlokasiService)
    {
        $unitId = $this->argument('unit_id');
        $period = $this->argument('period');

        $this->info('Starting rekap alokasi rebuild process...');

        try {
            if ($unitId && $period) {
                // Rebuild specific unit_id and period
                $this->info("Rebuilding rekap alokasi for unit_id: {$unitId}, period: {$period}");

                $rekapAlokasi = $rekapAlokasiService->updateOrCreateRekapAlokasi($unitId, $period);

                $this->info("Successfully rebuilt rekap alokasi ID: {$rekapAlokasi->id}");

                return 0;
            } else {
                // Rebuild all rekap alokasi records
                $this->info('Rebuilding all rekap alokasi records...');

                $results = $rekapAlokasiService->rebuildAllRekapAlokasi();

                $successCount = count(array_filter($results, function ($result) {
                    return $result['status'] === 'success';
                }));

                $errorCount = count($results) - $successCount;

                $this->info("Rebuild completed. Successful: {$successCount}, Failed: {$errorCount}");

                if ($errorCount > 0) {
                    $this->error('Some records failed to rebuild:');
                    foreach ($results as $result) {
                        if ($result['status'] === 'error') {
                            $this->error("Unit ID: {$result['unit_id']}, Period: {$result['period']} - {$result['message']}");
                        }
                    }
                    return 1;
                }

                return 0;
            }
        } catch (\Exception $e) {
            $this->error('Error rebuilding rekap alokasi: ' . $e->getMessage());
            return 1;
        }
    }
}
