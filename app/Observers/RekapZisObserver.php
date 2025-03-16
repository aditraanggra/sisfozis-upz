<?php

namespace App\Observers;

use App\Models\RekapZis;
use App\Services\RekapAlokasiService;

class RekapZisObserver
{
    protected $rekapAlokasiService;

    /**
     * Create a new observer instance.
     *
     * @param RekapAlokasiService $rekapAlokasiService
     * @return void
     */
    public function __construct(RekapAlokasiService $rekapAlokasiService)
    {
        $this->rekapAlokasiService = $rekapAlokasiService;
    }

    /**
     * Handle the RekapZis "created" event.
     *
     * @param  \App\Models\RekapZis  $rekapZis
     * @return void
     */
    public function created(RekapZis $rekapZis)
    {
        $this->rekapAlokasiService->processAllocation($rekapZis);
    }

    /**
     * Handle the RekapZis "updated" event.
     *
     * @param  \App\Models\RekapZis  $rekapZis
     * @return void
     */
    public function updated(RekapZis $rekapZis)
    {
        $this->rekapAlokasiService->processAllocation($rekapZis);
    }

    /**
     * Handle the RekapZis "deleted" event.
     *
     * @param  \App\Models\RekapZis  $rekapZis
     * @return void
     */
    public function deleted(RekapZis $rekapZis)
    {
        // Optionally handle deletion - you might want to delete the corresponding allocation record
        // or recalculate without this record's contribution
    }
}
