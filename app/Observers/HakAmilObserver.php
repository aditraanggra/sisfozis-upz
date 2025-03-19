<?php

namespace App\Observers;

use App\Models\Distribution;
use App\Services\RekapHakAmilService;

class HakAmilObserver
{

    protected $rekapHakAmilService;

    public function __construct(RekapHakAmilService $rekapHakAmilService)
    {
        $this->rekapHakAmilService = $rekapHakAmilService;
    }
    /**
     * Handle the Distribution "created" event.
     */
    public function created(Distribution $distribution): void
    {
        //
        $this->rekapHakAmilService->updateDailyRekapHakAmil($distribution->trx_date, $distribution->unit_id);
    }

    /**
     * Handle the Distribution "updated" event.
     */
    public function updated(Distribution $distribution): void
    {
        //
        $this->rekapHakAmilService->updateDailyRekapHakAmil($distribution->trx_date, $distribution->unit_id);
    }

    /**
     * Handle the Distribution "deleted" event.
     */
    public function deleted(Distribution $distribution): void
    {
        //
        $this->rekapHakAmilService->updateDailyRekapHakAmil($distribution->trx_date, $distribution->unit_id);
    }

    /**
     * Handle the Distribution "restored" event.
     */
    public function restored(Distribution $distribution): void
    {
        //
        $this->rekapHakAmilService->updateDailyRekapHakAmil($distribution->trx_date, $distribution->unit_id);
    }

    /**
     * Handle the Distribution "force deleted" event.
     */
    public function forceDeleted(Distribution $distribution): void
    {
        //
        $this->rekapHakAmilService->updateDailyRekapHakAmil($distribution->trx_date, $distribution->unit_id);
    }

    /* private function dispatchUpdateJob(Distribution $distribution): void
    {
        UpdateRekapHakAmil::dispatch($distribution->trx_date, $distribution->unit_id);
    } */
}
