<?php

// 1. Implementasi AsyncObserver dengan Queue

// File: app/Jobs/UpdateRekapitulasiJob.php

namespace App\Jobs;

use App\Services\RekapZisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateRekapZis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;

    protected $unitId;

    protected $periodType;

    /**
     * Create a new job instance.
     *
     * @param  string  $date  Date string (Y-m-d format)
     * @param  int  $unitId  Unit ID
     * @param  string  $periodType  Period type: 'harian', 'bulanan', 'tahunan'
     * @return void
     */
    public function __construct($date, $unitId, $periodType = 'harian')
    {
        $this->date = $date;
        $this->unitId = $unitId;
        $this->periodType = $periodType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(RekapZisService $rekapitulasiService)
    {
        switch ($this->periodType) {
            case 'harian':
                $rekapitulasiService->updateDailyRekapitulasi($this->date, $this->unitId);
                break;
            case 'bulanan':
                $date = \Carbon\Carbon::parse($this->date);
                $rekapitulasiService->updateMonthlyRekapitulasi($date->month, $date->year, $this->unitId);
                break;
            case 'tahunan':
                $date = \Carbon\Carbon::parse($this->date);
                $rekapitulasiService->updateYearlyRekapitulasi($date->year, $this->unitId);
                break;
        }
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
     * @param  string  $date  Date in Y-m-d format (any date within the month)
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
     * @param  string  $date  Date in Y-m-d format (any date within the year)
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
