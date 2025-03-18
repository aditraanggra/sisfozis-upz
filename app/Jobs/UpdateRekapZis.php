<?php

// 1. Implementasi AsyncObserver dengan Queue

// File: app/Jobs/UpdateRekapitulasiJob.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\RekapZisService;
use Carbon\Carbon;

class UpdateRekapitulasiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;
    protected $unitId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date, $unitId)
    {
        $this->date = $date;
        $this->unitId = $unitId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(RekapZisService $rekapitulasiService)
    {
        $rekapitulasiService->updateDailyRekapitulasi($this->date, $this->unitId);
    }
}
