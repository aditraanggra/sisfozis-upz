<?php

namespace App\Services;

use App\Models\RekapAlokasi;
use App\Models\RekapZis;
use App\Models\UnitZis;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for rebuilding Alokasi (Allocation) recapitulation data.
 *
 * This service calculates allocation percentages from RekapZis data
 * to determine setor, kelola, hak amil, and pendis allocations.
 *
 * Extends BaseRekapService to leverage chunked processing and bulk upsert
 * for optimized performance when processing large datasets.
 */
class RekapAlokasiService extends BaseRekapService
{
    protected string $rekapTable = 'rekap_alokasi';

    protected string $periodColumn = 'periode';

    protected string $periodDateColumn = 'periode_date';

    // ===== Skala pembulatan per jenis nilai =====
    protected const RUPIAH_SCALE = 0; // rupiah bulat (INT)

    protected const RICE_SCALE = 3;   // beras disimpan 3 desimal (kg)

    // ===== Persentase kebijakan =====
    // PCT_HAK_OP is kept as it's not part of the allocation config
    protected const PCT_HAK_OP = '5';       // 5% dari SETOR

    /**
     * AllocationConfigService instance for dynamic percentage retrieval
     */
    protected AllocationConfigService $allocationConfigService;

    /**
     * Create a new RekapAlokasiService instance.
     */
    public function __construct(AllocationConfigService $allocationConfigService)
    {
        $this->allocationConfigService = $allocationConfigService;
    }

    /**
     * Rebuild rekap for given parameters using batch processing
     *
     * @param  string  $unitId  Unit ID or 'all' for all units
     * @param  string  $periode  Period type: harian, bulanan, tahunan, or all
     * @param  Carbon|null  $startDate  Start date for rebuild
     * @param  Carbon|null  $endDate  End date for rebuild
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

        if (! empty($records)) {
            $this->bulkUpsert($records);
        }
    }

    /**
     * Build records for a specific periode from RekapZis data
     */
    protected function buildRecordsForPeriode(
        int $unitId,
        string $periode,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $records = [];

        // For yearly periods, aggregate from daily and monthly data
        if ($periode === 'tahunan') {
            $records = $this->buildYearlyRecords($unitId, $startDate, $endDate);
        } else {
            // For daily and monthly periods, use existing aggregated data
            $rekapZisData = $this->getAggregatedData($unitId, $startDate, $endDate, $periode);
            $records = collect($rekapZisData)->map(function ($data) use ($unitId, $periode) {
                return $this->buildRekapRecord($unitId, $periode, $data);
            })->toArray();
        }

        return $records;
    }

    /**
     * Build yearly records by aggregating daily and monthly data
     */
    protected function buildYearlyRecords(
        int $unitId,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $records = [];

        // Get unique years within the date range
        $years = DB::table('rekap_zis')
            ->where('unit_id', $unitId)
            ->whereBetween('period_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM period_date) as year')
            ->orderBy('year')
            ->pluck('year')
            ->toArray();

        foreach ($years as $year) {
            $yearStart = Carbon::create($year, 1, 1)->startOfDay();
            $yearEnd = Carbon::create($year, 12, 31)->endOfDay();

            // Aggregate data from daily and monthly records
            $dailyData = $this->getAggregatedData($unitId, $yearStart, $yearEnd, 'harian');
            $monthlyData = $this->getAggregatedData($unitId, $yearStart, $yearEnd, 'bulanan');

            // Merge and aggregate totals
            $aggregated = $this->aggregateYearlyData($dailyData, $monthlyData, $year);

            $hasData = ($aggregated->total_zf_amount ?? 0) > 0
                    || ($aggregated->total_zf_rice ?? 0) > 0
                    || ($aggregated->total_zm_amount ?? 0) > 0
                    || ($aggregated->total_ifs_amount ?? 0) > 0;

            if ($hasData) {
                $records[] = $this->buildRekapRecord($unitId, 'tahunan', $aggregated);
            }
        }

        return $records;
    }

