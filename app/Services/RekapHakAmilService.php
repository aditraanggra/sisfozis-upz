<?php

namespace App\Services;

use App\Models\Distribution;
use App\Models\RekapHakAmil;
use App\Models\UnitZis;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for rebuilding Hak Amil (Amil Rights) recapitulation data.
 * 
 * Extends BaseRekapService to leverage chunked processing and bulk upsert
 * for optimized performance when processing large datasets.
 */
class RekapHakAmilService extends BaseRekapService
{
    protected string $rekapTable = 'rekap_hak_amil';
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
            $records = array_merge($records, $this->buildDailyRecords($unitId, $startDate, $endDate));
        }

        if ($periode === 'all' || $periode === 'bulanan') {
            $records = array_merge($records, $this->buildMonthlyRecords($unitId, $startDate, $endDate));
        }

        if ($periode === 'all' || $periode === 'tahunan') {
            $records = array_merge($records, $this->buildYearlyRecords($unitId, $startDate, $endDate));
        }

        if (!empty($records)) {
            $this->bulkUpsert($records);
        }
    }

    /**
     * Build daily records using aggregated GROUP BY query
     *
     * @param int $unitId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    protected function buildDailyRecords(int $unitId, Carbon $startDate, Carbon $endDate): array
    {
        $dailyData = $this->getAggregatedData($unitId, $startDate, $endDate, 'daily');

        return collect($dailyData)->map(function ($data) use ($unitId) {
            return $this->buildRekapRecord($unitId, 'harian', $data);
        })->toArray();
    }

    /**
     * Build monthly records using aggregated GROUP BY query
     *
     * @param int $unitId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    protected function buildMonthlyRecords(int $unitId, Carbon $startDate, Carbon $endDate): array
    {
        $monthlyData = $this->getAggregatedData($unitId, $startDate, $endDate, 'monthly');

        return collect($monthlyData)->map(function ($data) use ($unitId) {
            return $this->buildRekapRecord($unitId, 'bulanan', $data);
        })->toArray();
    }

    /**
     * Build yearly records using aggregated GROUP BY query
     *
     * @param int $unitId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    protected function buildYearlyRecords(int $unitId, Carbon $startDate, Carbon $endDate): array
    {
        $yearlyData = $this->getAggregatedData($unitId, $startDate, $endDate, 'yearly');

        return collect($yearlyData)->map(function ($data) use ($unitId) {
            return $this->buildRekapRecord($unitId, 'tahunan', $data);
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
        return [
            'unit_id' => $unitId,
            'periode' => $periode,
            'periode_date' => $data->period_date,
            't_pendis_ha_zf_amount' => $data->t_pendis_ha_zf_amount ?? 0,
            't_pendis_ha_zf_rice' => $data->t_pendis_ha_zf_rice ?? 0,
            't_pendis_ha_zm' => $data->t_pendis_ha_zm ?? 0,
            't_pendis_ha_ifs' => $data->t_pendis_ha_ifs ?? 0,
            't_pm' => $data->total_pm ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Get aggregated data for a unit using GROUP BY queries
     * 
     * Uses database-level aggregation instead of PHP loops for better performance.
     *
     * @param int $unitId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $groupBy Grouping type: daily, monthly, yearly
     * @return array
     */
    protected function getAggregatedData(
        int $unitId,
        Carbon $startDate,
        Carbon $endDate,
        string $groupBy = 'daily'
    ): array {
        $driver = DB::getDriverName();
        $dateFormat = $this->getDateFormatExpression($groupBy, $driver);
        $periodDateExpr = $this->getPeriodDateExpression($groupBy, $driver);

        return DB::table('distributions')
            ->where('unit_id', $unitId)
            ->whereBetween('trx_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw("{$periodDateExpr} as period_date")
            ->selectRaw("COALESCE(SUM(CASE WHEN fund_type='zakat fitrah' AND asnaf='amil' AND total_amount > 0 THEN total_amount ELSE 0 END), 0) as t_pendis_ha_zf_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN fund_type='zakat fitrah' AND asnaf='amil' AND total_rice > 0 THEN total_rice ELSE 0 END), 0) as t_pendis_ha_zf_rice")
            ->selectRaw("COALESCE(SUM(CASE WHEN fund_type='zakat mal' AND asnaf='amil' THEN total_amount ELSE 0 END), 0) as t_pendis_ha_zm")
            ->selectRaw("COALESCE(SUM(CASE WHEN fund_type='infak' AND program='operasional' THEN total_amount ELSE 0 END), 0) as t_pendis_ha_ifs")
            ->selectRaw("COUNT(DISTINCT beneficiary) as total_pm")
            ->groupByRaw($dateFormat)
            ->get()
            ->toArray();
    }

    /**
     * Get the date format expression for GROUP BY based on database driver
     *
     * @param string $groupBy
     * @param string $driver
     * @return string
     */
    protected function getDateFormatExpression(string $groupBy, string $driver): string
    {
        if ($driver === 'pgsql') {
            return match ($groupBy) {
                'daily' => "DATE(trx_date)",
                'monthly' => "DATE_TRUNC('month', trx_date)",
                'yearly' => "DATE_TRUNC('year', trx_date)",
            };
        }

        // MySQL/MariaDB
        return match ($groupBy) {
            'daily' => "DATE(trx_date)",
            'monthly' => "DATE_FORMAT(trx_date, '%Y-%m-01')",
            'yearly' => "DATE_FORMAT(trx_date, '%Y-01-01')",
        };
    }

    /**
     * Get the period date expression for SELECT based on database driver
     *
     * @param string $groupBy
     * @param string $driver
     * @return string
     */
    protected function getPeriodDateExpression(string $groupBy, string $driver): string
    {
        if ($driver === 'pgsql') {
            return match ($groupBy) {
                'daily' => "TO_CHAR(trx_date, 'YYYY-MM-DD')",
                'monthly' => "TO_CHAR(DATE_TRUNC('month', trx_date), 'YYYY-MM-DD')",
                'yearly' => "TO_CHAR(DATE_TRUNC('year', trx_date), 'YYYY-MM-DD')",
            };
        }

        // MySQL/MariaDB
        return match ($groupBy) {
            'daily' => "DATE_FORMAT(trx_date, '%Y-%m-%d')",
            'monthly' => "DATE_FORMAT(trx_date, '%Y-%m-01')",
            'yearly' => "DATE_FORMAT(trx_date, '%Y-01-01')",
        };
    }

    // =========================================================================
    // Legacy methods for backward compatibility with observers
    // These methods are used by HakAmilObserver for real-time updates
    // =========================================================================

    /**
     * Update rekapitulasi harian untuk tanggal dan unit tertentu
     * 
     * This method is kept for backward compatibility with observers.
     * It does NOT trigger periodic recalculation to avoid redundant processing.
     * 
     * @param string|Carbon $date
     * @param int $unitId
     * @return RekapHakAmil
     */
    public function updateDailyRekapHakAmil($date, $unitId)
    {
        try {
            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();

            // Data HakAmil
            $HakAmilData = $this->getHakAmilData($startDate, $endDate, $unitId);

            // Simpan atau update rekapitulasi
            $rekapitulasi = RekapHakAmil::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => 'harian',
                    'periode_date' => $startDate->format('Y-m-d'),
                ],
                $this->prepareRekapitulasiData($HakAmilData)
            );

            // NOTE: Removed updatePeriodicRekapitulasi() call to avoid redundant
            // recalculation during daily processing (Requirements 6.2)
            // Monthly and yearly recaps should be rebuilt separately via commands

            return $rekapitulasi;
        } catch (\Exception $e) {
            Log::error('Error updating rekapitulasi: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update rekapitulasi bulanan
     * 
     * @param int $month
     * @param int $year
     * @param int $unitId
     * @return RekapHakAmil
     */
    public function updateMonthlyRekapHakAmil($month, $year, $unitId)
    {
        try {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();

            // Data HakAmil
            $HakAmilData = $this->getHakAmilData($startDate, $endDate, $unitId);

            // Simpan atau update rekapitulasi
            $rekapitulasi = RekapHakAmil::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => 'bulanan',
                    'periode_date' => $startDate->format('Y-m-01'),
                ],
                $this->prepareRekapitulasiData($HakAmilData)
            );

            return $rekapitulasi;
        } catch (\Exception $e) {
            Log::error('Error updating monthly rekapitulasi: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update rekapitulasi tahunan
     * 
     * @param int $year
     * @param int $unitId
     * @return RekapHakAmil
     */
    public function updateYearlyRekapHakAmil($year, $unitId)
    {
        try {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();

            // Data HakAmil
            $HakAmilData = $this->getHakAmilData($startDate, $endDate, $unitId);

            // Simpan atau update rekapitulasi
            $rekapitulasi = RekapHakAmil::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => 'tahunan',
                    'periode_date' => $startDate->format('Y-01-01'),
                ],
                $this->prepareRekapitulasiData($HakAmilData)
            );

            return $rekapitulasi;
        } catch (\Exception $e) {
            Log::error('Error updating yearly rekapitulasi: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ambil data HakAmil untuk rentang tanggal tertentu (legacy method)
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $unitId
     * @return object
     */
    protected function getHakAmilData(Carbon $startDate, Carbon $endDate, $unitId)
    {
        return Distribution::where('unit_id', $unitId)
            ->whereBetween('trx_date', [$startDate, $endDate])
            ->selectRaw(" 
                SUM(CASE WHEN fund_type='zakat fitrah' AND asnaf='amil' AND total_amount > 0 THEN total_amount ELSE 0 END) as t_pendis_ha_zf_amount,
                SUM(CASE WHEN fund_type='zakat fitrah' AND asnaf='amil' AND total_rice > 0 THEN total_rice ELSE 0 END) as t_pendis_ha_zf_rice,
                SUM(CASE WHEN fund_type='zakat mal' AND asnaf='amil' THEN total_amount ELSE 0 END) as t_pendis_ha_zm,
                SUM(CASE WHEN fund_type='infak' AND program='operasional' THEN total_amount ELSE 0 END) as t_pendis_ha_ifs,
                COUNT(DISTINCT beneficiary) as total_pm
            ")
            ->first();
    }

    /**
     * Menyiapkan data untuk update rekapitulasi (legacy method)
     * 
     * @param object $HakAmilData
     * @return array
     */
    protected function prepareRekapitulasiData($HakAmilData)
    {
        return [
            't_pendis_ha_zf_amount' => $HakAmilData->t_pendis_ha_zf_amount ?? 0,
            't_pendis_ha_zf_rice' => $HakAmilData->t_pendis_ha_zf_rice ?? 0,
            't_pendis_ha_zm' => $HakAmilData->t_pendis_ha_zm ?? 0,
            't_pendis_ha_ifs' => $HakAmilData->t_pendis_ha_ifs ?? 0,
            't_pm' => $HakAmilData->total_pm ?? 0,
        ];
    }
}
