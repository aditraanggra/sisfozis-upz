<?php

namespace App\Observers;

use App\Models\SetorZis;
use App\Jobs\UpdateRekapSetor;

class SetorObserver
{
    /**
     * Handle the SetorZis "created" event.
     */
    public function created(SetorZis $setorZis): void
    {
        $this->dispatchUpdateJob($setorZis);
    }

    /**
     * Handle the SetorZis "updated" event.
     */
    public function updated(SetorZis $setorZis): void
    {
        if ($setorZis->isDirty('trx_date') || $setorZis->isDirty('unit_id')) {
            $oldDate = $setorZis->getOriginal('trx_date');
            $oldUnitId = $setorZis->getOriginal('unit_id');
            UpdateRekapSetor::updateAllPeriods($oldDate, $oldUnitId);
        }

        $this->dispatchUpdateJob($setorZis);
    }

    /**
     * Handle the SetorZis "deleted" event.
     */
    public function deleted(SetorZis $setorZis): void
    {
        $this->dispatchUpdateJob($setorZis);
    }

    /**
     * Handle the SetorZis "restored" event.
     */
    public function restored(SetorZis $setorZis): void
    {
        $this->dispatchUpdateJob($setorZis);
    }

    /**
     * Handle the SetorZis "force deleted" event.
     */
    public function forceDeleted(SetorZis $setorZis): void
    {
        $this->dispatchUpdateJob($setorZis);
    }

    /**
     * Dispatch jobs to update rekapitulasi for all periods
     */
    private function dispatchUpdateJob(SetorZis $setorZis): void
    {
        UpdateRekapSetor::updateAllPeriods($setorZis->trx_date, $setorZis->unit_id);
    }
}
