<?php

namespace App\Observers;

use App\Models\RekapZis;
use App\Jobs\UpdateRekapAlokasi;

class RekapZisObserver
{
    /**
     * Handle the RekapZis "created" event.
     *
     * @param  \App\Models\RekapZis  $rekapZis
     * @return void
     */
    public function created(RekapZis $rekapZis)
    {
        $this->dispatchUpdateJob($rekapZis);
    }

    /**
     * Handle the RekapZis "updated" event.
     *
     * @param  \App\Models\RekapZis  $rekapZis
     * @return void
     */
    public function updated(RekapZis $rekapZis)
    {
        $this->dispatchUpdateJob($rekapZis);
    }

    /**
     * Handle the RekapZis "deleted" event.
     *
     * @param  \App\Models\RekapZis  $rekapZis
     * @return void
     */
    public function deleted(RekapZis $rekapZis)
    {
        // When a rekap_zis record is deleted, we might want to remove the corresponding 
        // rekap_alokasi record or update it to reflect zero values
        $this->dispatchUpdateJob($rekapZis);
    }

    /**
     * Handle the RekapZis "restored" event.
     *
     * @param  \App\Models\RekapZis  $rekapZis
     * @return void
     */
    public function restored(RekapZis $rekapZis)
    {
        $this->dispatchUpdateJob($rekapZis);
    }

    /**
     * Handle the RekapZis "force deleted" event.
     *
     * @param  \App\Models\RekapZis  $rekapZis
     * @return void
     */
    public function forceDeleted(RekapZis $rekapZis)
    {
        // Similar to deleted event handling
        $this->dispatchUpdateJob($rekapZis);
    }

    /**
     * Dispatch the update job
     *
     * @param RekapZis $rekapZis
     * @return void
     */
    private function dispatchUpdateJob(RekapZis $rekapZis)
    {
        UpdateRekapAlokasi::dispatch(
            $rekapZis->unit_id,
            $rekapZis->period
        );
    }
}
