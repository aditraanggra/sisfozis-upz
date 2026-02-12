<?php

namespace App\Services;

use App\Models\Zf;
use App\Models\Zm;
use App\Models\Ifs;
use App\Models\RekapZis;
use App\Models\UnitZis;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for rebuilding ZIS (Zakat, Infak, Sedekah) recapitulation data.
 * 
 * Extends BaseRekapService to leverage chunked processing and bulk upsert
 * for optimized performance when processing large datasets.
 */
class RekapZisService extends BaseRekapService
{
    protected string $rekapTable = 'rekap_zis';
    protected string $periodColumn = 'period';
    protected string $periodDateColumn = 'period_date';

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
            return [
                'unit_id' => $unitId,
                'period' => 'harian',
                'period_date' => $data->period_date,
                'total_zf_muzakki' => $data->total_zf_muzakki ?? 0,
                'total_zf_rice' => $data->total_zf_rice ?? 0,
                'total_zf_amount' => $data->total_zf_amount ?? 0,
                'total_zm_muzakki' => $data->total_zm_muzakki ?? 0,
                'total_zm_amount' => $data->total_zm_amount ?? 0,
                'total_ifs_munfiq' => $data->total_ifs_munfiq ?? 0,
                'total_ifs_amount' => $data->total_ifs_amount ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
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
            return [
                'unit_id' => $unitId,
                'period' => 'bulanan',
                'period_date' => $data->period_date,
                'total_zf_muzakki' => $data->total_zf_muzakki ?? 0,
                'total_zf_rice' => $data->total_zf_rice ?? 0,
                'total_zf_amount' => $data->total_zf_amount ?? 0,
                'total_zm_muzakki' => $data->total_zm_muzakki ?? 0,
                'total_zm_amount' => $data->total_zm_amount ?? 0,
                'total_ifs_munfiq' => $data->total_ifs_munfiq ?? 0,
                'total_ifs_amount' => $data->total_ifs_amount ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
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
            return [
                'unit_id' => $unitId,
                'period' => 'tahunan',
                'period_date' => $data->period_date,
                'total_zf_muzakki' => $data->total_zf_muzakki ?? 0,
                'total_zf_rice' => $data->total_zf_rice ?? 0,
                'total_zf_amount' => $data->total_zf_amount ?? 0,
                'total_zm_muzakki' => $data->total_zm_muzakki ?? 0,
                'total_zm_amount' => $data->total_zm_amount ?? 0,
                'total_ifs_munfiq' => $data->total_ifs_munfiq ?? 0,
                'total_ifs_amount' => $data->total_ifs_amount ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();
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
        // Determine date format based on database driver
        $driver = DB::getDriverName();
        $dateFormat = $this->getDateFormatExpression($groupBy, $driver);
        $periodDateExpr = $this->getPeriodDateExpression($groupBy, $driver);

        // Aggregated ZF data
        $zfData = DB::table('zfs')
            ->where('unit_id', $unitId)
            ->whereBetween('trx_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw("{$periodDateExpr} as period_date")
            ->selectRaw('COALESCE(SUM(total_muzakki), 0) as total_zf_muzakki')
            ->selectRaw('COALESCE(SUM(zf_rice), 0) as total_zf_rice')
            ->selectRaw('COALESCE(SUM(zf_amount), 0) as total_zf_amount')
            ->groupByRaw($dateFormat)
            ->get()
            ->keyBy('period_date');

        // Aggregated ZM data
        $zmData = DB::table('zms')
            ->where('unit_id', $unitId)
            ->whereBetween('trx_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw("{$periodDateExpr} as period_date")
            ->selectRaw('COUNT(DISTINCT muzakki_name) as total_zm_muzakki')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_zm_amount')
            ->groupByRaw($dateFormat)
            ->get()
            ->keyBy('period_date');

        // Aggregated IFS data
        $ifsData = DB::table('ifs')
            ->where('unit_id', $unitId)
            ->whereBetween('trx_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw("{$periodDateExpr} as period_date")
            ->selectRaw('COALESCE(SUM(total_munfiq), 0) as total_ifs_munfiq')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_ifs_amount')
            ->groupByRaw($dateFormat)
            ->get()
            ->keyBy('period_date');

        // Merge all data by period_date
        $allDates = $zfData->keys()
            ->merge($zmData->keys())
            ->merge($ifsData->keys())
            ->unique();

        return $allDates->map(function ($date) use ($zfData, $zmData, $ifsData) {
            return (object) [
                'period_date' => $date,
                'total_zf_muzakki' => $zfData->get($date)?->total_zf_muzakki ?? 0,
                'total_zf_rice' => $zfData->get($date)?->total_zf_rice ?? 0,
                'total_zf_amount' => $zfData->get($date)?->total_zf_amount ?? 0,
                'total_zm_muzakki' => $zmData->get($date)?->total_zm_muzakki ?? 0,
                'total_zm_amount' => $zmData->get($date)?->total_zm_amount ?? 0,
                'total_ifs_munfiq' => $ifsData->get($date)?->total_ifs_munfiq ?? 0,
                'total_ifs_amount' => $ifsData->get($date)?->total_ifs_amount ?? 0,
            ];
        })->values()->toArray();
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
    // These methods are used by RekapZisObserver for real-time updates
    // =========================================================================

    /**
     * Update rekapitulasi harian untuk tanggal dan unit tertentu
     * 
     * This method is kept for backward compatibility with observers.
     * It does NOT trigger periodic recalculation to avoid redundant processing.
     * 
     * @param string|Carbon $date
     * @param int $unitId
     * @return RekapZis
     */
    public function updateDailyRekapitulasi($date, $unitId)
    {
        try {
            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();

            // Data ZF
            $zfData = Zf::where('unit_id', $unitId)
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->selectRaw('SUM(total_muzakki) as total_muzakki, 
                            SUM(zf_rice) as total_rice, 
                            SUM(zf_amount) as total_amount')
                ->first();

            // Data ZM
            $zmData = Zm::where('unit_id', $unitId)
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->selectRaw('COUNT(DISTINCT muzakki_name) as total_muzakki, 
                            SUM(amount) as total_amount')
                ->first();

            // Data IFS
            $ifsData = Ifs::where('unit_id', $unitId)
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->selectRaw('SUM(total_munfiq) as total_munfiq, 
                            SUM(amount) as total_amount')
                ->first();

            // Simpan atau update rekapitulasi
            $rekapitulasi = RekapZis::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'period' => 'harian',
                    'period_date' => $startDate->format('Y-m-d'),
                ],
                [
                    'total_zf_muzakki' => $zfData->total_muzakki ?? 0,
                    'total_zf_rice' => $zfData->total_rice ?? 0,
                    'total_zf_amount' => $zfData->total_amount ?? 0,
                    'total_zm_muzakki' => $zmData->total_muzakki ?? 0,
                    'total_zm_amount' => $zmData->total_amount ?? 0,
                    'total_ifs_munfiq' => $ifsData->total_munfiq ?? 0,
                    'total_ifs_amount' => $ifsData->total_amount ?? 0,
                ]
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
     * @return RekapZis
     */
    public function updateMonthlyRekapitulasi($month, $year, $unitId)
    {
        try {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();

            // Data ZF Bulanan
            $zfData = Zf::where('unit_id', $unitId)
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->selectRaw('SUM(total_muzakki) as total_muzakki, 
                            SUM(zf_rice) as total_rice, 
                            SUM(zf_amount) as total_amount')
                ->first();

            // Data ZM Bulanan
            $zmData = Zm::where('unit_id', $unitId)
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->selectRaw('COUNT(DISTINCT muzakki_name) as total_muzakki, 
                            SUM(amount) as total_amount')
                ->first();

            // Data IFS Bulanan
            $ifsData = Ifs::where('unit_id', $unitId)
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->selectRaw('SUM(total_munfiq) as total_munfiq, 
                            SUM(amount) as total_amount')
                ->first();

            // Simpan atau update rekapitulasi bulanan
            $rekapitulasi = RekapZis::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'period' => 'bulanan',
                    'period_date' => $startDate->format('Y-m-01'),
                ],
                [
                    'total_zf_muzakki' => $zfData->total_muzakki ?? 0,
                    'total_zf_rice' => $zfData->total_rice ?? 0,
                    'total_zf_amount' => $zfData->total_amount ?? 0,
                    'total_zm_muzakki' => $zmData->total_muzakki ?? 0,
                    'total_zm_amount' => $zmData->total_amount ?? 0,
                    'total_ifs_munfiq' => $ifsData->total_munfiq ?? 0,
                    'total_ifs_amount' => $ifsData->total_amount ?? 0,
                ]
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
     * @return RekapZis
     */
    public function updateYearlyRekapitulasi($year, $unitId)
    {
        try {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();

            // Data ZF Tahunan
            $zfData = Zf::where('unit_id', $unitId)
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->selectRaw('SUM(total_muzakki) as total_muzakki, 
                            SUM(zf_rice) as total_rice, 
                            SUM(zf_amount) as total_amount')
                ->first();

            // Data ZM Tahunan
            $zmData = Zm::where('unit_id', $unitId)
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->selectRaw('COUNT(DISTINCT muzakki_name) as total_muzakki, 
                            SUM(amount) as total_amount')
                ->first();

            // Data IFS Tahunan
            $ifsData = Ifs::where('unit_id', $unitId)
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->selectRaw('SUM(total_munfiq) as total_munfiq, 
                            SUM(amount) as total_amount')
                ->first();

            // Simpan atau update rekapitulasi tahunan
            $rekapitulasi = RekapZis::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'period' => 'tahunan',
                    'period_date' => $startDate->format('Y-01-01'),
                ],
                [
                    'total_zf_muzakki' => $zfData->total_muzakki ?? 0,
                    'total_zf_rice' => $zfData->total_rice ?? 0,
                    'total_zf_amount' => $zfData->total_amount ?? 0,
                    'total_zm_muzakki' => $zmData->total_muzakki ?? 0,
                    'total_zm_amount' => $zmData->total_amount ?? 0,
                    'total_ifs_munfiq' => $ifsData->total_munfiq ?? 0,
                    'total_ifs_amount' => $ifsData->total_amount ?? 0,
                ]
            );

            return $rekapitulasi;
        } catch (\Exception $e) {
            Log::error('Error updating yearly rekapitulasi: ' . $e->getMessage());
            throw $e;
        }
    }
}
