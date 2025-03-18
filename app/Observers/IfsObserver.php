<?php

namespace App\Observers;

use App\Models\Ifs;
use App\Services\RekapZisService;
use App\Jobs\UpdateRekapitulasiJob;

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
     * Handle the Ifs "updated" event.
     */
    public function updated(Ifs $ifs): void
    {
        //
        if ($ifs->isDirty('trx_date') || $ifs->isDirty('unit_id')) {
            $oldDate = $ifs->getOriginal('trx_date');
            UpdateRekapitulasiJob::dispatch($oldDate, $ifs->unit_id);
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
        UpdateRekapitulasiJob::dispatch($ifs->trx_date, $ifs->unit_id);
    }
}
