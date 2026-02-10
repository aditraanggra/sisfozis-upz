<?php

namespace App\Observers;

use App\Jobs\UpdateRekapZis;
use App\Models\Ifs;
use App\Services\RekapZisService;

class IfsObserver
{
    protected $rekapzisService;

    public function __construct(RekapZisService $rekapzisService)
    {
        $this->rekapzisService = $rekapzisService;
    }

    /**
     * Handle the Ifs "created" event.
     */
    public function created(Ifs $ifs): void
    {
        //
        $this->dispatchUpdateJob($ifs);
    }

    /**
     * Handle Ifs "updated" event.
     */
    public function updated(Ifs $ifs): void
    {
        //
        if ($ifs->isDirty('trx_date') || $ifs->isDirty('unit_id')) {
            $oldDate = $ifs->getOriginal('trx_date');
            UpdateRekapZis::updateAllPeriods($oldDate, $ifs->unit_id);
        }

        $this->dispatchUpdateJob($ifs);
    }

    /**
     * Handle the Ifs "deleted" event.
     */
    public function deleted(Ifs $ifs): void
    {
        //
        $this->dispatchUpdateJob($ifs);
    }

    /**
     * Handle the Ifs "restored" event.
     */
    public function restored(Ifs $ifs): void
    {
        //
        $this->dispatchUpdateJob($ifs);
    }

    /**
     * Handle the Ifs "force deleted" event.
     */
    public function forceDeleted(Ifs $ifs): void
    {
        //
        $this->dispatchUpdateJob($ifs);
    }

    /**
     * Dispatch job untuk update rekapitulasi
     */
    private function dispatchUpdateJob(Ifs $ifs): void
    {
        // Update all periods when transaction occurs
        UpdateRekapZis::updateAllPeriods($ifs->trx_date, $ifs->unit_id);
    }
}
