<?php

namespace App\Observers;

use App\Models\Distribution;
use App\Jobs\UpdateRekapPendis;

class PendisObserver
{
    /**
     * Handle the Distribution "created" event.
     */
    public function created(Distribution $distribution): void
    {
        $this->dispatchUpdateJob($distribution);
    }

    /**
     * Handle the Distribution "updated" event.
     */
    public function updated(Distribution $distribution): void
    {
        if ($distribution->isDirty('trx_date') || $distribution->isDirty('unit_id')) {
            $oldDate = $distribution->getOriginal('trx_date');
            $oldUnitId = $distribution->getOriginal('unit_id');
            UpdateRekapPendis::updateAllPeriods($oldDate, $oldUnitId);
        }

        $this->dispatchUpdateJob($distribution);
    }

    /**
     * Handle the Distribution "deleted" event.
     */
    public function deleted(Distribution $distribution): void
    {
        $this->dispatchUpdateJob($distribution);
    }

    /**
     * Handle the Distribution "restored" event.
     */
    public function restored(Distribution $distribution): void
    {
        $this->dispatchUpdateJob($distribution);
    }

    /**
     * Handle the Distribution "force deleted" event.
     */
    public function forceDeleted(Distribution $distribution): void
    {
        $this->dispatchUpdateJob($distribution);
    }

    /**
     * Dispatch jobs to update rekapitulasi for all periods
     */
    private function dispatchUpdateJob(Distribution $distribution): void
    {
        UpdateRekapPendis::updateAllPeriods($distribution->trx_date, $distribution->unit_id);
    }
}