    /**
     * Aggregate daily and monthly data into yearly totals
     */
    protected function aggregateYearlyData(array $dailyData, array $monthlyData, int $year): object
    {
        $totals = [
            'total_zf_amount' => 0,
            'total_zf_rice' => 0,
            'total_zm_amount' => 0,
            'total_ifs_amount' => 0,
        ];

        // Handle both array of objects and array of arrays
        $dailyMonthKeys = array_unique(array_filter(array_map(function ($day) {
            // Handle both object and array formats
            $periodDate = is_object($day) ? ($day->period_date ?? null) : ($day['period_date'] ?? null);

            return ($periodDate && is_string($periodDate)) ? substr($periodDate, 0, 7) : null; // Format: YYYY-MM
        }, $dailyData)));

        // Add daily totals
        foreach ($dailyData as $day) {
            // Handle both object and array formats
            $amount = is_object($day) ? ($day->total_zf_amount ?? 0) : ($day['total_zf_amount'] ?? 0);
            $amount = (float) $amount;
            $totals['total_zf_amount'] += $amount;
            $totals['total_zf_rice'] += $amount;
            $totals['total_zm_amount'] += $amount;
            $totals['total_ifs_amount'] += $amount;
        }

        // Add monthly totals (only if no daily records exist for that month)
        foreach ($monthlyData as $month) {
            // Handle both object and array formats
            $periodDate = is_object($month) ? ($month->period_date ?? null) : ($month['period_date'] ?? null);

            if (! $periodDate || ! is_string($periodDate)) {
                continue;
            }

            $monthYear = (int) substr($periodDate, 0, 4);
            $monthKey = substr($periodDate, 0, 7); // Format: YYYY-MM

            // Only add monthly total if the month is within the target year
            // AND there are no daily records for any day in this month
            if ($monthYear === $year && ! in_array($monthKey, $dailyMonthKeys)) {
                $amount = is_object($month) ? ($month->total_zf_amount ?? 0) : ($month['total_zf_amount'] ?? 0);
                $amount = (float) $amount;
                $totals['total_zf_amount'] += $amount;
                $totals['total_zf_rice'] += $amount;
                $totals['total_zm_amount'] += $amount;
                $totals['total_ifs_amount'] += $amount;
            }
        }

        return (object) [
            'period_date' => $year.'-01-01',
            ...$totals,
        ];
    }

