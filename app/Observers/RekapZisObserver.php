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
        $periodDate = $rekapZis->period_date instanceof \Carbon\Carbon
            ? $rekapZis->period_date->format('Y-m-d')
            : $rekapZis->period_date;

        // Dispatch job for the specific period that was changed
        UpdateRekapAlokasi::dispatch(
            $rekapZis->unit_id,
            $rekapZis->period,
            $periodDate
        );

        // Also dispatch for tahunan when harian/bulanan changes,
        // since any period change affects yearly totals.
        // This ensures rekap_alokasi tahunan stays up-to-date for the API.
        if ($rekapZis->period !== 'tahunan') {
            $year = substr($periodDate, 0, 4);
            UpdateRekapAlokasi::dispatch(
                $rekapZis->unit_id,
                'tahunan',
                "{$year}-01-01"
            );
        }
    }
}
