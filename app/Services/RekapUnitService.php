<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\RekapZis;
use App\Models\RekapPendis;
use App\Models\RekapSetor;
use App\Models\RekapUnit;
use App\Models\UnitZis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for rebuilding Unit recapitulation data.
 * 
 * This service aggregates data from other rekap tables (RekapZis, RekapPendis, RekapSetor)
 * to create a unified unit-level summary.
 * 
 * Extends BaseRekapService to leverage chunked processing and bulk upsert
 * for optimized performance when processing large datasets.
 */
class RekapUnitService extends BaseRekapService
{
    protected string $rekapTable = 'rekap_dkm';
    protected string $periodColumn = 'periode';
    protected string $periodDateColumn = 'periode_date';

    /**
     * Rebuild rekap for given parameters using batch processing
     *
     * @param string $unitId Unit ID or 'all' for all units
     * @param string $periode Period type: harian, bulanan, tahunan, or all
     * @param Carbon|null $startDate Start date for rebuild
     * @param Carbon|null $endDate End date for rebuild
     * @return array Results with 'processed' count and 'errors' array
     */
    public function rebuild(
        string $unitId = 'all',
        string $periode = 'all',
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $startDate = $startDate ?? $this->getDefaultStartDate();
        $endDate = $endDate ?? $this->getDefaultEndDate();

        $unitQuery = $unitId === 'all'
            ? UnitZis::query()
            : UnitZis::where('id', $unitId);

        return $this->processInChunks($unitQuery, function ($unit) use ($periode, $startDate, $endDate) {
            $this->rebuildForUnit($unit->id, $periode, $startDate, $endDate);
        });
    }

    /**
     * Rebuild rekap for a specific unit
     *
     * @param int $unitId
     * @param string $periode
     * @param Carbon $startDate
     * @param Carbon $endDate
     */
    protected function rebuildForUnit(
        int $unitId,
        string $periode,
        Carbon $startDate,
        Carbon $endDate
    ): void {
        $records = [];

        if ($periode === 'all' || $periode === 'harian') {
            $records = array_merge($records, $this->buildRecordsForPeriode($unitId, 'harian', $startDate, $endDate));
        }

        if ($periode === 'all' || $periode === 'bulanan') {
            $records = array_merge($records, $this->buildRecordsForPeriode($unitId, 'bulanan', $startDate, $endDate));
        }

        if ($periode === 'all' || $periode === 'tahunan') {
            $records = array_merge($records, $this->buildRecordsForPeriode($unitId, 'tahunan', $startDate, $endDate));
        }

        if (!empty($records)) {
            $this->bulkUpsert($records);
        }
    }

