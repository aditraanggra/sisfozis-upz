<?php

namespace App\Jobs;

use App\Services\RekapAlokasiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateRekapAlokasi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The unit_id to update.
     *
     * @var int
     */
    protected $unitId;

    /**
     * The period to update.
     *
     * @var string
     */
    protected $period;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param int $unitId
     * @param string $period
     * @return void
     */
    public function __construct(int $unitId, string $period)
    {
        $this->unitId = $unitId;
        $this->period = $period;
    }

    /**
     * Execute the job.
     *
     * @param RekapAlokasiService $rekapAlokasiService
     * @return void
     */
    public function handle(RekapAlokasiService $rekapAlokasiService)
    {
        try {
            Log::info("Updating rekap alokasi for unit_id: {$this->unitId}, period: {$this->period}");

            $rekapAlokasi = $rekapAlokasiService->updateOrCreateRekapAlokasi(
                $this->unitId,
                $this->period
            );

            Log::info("Successfully updated rekap alokasi ID: {$rekapAlokasi->id}");
        } catch (\Exception $e) {
            Log::error("Failed to update rekap alokasi: " . $e->getMessage());

            // Rethrow the exception to retry the job
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error("UpdateRekapAlokasi job failed after {$this->tries} attempts. Unit ID: {$this->unitId}, Period: {$this->period}. Error: {$exception->getMessage()}");
    }
}
