<?php

namespace App\Observers;

use App\Models\RekapZis;
use App\Jobs\UpdateRekapUnit;
use Illuminate\Support\Facades\Log;

class RekapZisToUnitObserver
{
    /**
     * Handle created events
     */
    public function created(RekapZis $rekapZis)
    {
        $this->dispatchUpdateJob($rekapZis);
    }

    /**
     * Handle updated events
     */
    public function updated(RekapZis $rekapZis)
    {
        // Hanya dispatch job jika ada perubahan signifikan
        if ($this->shouldUpdateRekap($rekapZis)) {
            $this->dispatchUpdateJob($rekapZis);
        }
    }

    /**
     * Handle deleted events
     */
    public function deleted(RekapZis $rekapZis)
    {
        $this->dispatchUpdateJob($rekapZis);
    }

    /**
     * Handle restored events
     */
    public function restored(RekapZis $rekapZis): void
    {
        $this->dispatchUpdateJob($rekapZis);
    }

    /**
     * Handle force deleted events
     */
    public function forceDeleted(RekapZis $rekapZis): void
    {
        $this->dispatchUpdateJob($rekapZis);
    }

    /**
     * Dispatch the update job
     */
    private function dispatchUpdateJob(RekapZis $rekapZis)
    {
        // Validasi input sebelum dispatch
        if (!$rekapZis->unit_id || !$rekapZis->period) {
            Log::warning('Cannot dispatch UpdateRekapUnit job', [
                'unit_id' => $rekapZis->unit_id,
                'period' => $rekapZis->period
            ]);
            return;
        }

        try {
            UpdateRekapUnit::dispatch(
                $rekapZis->unit_id,
                $rekapZis->period
            )->onQueue('rekap-update'); // Tambahkan queue spesifik
        } catch (\Exception $e) {
            Log::error('Failed to dispatch UpdateRekapUnit job', [
                'unit_id' => $rekapZis->unit_id,
                'period' => $rekapZis->period,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Tentukan apakah rekap perlu diupdate
     * 
     * @param RekapZis $rekapZis
     * @return bool
     */
    private function shouldUpdateRekap(RekapZis $rekapZis): bool
    {
        // Cek apakah ada perubahan signifikan di kolom-kolom penting
        $importantColumns = [
            'total_zf_amount',
            'total_zm_amount',
            'total_ifs_amount',
            'total_zf_rice',
            'total_zf_muzakki',
            'total_zm_muzakki',
            'total_ifs_munfiq'
        ];

        // Gunakan method isDirty untuk memeriksa perubahan
        return $rekapZis->isDirty($importantColumns);
    }
}
