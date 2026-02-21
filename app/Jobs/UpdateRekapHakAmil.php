<?php

namespace App\Jobs;

use App\Services\RekapHakAmilService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateRekapHakAmil implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;

    protected $unitId;

    protected $period;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @param  string  $date
     * @param  int  $unitId
     * @param  string|null  $period
     * @return void
     */
    public function __construct($date, $unitId, $period = null)
    {
        $this->date = $date;
        $this->unitId = $unitId;
        $this->period = $period ?: 'harian';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(RekapHakAmilService $rekapService)
    {
        try {
            Log::info("Starting UpdateRekapHakAmil job for date: {$this->date}, unit: {$this->unitId}, period: {$this->period}");

            if (empty($this->date) || empty($this->unitId) || empty($this->period)) {
                Log::error("Invalid input for UpdateRekapHakAmil job. Date: {$this->date}, UnitId: {$this->unitId}, Period: {$this->period}");

                return;
            }

            switch ($this->period) {
                case 'harian':
                    $rekapService->updateDailyRekapHakAmil($this->date, $this->unitId);
                    break;
                case 'bulanan':
                    $date = Carbon::parse($this->date);
                    $rekapService->updateMonthlyRekapHakAmil($date->month, $date->year, $this->unitId);
                    break;
                case 'tahunan':
                    $year = Carbon::parse($this->date)->year;
                    $rekapService->updateYearlyRekapHakAmil($year, $this->unitId);
                    break;
                default:
                    Log::error("Unsupported period '{$this->period}' for UpdateRekapHakAmil job");

                    return;
            }

            Log::info("Successfully completed UpdateRekapHakAmil job for date: {$this->date}, unit: {$this->unitId}, period: {$this->period}");
        } catch (\Throwable $e) {
             Log::error('Error in UpdateRekapHakAmil job: '.$e->getMessage());
            Log::error('Error in UpdateRekapHakAmil job: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            if ($this->attempts() >= $this->tries) {
                Log::error("Job UpdateRekapHakAmil has been attempted {$this->tries} times and will not be retried.");
            } else {
                Log::info("Job UpdateRekapHakAmil will be retried. Attempt: {$this->attempts()}");
            }

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
        Log::error("Job UpdateRekapHakAmil failed finally after {$this->attempts()} attempts for date: {$this->date}, unit: {$this->unitId}, period: {$this->period}");
        Log::error('Final error: '.$exception->getMessage());
    }

    /**
     * Create and dispatch daily update job
     */
    public static function updateDaily($date, $unitId)
    {
        dispatch(new self($date, $unitId, 'harian'));
    }

    /**
     * Create and dispatch monthly update job
     */
    public static function updateMonthly($date, $unitId)
    {
        dispatch(new self($date, $unitId, 'bulanan'));
    }

    /**
     * Create and dispatch yearly update job
     */
    public static function updateYearly($date, $unitId)
    {
        dispatch(new self($date, $unitId, 'tahunan'));
    }

    /**
     * Create and dispatch jobs for all period types for a given date
     */
    public static function updateAllPeriods($date, $unitId)
    {
        self::updateDaily($date, $unitId);
        self::updateMonthly($date, $unitId);
        self::updateYearly($date, $unitId);
    }
}
