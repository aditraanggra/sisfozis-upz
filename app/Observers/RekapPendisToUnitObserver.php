<?php

namespace App\Observers;

use App\Models\RekapPendis;
use App\Jobs\UpdateRekapUnit;
use Illuminate\Support\Facades\Log;

class RekapPendisToUnitObserver
{
    public function created(RekapPendis $RekapPendis)
    {
        $this->dispatchUpdateJob($RekapPendis);
    }

    /**
     * Handle the RekapPendis "updated" event.
     *
     * @param  \App\Models\RekapPendis  $RekapPendis
     * @return void
     */
    public function updated(RekapPendis $RekapPendis)
    {
        $this->dispatchUpdateJob($RekapPendis);
    }

    /**
     * Handle the RekapPendis "deleted" event.
     *
     * @param  \App\Models\RekapPendis  $RekapPendis
     * @return void
     */
    public function deleted(RekapPendis $RekapPendis)
    {
        // When a rekap_zis record is deleted, we might want to remove the corresponding 
        // rekap_alokasi record or update it to reflect zero values
        $this->dispatchUpdateJob($RekapPendis);
    }

    /**
     * Handle the RekapPendis "restored" event.
     */
    public function restored(RekapPendis $RekapPendis): void
    {
        //
        $this->dispatchUpdateJob($RekapPendis);
    }

    /**
     * Handle the RekapPendis "force deleted" event.
     */
    public function forceDeleted(RekapPendis $RekapPendis): void
    {
        //
        $this->dispatchUpdateJob($RekapPendis);
    }

    /**
     * Dispatch the update job
     *
     * @param RekapPendis $RekapPendis
     * @return void
     */
    private function dispatchUpdateJob(RekapPendis $RekapPendis)
    {
        try {
            // Validasi tambahan
            if (!$RekapPendis->unit_id) {
                Log::warning("Cannot dispatch UpdateRekapUnit: Missing unit_id");
                return;
            }

            if (!$RekapPendis->periode) {
                Log::warning("Cannot dispatch UpdateRekapUnit: Missing periode for unit {$RekapPendis->unit_id}");
                return;
            }

            UpdateRekapUnit::dispatch(
                $RekapPendis->unit_id,
                $RekapPendis->periode
            );
        } catch (\Exception $e) {
            Log::error("Error dispatching UpdateRekapUnit: " . $e->getMessage());
        }
    }
}
