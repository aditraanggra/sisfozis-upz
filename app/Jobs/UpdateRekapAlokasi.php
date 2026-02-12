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
            Log::error('Failed to update rekap alokasi: '.$e->getMessage());

            // Rethrow the exception to retry the job
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error("UpdateRekapAlokasi job failed after {$this->tries} attempts. Unit ID: {$this->unitId}, Period: {$this->period}. Error: {$exception->getMessage()}");
    }

    /**
     * Create and dispatch daily update job
     *
     * @param  int  $unitId
     * @param  string  $date  Date in Y-m-d format
     * @return void
     */
    public static function updateDaily($unitId, $date = null)
    {
        $date = $date ?? now()->format('Y-m-d');
        dispatch(new self($unitId, 'harian'));
    }

    /**
     * Create and dispatch monthly update job
     *
     * @param  int  $unitId
     * @param  string  $date  Date in Y-m-d format (any date within month)
     * @return void
     */
    public static function updateMonthly($unitId, $date = null)
    {
        $date = $date ?? now()->format('Y-m-d');
        dispatch(new self($unitId, 'bulanan'));
    }

    /**
     * Create and dispatch yearly update job
     *
     * @param  int  $unitId
     * @param  string  $date  Date in Y-m-d format (any date within year)
     * @return void
     */
    public static function updateYearly($unitId, $date = null)
    {
        $date = $date ?? now()->format('Y-m-d');
        dispatch(new self($unitId, 'tahunan'));
    }

    /**
     * Create and dispatch job for automatic monthly update
     * This method is typically called at the end of each month
     *
     * @param  int  $unitId
     * @return void
     */
    public static function updateCurrentMonth($unitId)
    {
        self::updateMonthly($unitId);
    }

    /**
     * Create and dispatch job for automatic yearly update
     * This method is typically called at the end of each year
     *
     * @param  int  $unitId
     * @return void
     */
    public static function updateCurrentYear($unitId)
    {
        self::updateYearly($unitId);
    }

    /**
     * Create and dispatch jobs for all period types for a unit
     *
     * @param  int  $unitId
     * @param  string  $date  Date in Y-m-d format
     * @return void
     */
    public static function updateAllPeriods($unitId, $date = null)
    {
        $date = $date ?? now()->format('Y-m-d');
        self::updateDaily($unitId, $date);
        self::updateMonthly($unitId, $date);
        self::updateYearly($unitId, $date);
    }
}
