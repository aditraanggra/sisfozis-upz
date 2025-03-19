<?php

namespace App\Observers;

use App\Models\Distribution;
use App\Services\RekapPendisService;
use App\Jobs\UpdateRekapPendis;

class PendisObserver
{

    protected $rekapPendisService;

    public function __construct(RekapPendisService $rekapPendisService)
    {
        $this->rekapPendisService = $rekapPendisService;
    }
    /**
     * Handle the Distribution "created" event.
     */
    public function created(Distribution $distribution): void
    {
        //
        $this->rekapPendisService->updateDailyRekapPendis($distribution->trx_date, $distribution->unit_id);
    }

    /**
     * Handle the Distribution "updated" event.
     */
    public function updated(Distribution $distribution): void
    {
        //
        $this->rekapPendisService->updateDailyRekapPendis($distribution->trx_date, $distribution->unit_id);
    }

    /**
     * Handle the Distribution "deleted" event.
     */
    public function deleted(Distribution $distribution): void
    {
        //
        $this->rekapPendisService->updateDailyRekapPendis($distribution->trx_date, $distribution->unit_id);
    }

    /**
     * Handle the Distribution "restored" event.
     */
    public function restored(Distribution $distribution): void
    {
        //
        $this->rekapPendisService->updateDailyRekapPendis($distribution->trx_date, $distribution->unit_id);
    }

    /**
     * Handle the Distribution "force deleted" event.
     */
    public function forceDeleted(Distribution $distribution): void
    {
        //
        $this->rekapPendisService->updateDailyRekapPendis($distribution->trx_date, $distribution->unit_id);
    }

    /* private function dispatchUpdateJob(Distribution $distribution): void
    {
        UpdateRekapPendis::dispatch($distribution->trx_date, $distribution->unit_id);
    } */
}
