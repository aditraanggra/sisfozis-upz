<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\RekapZis;
use App\Models\RekapPendis;
use App\Models\RekapSetor;
use App\Models\RekapUnit;
use App\Models\Unit;
use App\Models\UnitZis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RekapUnitService
{
    /**
     * Rebuild rekap Unit untuk unit dan periode tertentu
     *
     * @param string $unit unit ID atau 'all'
     * @param string $periode 'harian', 'mingguan', 'bulanan', 'tahunan', atau 'all'
     * @param Carbon|null $startDate Tanggal mulai
     * @param Carbon|null $endDate Tanggal akhir
     * @return array Informasi proses rebuild
     */
    public function rebuildRekapUnit(
        string $unit = 'all',
        string $periode = 'all',
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $processedUnits = 0;
        $processedPeriodes = 0;
        $errors = [];

        // Tentukan unit yang akan diproses
        $unitQuery = $unit === 'all'
            ? UnitZis::query()
            : UnitZis::where('id', $unit);

        // Proses setiap unit
        $unitQuery->chunk(50, function ($units) use (
            $periode,
            $startDate,
            $endDate,
            &$processedUnits,
            &$processedPeriodes,
            &$errors
        ) {
            foreach ($units as $unitModel) {
                try {
                    // Tentukan periode yang akan diproses
                    $periodeQuery = $this->determinePeriodeQuery($periode, $unitModel->id, $startDate, $endDate);

                    foreach ($periodeQuery as $currentPeriode) {
                        try {
                            $this->updateOrCreateRekapUnit($unitModel->id, $currentPeriode);
                            $processedPeriodes++;
                        } catch (\Exception $e) {
                            $errors[] = [
                                'unit_id' => $unitModel->id,
                                'periode' => $currentPeriode,
                                'error' => $e->getMessage()
                            ];
                        }
                    }
                    $processedUnits++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'unit_id' => $unitModel->id,
                        'error' => $e->getMessage()
                    ];
                }
            }
        });

        return [
            'processed_units' => $processedUnits,
            'processed_periodes' => $processedPeriodes,
            'errors' => $errors
        ];
    }

    /**
     * Tentukan periode yang akan diproses
     *
     * @param string $periode
     * @param int $unitId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    private function determinePeriodeQuery(
        string $periode,
        int $unitId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $query = RekapZis::where('unit_id', $unitId);

        // Filter berdasarkan periode
        if ($periode !== 'all') {
            $query->where('period', $periode);
        }

        // Filter berdasarkan rentang tanggal
        if ($startDate) {
            $query->where('period_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('period_date', '<=', $endDate);
        }

        return $query->pluck('period')->unique()->toArray();
    }

    /**
     * Update or create rekap Unit based on rekap zis data
     *
     * @param int $unitId
     * @param string $period
     * @return RekapUnit
     */
    public function updateOrCreateRekapUnit(int $unitId, string $period): RekapUnit
    {
        try {
            DB::beginTransaction();

            // Get rekap_zis data
            $rekapZis = RekapZis::where('unit_id', $unitId)
                ->where('period', $period)
                ->first();

            if (!$rekapZis) {
                throw new \Exception("RekapZis record not found for unit_id: {$unitId} and period: {$period}");
            }

            //Get rekap_pendis data
            $rekapPendis = RekapPendis::where('unit_id', $unitId)->where('periode', $period)->first();

            if (!$rekapPendis) {
                throw new \Exception("RekapPendis record not found for unit_id: {$unitId} and period: {$period}");
            }

            //Get rekap_pendis data
            $rekapSetor = RekapSetor::where('unit_id', $unitId)->where('periode', $period)->first();

            if (!$rekapSetor) {
                throw new \Exception("RekapSetor record not found for unit_id: {$unitId} and period: {$period}");
            }

            // Calculate allocation values based on the formulas
            $totalPenerimaanZis =
                ($rekapZis->total_zf_amount ?? 0) +
                ($rekapZis->total_zm_amount ?? 0) +
                ($rekapZis->total_ifs_amount ?? 0);
            $totalPenerimaanZisBeras = $rekapZis->total_zf_rice ?? 0;
            $totalPendistribusian =
                ($rekapPendis->t_pendis_zf_amount ?? 0) +
                ($rekapPendis->t_pendis_zm ?? 0) +
                ($rekapPendis->t_pendis_ifs ?? 0);
            $totalPendistribusianBeras = $rekapPendis->t_pendis_zf_rice ?? 0;
            $totalSetor =
                ($rekapSetor->t_setor_zf_amount ?? 0) +
                ($rekapSetor->t_setor_zm ?? 0) +
                ($rekapSetor->t_setor_ifs ?? 0);
            $totalSetorBeras = $rekapSetor->t_setor_zf_rice ?? 0;
            $totalMuzakki =
                ($rekapZis->total_zf_muzakki ?? 0) +
                ($rekapZis->total_zm_muzakki ?? 0) +
                ($rekapZis->total_ifs_munfiq ?? 0);
            $totalMustahik = $rekapPendis->t_pm ?? 0;

            // Update or create rekap_Unit record
            $rekapUnit = RekapUnit::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => $period,
                ],
                [
                    'periode_date' => $rekapZis->period_date,
                    't_penerimaan_zis' => $totalPenerimaanZis,
                    't_penerimaan_zis_beras' => $totalPenerimaanZisBeras,
                    't_pendistribusian' => $totalPendistribusian,
                    't_pendistribusian_beras' => $totalPendistribusianBeras,
                    't_setor' => $totalSetor,
                    't_setor_beras' => $totalSetorBeras,
                    'muzakki' => $totalMuzakki,
                    'mustahik' => $totalMustahik
                ]
            );

            DB::commit();
            return $rekapUnit;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating rekap Unit: ' . $e->getMessage());
            throw $e;
        }
    }
}
