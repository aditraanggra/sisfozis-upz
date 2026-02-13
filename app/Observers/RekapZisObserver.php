<?php

namespace App\Observers;

use App\Jobs\UpdateRekapAlokasi;
use App\Models\RekapZis;

class RekapZisObserver
{
    /**
     * Handle the RekapZis "created" event.
     *
     * @return void
     */
    public function created(RekapZis $rekapZis)
    {
        $this->dispatchUpdateJobs($rekapZis);
    }

    /**
     * Handle the RekapZis "updated" event.
     *
     * @return void
     */
    public function updated(RekapZis $rekapZis)
    {
        $this->dispatchUpdateJobs($rekapZis);
    }

    /**
     * Handle the RekapZis "deleted" event.
     *
     * @return void
     */
    public function deleted(RekapZis $rekapZis)
    {
        // When a rekap_zis record is deleted, we might want to remove the corresponding
        // rekap_alokasi record or update it to reflect zero values
        $this->dispatchUpdateJobs($rekapZis);
    }

    /**
     * Handle the RekapZis "restored" event.
     *
     * @return void
     */
    public function restored(RekapZis $rekapZis)
    {
        $this->dispatchUpdateJobs($rekapZis);
    }

    /**
     * Handle the RekapZis "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(RekapZis $rekapZis)
    {
        // Similar to deleted event handling
        $this->dispatchUpdateJobs($rekapZis);
    }

    /**
     * Dispatch update jobs for all relevant periods
     *
     * @return void
     */
    private function dispatchUpdateJobs(RekapZis $rekapZis)
    {
        // Dispatch jobs for all periods that may be affected:
        // - The specific period (harian, bulanan, tahunan)
        // - All related periods if needed
        $periods = ['harian', 'bulanan', 'tahunan'];

        foreach ($periods as $period) {
            UpdateRekapAlokasi::dispatch(
                $rekapZis->unit_id,
                $period
            );
        }
    }
}
