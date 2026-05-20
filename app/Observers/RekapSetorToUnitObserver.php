<?php

namespace App\Observers;

use App\Jobs\UpdateRekapUnit;
use App\Models\RekapSetor;
use Illuminate\Support\Facades\Log;

class RekapSetorToUnitObserver
{
    public function created(RekapSetor $RekapSetor)
    {
        $this->dispatchUpdateJob($RekapSetor);
    }

    /**
     * Handle the RekapSetor "updated" event.
     *
     * @return void
     */
    public function updated(RekapSetor $RekapSetor)
    {
        $this->dispatchUpdateJob($RekapSetor);
    }

    /**
     * Handle the RekapSetor "deleted" event.
     *
     * @return void
     */
    public function deleted(RekapSetor $RekapSetor)
    {
        // When a rekap_zis record is deleted, we might want to remove the corresponding
        // rekap_alokasi record or update it to reflect zero values
        $this->dispatchUpdateJob($RekapSetor);
    }

    /**
     * Handle the RekapSetor "restored" event.
     */
    public function restored(RekapSetor $RekapSetor): void
    {
        //
        $this->dispatchUpdateJob($RekapSetor);
    }

    /**
     * Handle the RekapSetor "force deleted" event.
     */
    public function forceDeleted(RekapSetor $RekapSetor): void
    {
        //
        $this->dispatchUpdateJob($RekapSetor);
    }

    /**
     * Dispatch the update job
     *
     * @return void
     */
    private function dispatchUpdateJob(RekapSetor $RekapSetor)
    {
        try {
            // Validasi tambahan
            if (! $RekapSetor->unit_id) {
                Log::warning('Cannot dispatch UpdateRekapUnit: Missing unit_id');

                return;
            }

            if (! $RekapSetor->periode) {
                Log::warning("Cannot dispatch UpdateRekapUnit: Missing periode for unit {$RekapSetor->unit_id}");

                return;
            }

            UpdateRekapUnit::dispatch(
                $RekapSetor->unit_id,
                $RekapSetor->periode
            );
        } catch (\Exception $e) {
            Log::error('Error dispatching UpdateRekapUnit: '.$e->getMessage());
        }
    }
}