    /**
     * Build a single rekap record with BCMath calculations
     */
    protected function buildRekapRecord(int $unitId, string $periode, object $data): array
    {
        // Get the period date for allocation lookup
        $date = $data->period_date;

        // Get dynamic percentages for each ZIS type
        $zfAlloc = $this->allocationConfigService->getAllocation('zf', $date);
        $zmAlloc = $this->allocationConfigService->getAllocation('zm', $date);
        $ifsAlloc = $this->allocationConfigService->getAllocation('ifs', $date);

        // Convert values to string for BCMath
        $totZfAmount = $this->numStr($data->total_zf_amount ?? 0, self::RUPIAH_SCALE);
        $totZfRice = $this->numStr($data->total_zf_rice ?? 0, self::RICE_SCALE);
        $totZmAmount = $this->numStr($data->total_zm_amount ?? 0, self::RUPIAH_SCALE);
        $totIfsAmount = $this->numStr($data->total_ifs_amount ?? 0, self::RUPIAH_SCALE);

        // ===== 1) SETOR (dibulatkan per skala), KELOLA = TOTAL − SETOR =====
        // ZF Amount - using dynamic percentage
        $totalSetorZfAmount = $this->bcPercent($totZfAmount, $zfAlloc['setor'], self::RUPIAH_SCALE);
        $totalKelolaZfAmount = bcsub($totZfAmount, $totalSetorZfAmount, self::RUPIAH_SCALE);

        // ZF Rice - using dynamic percentage
        $totalSetorZfRice = $this->bcPercent($totZfRice, $zfAlloc['setor'], self::RICE_SCALE);
        $totalKelolaZfRice = bcsub($totZfRice, $totalSetorZfRice, self::RICE_SCALE);

        // ZM Amount - using dynamic percentage
        $totalSetorZm = $this->bcPercent($totZmAmount, $zmAlloc['setor'], self::RUPIAH_SCALE);
        $totalKelolaZm = bcsub($totZmAmount, $totalSetorZm, self::RUPIAH_SCALE);

        // IFS Amount - using dynamic percentage
        $totalSetorIfs = $this->bcPercent($totIfsAmount, $ifsAlloc['setor'], self::RUPIAH_SCALE);
        $totalKelolaIfs = bcsub($totIfsAmount, $totalSetorIfs, self::RUPIAH_SCALE);

        // ===== 2) Hak Amil dihitung dari KELOLA; Pendis = KELOLA − Amil =====
        // Handle zero kelola case (Requirement 10.3)

        // ZF Amount
        if (bccomp($totalKelolaZfAmount, '0', self::RUPIAH_SCALE) === 0) {
            $hakAmilZfAmount = '0';
            $alokasiPendisZfAmount = '0';
        } else {
            $hakAmilZfAmount = $this->bcPercent($totalKelolaZfAmount, $zfAlloc['amil'], self::RUPIAH_SCALE);
            $alokasiPendisZfAmount = bcsub($totalKelolaZfAmount, $hakAmilZfAmount, self::RUPIAH_SCALE);
        }

        // ZF Rice
        if (bccomp($totalKelolaZfRice, '0', self::RICE_SCALE) === 0) {
            $hakAmilZfRice = '0';
            $alokasiPendisZfRice = '0';
        } else {
            $hakAmilZfRice = $this->bcPercent($totalKelolaZfRice, $zfAlloc['amil'], self::RICE_SCALE);
            $alokasiPendisZfRice = bcsub($totalKelolaZfRice, $hakAmilZfRice, self::RICE_SCALE);
        }

        // ZM Amount
        if (bccomp($totalKelolaZm, '0', self::RUPIAH_SCALE) === 0) {
            $hakAmilZm = '0';
            $alokasiPendisZm = '0';
        } else {
            $hakAmilZm = $this->bcPercent($totalKelolaZm, $zmAlloc['amil'], self::RUPIAH_SCALE);
            $alokasiPendisZm = bcsub($totalKelolaZm, $hakAmilZm, self::RUPIAH_SCALE);
        }

        // IFS Amount
        if (bccomp($totalKelolaIfs, '0', self::RUPIAH_SCALE) === 0) {
            $hakAmilIfs = '0';
            $alokasiPendisIfs = '0';
        } else {
            $hakAmilIfs = $this->bcPercent($totalKelolaIfs, $ifsAlloc['amil'], self::RUPIAH_SCALE);
            $alokasiPendisIfs = bcsub($totalKelolaIfs, $hakAmilIfs, self::RUPIAH_SCALE);
        }

        // ===== 3) Hak Operasional 5% dari SETOR =====
        $hakOpZfAmount = $this->bcPercent($totalSetorZfAmount, self::PCT_HAK_OP, self::RUPIAH_SCALE);
        $hakOpZfRice = $this->bcPercent($totalSetorZfRice, self::PCT_HAK_OP, self::RICE_SCALE);

        return [
            'unit_id' => $unitId,
            'periode' => $periode,
            'periode_date' => $data->period_date,
            'total_setor_zf_amount' => (int) $totalSetorZfAmount,
            'total_setor_zf_rice' => $totalSetorZfRice,
            'total_setor_zm' => (int) $totalSetorZm,
            'total_setor_ifs' => (int) $totalSetorIfs,
            'total_kelola_zf_amount' => (int) $totalKelolaZfAmount,
            'total_kelola_zf_rice' => $totalKelolaZfRice,
            'total_kelola_zm' => (int) $totalKelolaZm,
            'total_kelola_ifs' => (int) $totalKelolaIfs,
            'hak_amil_zf_amount' => (int) $hakAmilZfAmount,
            'hak_amil_zf_rice' => $hakAmilZfRice,
            'hak_amil_zm' => (int) $hakAmilZm,
            'hak_amil_ifs' => (int) $hakAmilIfs,
            'alokasi_pendis_zf_amount' => (int) $alokasiPendisZfAmount,
            'alokasi_pendis_zf_rice' => $alokasiPendisZfRice,
            'alokasi_pendis_zm' => (int) $alokasiPendisZm,
            'alokasi_pendis_ifs' => (int) $alokasiPendisIfs,
            'hak_op_zf_amount' => (int) $hakOpZfAmount,
            'hak_op_zf_rice' => $hakOpZfRice,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Get RekapZis data for a unit and date range
     *
     * @param  string  $periode  Period type: harian, bulanan, tahunan
     */
    protected function getAggregatedData(
        int $unitId,
        Carbon $startDate,
        Carbon $endDate,
        string $periode = 'harian'
    ): array {
        return DB::table('rekap_zis')
            ->where('unit_id', $unitId)
            ->where('period', $periode)
            ->whereBetween('period_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select([
                'period_date',
                'total_zf_amount',
                'total_zf_rice',
                'total_zm_amount',
                'total_ifs_amount',
            ])
            ->get()
            ->toArray();
    }

    /**
     * BCMath percentage calculation
     */
    protected function bcPercent(string $value, string $percent, int $scale = 0): string
    {
        $mul = bcmul($value, $percent, $scale + 4);

        return bcdiv($mul, '100', $scale);
    }

    /**
     * Convert value to numeric string for BCMath
     *
     * @param  mixed  $v
     */
    protected function numStr($v, int $scale = 0): string
    {
        if ($v === null || $v === '') {
            return $scale > 0 ? ('0.'.str_repeat('0', $scale)) : '0';
        }

        return (string) $v;
    }

    // =========================================================================
    // Legacy methods for backward compatibility
    // =========================================================================

    /**
     * Update or create rekap alokasi based on rekap zis data (legacy method)
     */
    public function updateOrCreateRekapAlokasi(int $unitId, string $period): RekapAlokasi
    {
        try {
            DB::beginTransaction();

            // Get rekap_zis data
            $rekapZis = RekapZis::where('unit_id', $unitId)
                ->where('period', $period)
                ->first();

            if (! $rekapZis) {
                throw new \Exception("RekapZis record not found for unit_id: {$unitId} and period: {$period}");
            }

            // Get the period date for allocation lookup
            $date = $rekapZis->period_date;

            // Get dynamic percentages for each ZIS type
            $zfAlloc = $this->allocationConfigService->getAllocation('zf', $date);
            $zmAlloc = $this->allocationConfigService->getAllocation('zm', $date);
            $ifsAlloc = $this->allocationConfigService->getAllocation('ifs', $date);

            // Convert values to string for BCMath
            $totZfAmount = $this->numStr($rekapZis->total_zf_amount, self::RUPIAH_SCALE);
            $totZfRice = $this->numStr($rekapZis->total_zf_rice, self::RICE_SCALE);
            $totZmAmount = $this->numStr($rekapZis->total_zm_amount, self::RUPIAH_SCALE);
            $totIfsAmount = $this->numStr($rekapZis->total_ifs_amount, self::RUPIAH_SCALE);

            // ===== 1) SETOR (dibulatkan per skala), KELOLA = TOTAL − SETOR =====
            // ZF Amount - using dynamic percentage
            $totalSetorZfAmount = $this->bcPercent($totZfAmount, $zfAlloc['setor'], self::RUPIAH_SCALE);
            $totalKelolaZfAmount = bcsub($totZfAmount, $totalSetorZfAmount, self::RUPIAH_SCALE);

            // ZF Rice - using dynamic percentage
            $totalSetorZfRice = $this->bcPercent($totZfRice, $zfAlloc['setor'], self::RICE_SCALE);
            $totalKelolaZfRice = bcsub($totZfRice, $totalSetorZfRice, self::RICE_SCALE);

            // ZM Amount - using dynamic percentage
            $totalSetorZm = $this->bcPercent($totZmAmount, $zmAlloc['setor'], self::RUPIAH_SCALE);
            $totalKelolaZm = bcsub($totZmAmount, $totalSetorZm, self::RUPIAH_SCALE);

            // IFS Amount - using dynamic percentage
            $totalSetorIfs = $this->bcPercent($totIfsAmount, $ifsAlloc['setor'], self::RUPIAH_SCALE);
            $totalKelolaIfs = bcsub($totIfsAmount, $totalSetorIfs, self::RUPIAH_SCALE);

            // ===== 2) Hak Amil dihitung dari KELOLA; Pendis = KELOLA − Amil =====
            // Handle zero kelola case (Requirement 10.3)

            // ZF Amount
            if (bccomp($totalKelolaZfAmount, '0', self::RUPIAH_SCALE) === 0) {
                $hakAmilZfAmount = '0';
                $alokasiPendisZfAmount = '0';
            } else {
                $hakAmilZfAmount = $this->bcPercent($totalKelolaZfAmount, $zfAlloc['amil'], self::RUPIAH_SCALE);
                $alokasiPendisZfAmount = bcsub($totalKelolaZfAmount, $hakAmilZfAmount, self::RUPIAH_SCALE);
            }

            // ZF Rice
            if (bccomp($totalKelolaZfRice, '0', self::RICE_SCALE) === 0) {
                $hakAmilZfRice = '0';
                $alokasiPendisZfRice = '0';
            } else {
                $hakAmilZfRice = $this->bcPercent($totalKelolaZfRice, $zfAlloc['amil'], self::RICE_SCALE);
                $alokasiPendisZfRice = bcsub($totalKelolaZfRice, $hakAmilZfRice, self::RICE_SCALE);
            }

            // ZM Amount
            if (bccomp($totalKelolaZm, '0', self::RUPIAH_SCALE) === 0) {
                $hakAmilZm = '0';
                $alokasiPendisZm = '0';
            } else {
                $hakAmilZm = $this->bcPercent($totalKelolaZm, $zmAlloc['amil'], self::RUPIAH_SCALE);
                $alokasiPendisZm = bcsub($totalKelolaZm, $hakAmilZm, self::RUPIAH_SCALE);
            }

            // IFS Amount
            if (bccomp($totalKelolaIfs, '0', self::RUPIAH_SCALE) === 0) {
                $hakAmilIfs = '0';
                $alokasiPendisIfs = '0';
            } else {
                $hakAmilIfs = $this->bcPercent($totalKelolaIfs, $ifsAlloc['amil'], self::RUPIAH_SCALE);
                $alokasiPendisIfs = bcsub($totalKelolaIfs, $hakAmilIfs, self::RUPIAH_SCALE);
            }

            // ===== 3) Hak Operasional 5% dari SETOR =====
            $hakOpZfAmount = $this->bcPercent($totalSetorZfAmount, self::PCT_HAK_OP, self::RUPIAH_SCALE);
            $hakOpZfRice = $this->bcPercent($totalSetorZfRice, self::PCT_HAK_OP, self::RICE_SCALE);

            // Update or create rekap_alokasi record
            $rekapAlokasi = RekapAlokasi::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => $period,
                ],
                [
                    'periode_date' => $rekapZis->period_date,
                    'total_setor_zf_amount' => (int) $totalSetorZfAmount,
                    'total_setor_zf_rice' => $totalSetorZfRice,
                    'total_setor_zm' => (int) $totalSetorZm,
                    'total_setor_ifs' => (int) $totalSetorIfs,
                    'total_kelola_zf_amount' => (int) $totalKelolaZfAmount,
                    'total_kelola_zf_rice' => $totalKelolaZfRice,
                    'total_kelola_zm' => (int) $totalKelolaZm,
                    'total_kelola_ifs' => (int) $totalKelolaIfs,
                    'hak_amil_zf_amount' => (int) $hakAmilZfAmount,
                    'hak_amil_zf_rice' => $hakAmilZfRice,
                    'hak_amil_zm' => (int) $hakAmilZm,
                    'hak_amil_ifs' => (int) $hakAmilIfs,
                    'alokasi_pendis_zf_amount' => (int) $alokasiPendisZfAmount,
                    'alokasi_pendis_zf_rice' => $alokasiPendisZfRice,
                    'alokasi_pendis_zm' => (int) $alokasiPendisZm,
                    'alokasi_pendis_ifs' => (int) $alokasiPendisIfs,
                    'hak_op_zf_amount' => (int) $hakOpZfAmount,
                    'hak_op_zf_rice' => $hakOpZfRice,
                ]
            );

            DB::commit();

            return $rekapAlokasi;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating rekap alokasi: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Update all rekap alokasi records (legacy method)
     */
    public function rebuildAllRekapAlokasi(): array
    {
        $results = [];
        $rekapZisRecords = RekapZis::all();

        foreach ($rekapZisRecords as $rekapZis) {
            try {
                if ($rekapZis->unit_id !== null && $rekapZis->period !== null) {
                    $rekapAlokasi = $this->updateOrCreateRekapAlokasi(
                        (int) $rekapZis->unit_id,
                        (string) $rekapZis->period
                    );

                    $results[] = [
                        'id' => $rekapZis->id,
                        'unit_id' => $rekapZis->unit_id,
                        'periode' => $rekapZis->period,
                        'status' => 'success',
                    ];
                } else {
                    $errors = [];
                    if ($rekapZis->unit_id === null) {
                        $errors[] = 'unit_id is null';
                    }
                    if ($rekapZis->period === null) {
                        $errors[] = 'period is null';
                    }
                    $errorMessage = 'Missing required data: '.implode(', ', $errors);

                    $results[] = [
                        'id' => $rekapZis->id,
                        'unit_id' => $rekapZis->unit_id,
                        'periode' => $rekapZis->period,
                        'status' => 'error',
                        'message' => $errorMessage,
                    ];
                }
            } catch (\Exception $e) {
                Log::error("Error updating rekap alokasi for rekap_zis ID: {$rekapZis->id}, Error: {$e->getMessage()}");

                $results[] = [
                    'id' => $rekapZis->id,
                    'unit_id' => $rekapZis->unit_id ?? 'unknown',
                    'periode' => $rekapZis->period ?? 'unknown',
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
