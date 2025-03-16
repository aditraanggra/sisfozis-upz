<?php

namespace App\Observers;

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
        $this->rekapzisService->updateDailyRekapitulasi($ifs->trx_date, $ifs->unit_id);
    }

    /**
     * Handle the Ifs "updated" event.
     */
    public function updated(Ifs $ifs): void
    {
        //
        if ($ifs->isDirty('trx_date') || $ifs->isDirty('unit_id')) {
            $oldDate = $ifs->getOriginal('trx_date');
            $this->rekapzisService->updateDailyRekapitulasi($oldDate, $ifs->unit_id);
        }
    }

    /**
     * Handle the Ifs "deleted" event.
     */
    public function deleted(Ifs $ifs): void
    {
        //
        $this->rekapzisService->updateDailyRekapitulasi($ifs->trx_date, $ifs->unit_id);
    }

    /**
     * Handle the Ifs "restored" event.
     */
    public function restored(Ifs $ifs): void
    {
        //
        $this->rekapzisService->updateDailyRekapitulasi($ifs->trx_date, $ifs->unit_id);
    }

    /**
     * Handle the Ifs "force deleted" event.
     */
    public function forceDeleted(Ifs $ifs): void
    {
        //
        $this->rekapzisService->updateDailyRekapitulasi($ifs->trx_date, $ifs->unit_id);
    }
}
