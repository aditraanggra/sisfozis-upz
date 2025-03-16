<?php

namespace App\Observers;

use App\Models\Zm;
use App\Services\RekapZisService;

class ZmObserver
{

    protected $rekapzisService;

    public function __construct(RekapZisService $rekapzisService)
    {
        $this->rekapzisService = $rekapzisService;
    }
    /**
     * Handle the Zm "created" event.
     */
    public function created(Zm $zm): void
    {
        //
        $this->rekapzisService->updateDailyRekapitulasi($zm->trx_date, $zm->unit_id);
    }

    /**
     * Handle the Zm "updated" event.
     */
    public function updated(Zm $zm): void
    {
        //
        if ($zm->isDirty('trx_date') || $zm->isDirty('unit_id')) {
            $oldDate = $zm->getOriginal('trx_date');
            $this->rekapzisService->updateDailyRekapitulasi($oldDate, $zm->unit_id);
        }
    }

    /**
     * Handle the Zm "deleted" event.
     */
    public function deleted(Zm $zm): void
    {
        //
        $this->rekapzisService->updateDailyRekapitulasi($zm->trx_date, $zm->unit_id);
    }

    /**
     * Handle the Zm "restored" event.
     */
    public function restored(Zm $zm): void
    {
        //
        $this->rekapzisService->updateDailyRekapitulasi($zm->trx_date, $zm->unit_id);
    }

    /**
     * Handle the Zm "force deleted" event.
     */
    public function forceDeleted(Zm $zm): void
    {
        //
        $this->rekapzisService->updateDailyRekapitulasi($zm->trx_date, $zm->unit_id);
    }
}