    /**
     * Build records for a specific periode by joining rekap tables
     *
     * @param int $unitId
     * @param string $periode
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    protected function buildRecordsForPeriode(
        int $unitId,
        string $periode,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $aggregatedData = $this->getAggregatedData($unitId, $startDate, $endDate, $periode);

        return collect($aggregatedData)->map(function ($data) use ($unitId, $periode) {
            return $this->buildRekapRecord($unitId, $periode, $data);
        })->toArray();
    }

    /**
     * Build a single rekap record from aggregated data
     *
     * @param int $unitId
     * @param string $periode
     * @param object $data
     * @return array
     */
    protected function buildRekapRecord(int $unitId, string $periode, object $data): array
    {
        $totalPenerimaanZis = ($data->total_zf_amount ?? 0) +
            ($data->total_zm_amount ?? 0) +
            ($data->total_ifs_amount ?? 0);

        $totalPendistribusian = ($data->t_pendis_zf_amount ?? 0) +
            ($data->t_pendis_zm ?? 0) +
            ($data->t_pendis_ifs ?? 0);

        $totalSetor = ($data->t_setor_zf_amount ?? 0) +
            ($data->t_setor_zm ?? 0) +
            ($data->t_setor_ifs ?? 0);

        $totalMuzakki = ($data->total_zf_muzakki ?? 0) +
            ($data->total_zm_muzakki ?? 0) +
            ($data->total_ifs_munfiq ?? 0);

        return [
            'unit_id' => $unitId,
            'periode' => $periode,
            'periode_date' => $data->period_date,
            't_penerimaan_zis' => $totalPenerimaanZis,
            't_penerimaan_zis_beras' => $data->total_zf_rice ?? 0,
            't_pendistribusian' => $totalPendistribusian,
            't_pendistribusian_beras' => $data->t_pendis_zf_rice ?? 0,
            't_setor' => $totalSetor,
            't_setor_beras' => $data->t_setor_zf_rice ?? 0,
            'muzakki' => $totalMuzakki,
            'mustahik' => $data->t_pm ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Get aggregated data by joining rekap tables
     * 
     * Uses database-level joins to aggregate data from RekapZis, RekapPendis, and RekapSetor.
     *
     * @param int $unitId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $periode Period type: harian, bulanan, tahunan
     * @return array
     */
    protected function getAggregatedData(
        int $unitId,
        Carbon $startDate,
        Carbon $endDate,
        string $periode = 'harian'
    ): array {
        // Map periode to the correct column names in each table
        $zisPerioColumn = 'period';
        $zisDateColumn = 'period_date';
        $pendisPerioColumn = 'periode';
        $pendisDateColumn = 'periode_date';
        $setorPerioColumn = 'periode';
        $setorDateColumn = 'periode_date';

        return DB::table('rekap_zis as rz')
            ->leftJoin('rekap_pendis as rp', function ($join) use ($unitId, $periode, $pendisPerioColumn, $pendisDateColumn) {
                $join->on('rz.unit_id', '=', 'rp.unit_id')
                    ->on('rz.period_date', '=', "rp.{$pendisDateColumn}")
                    ->where("rp.{$pendisPerioColumn}", '=', $periode);
            })
            ->leftJoin('rekap_setor as rs', function ($join) use ($unitId, $periode, $setorPerioColumn, $setorDateColumn) {
                $join->on('rz.unit_id', '=', 'rs.unit_id')
                    ->on('rz.period_date', '=', "rs.{$setorDateColumn}")
                    ->where("rs.{$setorPerioColumn}", '=', $periode);
            })
            ->where('rz.unit_id', $unitId)
            ->where("rz.{$zisPerioColumn}", $periode)
            ->whereBetween("rz.{$zisDateColumn}", [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select([
                "rz.{$zisDateColumn} as period_date",
                // ZIS data
                'rz.total_zf_muzakki',
                'rz.total_zf_rice',
                'rz.total_zf_amount',
                'rz.total_zm_muzakki',
                'rz.total_zm_amount',
                'rz.total_ifs_munfiq',
                'rz.total_ifs_amount',
                // Pendis data
                'rp.t_pendis_zf_amount',
                'rp.t_pendis_zf_rice',
                'rp.t_pendis_zm',
                'rp.t_pendis_ifs',
                'rp.t_pm',
                // Setor data
                'rs.t_setor_zf_amount',
                'rs.t_setor_zf_rice',
                'rs.t_setor_zm',
                'rs.t_setor_ifs',
            ])
            ->get()
            ->toArray();
    }

    // =========================================================================
    // Legacy methods for backward compatibility
    // =========================================================================

    /**
     * Rebuild rekap Unit untuk unit dan periode tertentu (legacy method)
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
        // Delegate to the new rebuild method
        $result = $this->rebuild($unit, $periode, $startDate, $endDate);

        // Transform result to legacy format
        return [
            'processed_units' => $result['processed'],
            'processed_periodes' => $result['processed'], // Approximate
            'errors' => $result['errors']
        ];
    }

    /**
     * Update or create rekap Unit based on rekap zis data (legacy method)
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

            // Get rekap_pendis data
            $rekapPendis = RekapPendis::where('unit_id', $unitId)->where('periode', $period)->first();

            if (!$rekapPendis) {
                throw new \Exception("RekapPendis record not found for unit_id: {$unitId} and period: {$period}");
            }

            // Get rekap_setor data
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
