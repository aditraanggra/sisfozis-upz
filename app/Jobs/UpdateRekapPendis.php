<?php

// File: app/Jobs/UpdateRekapPendis.php

namespace App\Jobs;

use App\Services\RekapPendisService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateRekapPendis implements ShouldQueue
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
    public function handle(RekapPendisService $rekapService)
    {
        try {
            Log::info("Starting UpdateRekapPendis job for date: {$this->date}, unit: {$this->unitId}, period: {$this->period}");

            // Validasi input
            if (empty($this->date) || empty($this->unitId) || empty($this->period)) {
                Log::error("Invalid input for UpdateRekapPendis job. Date: {$this->date}, UnitId: {$this->unitId}, Period: {$this->period}");

                return;
            }

            // Panggil service berdasarkan periode
            switch ($this->period) {
                case 'harian':
                    $rekapService->updateDailyRekapPendis($this->date, $this->unitId);
                    break;
                case 'bulanan':
                    $date = Carbon::parse($this->date);
                    $rekapService->updateMonthlyRekapPendis($date->month, $date->year, $this->unitId);
                    break;
                case 'tahunan':
                    $year = Carbon::parse($this->date)->year;
                    $rekapService->updateYearlyRekapPendis($year, $this->unitId);
                    break;
                default:
                    Log::error("Unsupported period '{$this->period}' for UpdateRekapPendis job");

                    return;
            }

            Log::info("Successfully completed UpdateRekapPendis job for date: {$this->date}, unit: {$this->unitId}, period: {$this->period}");
        } catch (\Exception $e) {
            Log::error('Error in UpdateRekapPendis job: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            // Tambahkan informasi tentang batas percobaan
            if ($this->attempts() >= $this->tries) {
                Log::error("Job UpdateRekapPendis has been attempted {$this->tries} times and will not be retried.");
            } else {
                Log::info("Job UpdateRekapPendis will be retried. Attempt: {$this->attempts()}");
            }

            throw $e; // Lempar exception agar job gagal dan dicoba lagi
        }
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Job UpdateRekapPendis failed finally after {$this->attempts()} attempts for date: {$this->date}, unit: {$this->unitId}, period: {$this->period}");
        Log::error('Final error: '.$exception->getMessage());
    }

    /**
     * Create and dispatch daily update job
     *
     * @param  string  $date  Date in Y-m-d format
     * @param  int  $unitId
     * @return void
     */
    public static function updateDaily($date, $unitId)
    {
        dispatch(new self($date, $unitId, 'harian'));
    }

    /**
     * Create and dispatch monthly update job
     *
     * @param  string  $date  Date in Y-m-d format (any date within month)
     * @param  int  $unitId
     * @return void
     */
    public static function updateMonthly($date, $unitId)
    {
        dispatch(new self($date, $unitId, 'bulanan'));
    }

    /**
     * Create and dispatch yearly update job
     *
     * @param  string  $date  Date in Y-m-d format (any date within year)
     * @param  int  $unitId
     * @return void
     */
    public static function updateYearly($date, $unitId)
    {
        dispatch(new self($date, $unitId, 'tahunan'));
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
        $currentDate = now()->format('Y-m-d');
        self::updateMonthly($currentDate, $unitId);
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
        $currentDate = now()->format('Y-m-d');
        self::updateYearly($currentDate, $unitId);
    }

    /**
     * Create and dispatch jobs for all period types for a given date
     *
     * @param  string  $date  Date in Y-m-d format
     * @param  int  $unitId
     * @return void
     */
    public static function updateAllPeriods($date, $unitId)
    {
        self::updateDaily($date, $unitId);
        self::updateMonthly($date, $unitId);
        self::updateYearly($date, $unitId);
    }
}
