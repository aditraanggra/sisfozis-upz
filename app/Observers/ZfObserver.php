<?php

namespace App\Observers;

use App\Models\Zf;
use App\Services\RekapZisService;

class ZfObserver
{

    protected $rekapzisService;

    public function __construct(RekapZisService $rekapzisService)
    {
        $this->rekapzisService = $rekapzisService;
    }
    /**
     * Handle the Zf "created" event.
     */
    public function created(Zf $zf): void
    {
        //
        $this->rekapzisService->updateDailyRekapitulasi($zf->trx_date, $zf->unit_id);
    }

    /**
     * Handle the Zf "updated" event.
     */
    public function updated(Zf $zf): void
    {
        //
        if ($zf->isDirty('trx_date') || $zf->isDirty('unit_id')) {
            $oldDate = $zf->getOriginal('trx_date');
            $this->rekapzisService->updateDailyRekapitulasi($oldDate, $zf->unit_id);
        }
    }

    /**
     * Handle the Zf "deleted" event.
     */
    public function deleted(Zf $zf): void
    {
        //
        $this->rekapzisService->updateDailyRekapitulasi($zf->trx_date, $zf->unit_id);
    }

    /**
     * Handle the Zf "restored" event.
     */
    public function restored(Zf $zf): void
    {
        //
        $this->rekapzisService->updateDailyRekapitulasi($zf->trx_date, $zf->unit_id);
    }

    /**
     * Handle the Zf "force deleted" event.
     */
    public function forceDeleted(Zf $zf): void
    {
        //
        $this->rekapzisService->updateDailyRekapitulasi($zf->trx_date, $zf->unit_id);
    }
}
