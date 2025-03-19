<?php

namespace App\Observers;

use App\Models\SetorZis;
use App\Services\RekapSetorService;

class SetorObserver
{

    protected $rekapSetorService;

    public function __construct(RekapSetorService $rekapSetorService)
    {
        $this->rekapSetorService = $rekapSetorService;
    }
    /**
     * Handle the SetorZis "created" event.
     */
    public function created(SetorZis $setorZis): void
    {
        //
        $this->rekapSetorService->updateDailyRekapitulasi($setorZis->trx_date, $setorZis->unit_id);
    }

    /** 
     * Handle the SetorZis "updated" event.
     */
    public function updated(SetorZis $setorZis): void
    {
        //
        if ($setorZis->isDirty('trx_date') || $setorZis->isDirty('unit_id')) {
            $oldDate = $setorZis->getOriginal('trx_date');
            $this->rekapSetorService->updateDailyRekapitulasi($oldDate, $setorZis->unit_id);
        }
    }

    /**
     * Handle the SetorZis "deleted" event.
     */
    public function deleted(SetorZis $setorZis): void
    {
        //
        $this->rekapSetorService->updateDailyRekapitulasi($setorZis->trx_date, $setorZis->unit_id);
    }

    /**
     * Handle the SetorZis "restored" event.
     */
    public function restored(SetorZis $setorZis): void
    {
        //
        $this->rekapSetorService->updateDailyRekapitulasi($setorZis->trx_date, $setorZis->unit_id);
    }

    /**
     * Handle the SetorZis "force deleted" event.
     */
    public function forceDeleted(SetorZis $setorZis): void
    {
        //
        $this->rekapSetorService->updateDailyRekapitulasi($setorZis->trx_date, $setorZis->unit_id);
    }
}
