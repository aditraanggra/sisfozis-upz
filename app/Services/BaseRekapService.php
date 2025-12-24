<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Base abstract class for rekap services providing common functionality
 * for chunked processing, bulk upsert operations, and error handling.
 */
abstract class BaseRekapService
{
    /**
     * Number of units to process per chunk
     */
    protected int $chunkSize = 50;

    /**
     * The rekap table name for bulk upsert operations
     */
    protected string $rekapTable;

    public function __construct()
    {
        if (!isset($this->rekapTable)) {
            throw new \LogicException(static::class . ' must set the $rekapTable property');
        }
    }
    /**
     * The period column name in the rekap table
     */
    protected string $periodColumn = 'period';

    /**
     * The period date column name in the rekap table
     */
    protected string $periodDateColumn = 'period_date';

    /**
     * Set chunk size for processing
     *
     * @param int $size Number of units per chunk
     * @return self
     */
    public function setChunkSize(int $size): self
    {
        $this->chunkSize = max(1, $size);
        return $this;
    }

    /**
     * Get current chunk size
     *
     * @return int
     */
    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * Rebuild rekap for given parameters
     *
     * @param string $unitId Unit ID or 'all' for all units
     * @param string $periode Period type: harian, bulanan, tahunan, or all
     * @param Carbon|null $startDate Start date for rebuild
     * @param Carbon|null $endDate End date for rebuild
     * @return array Results with 'processed' count and 'errors' array
     */
    abstract public function rebuild(
        string $unitId = 'all',
        string $periode = 'all',
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array;

    /**
     * Get aggregated data for a unit and date range
     *
     * @param int $unitId The unit ID
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return array Aggregated data
     */
    abstract protected function getAggregatedData(
        int $unitId,
        Carbon $startDate,
        Carbon $endDate
    ): array;

    /**
     * Bulk upsert rekap records using database-level upsert
     *
     * @param array $records Array of records to upsert
     * @return int Number of affected rows
     */
    protected function bulkUpsert(array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        // Get the unique keys for upsert
        $uniqueKeys = ['unit_id', $this->periodColumn, $this->periodDateColumn];

        // Get update columns (all columns except unique keys)
        $updateColumns = array_diff(array_keys($records[0]), $uniqueKeys);

        return DB::table($this->rekapTable)->upsert(
            $records,
            $uniqueKeys,
            array_values($updateColumns)
        );
    }

    /**
     * Process units in chunks with error isolation
     *
     * Each unit is processed independently - if one fails, others continue.
     * Memory is released after each chunk via garbage collection.
     *
     * @param Builder $unitQuery Query builder for units to process
     * @param callable $processor Callback function to process each unit
     * @return array Results with 'processed' count and 'errors' array
     */
    protected function processInChunks(
        Builder $unitQuery,
        callable $processor
    ): array {
        $results = [
            'processed' => 0,
            'errors' => []
        ];

        $unitQuery->chunk($this->chunkSize, function ($units) use ($processor, &$results) {
            foreach ($units as $unit) {
                try {
                    $processor($unit);
                    $results['processed']++;
                } catch (\Throwable $e) {
                    $results['errors'][] = [
                        'unit_id' => $unit->id,
                        'error' => $e->getMessage()
                    ];
                    Log::error("Rebuild error for unit {$unit->id}: " . $e->getMessage(), [
                        'exception' => $e,
                        'unit_id' => $unit->id,
                    ]);
                }
            }

            // Release memory after each chunk
            gc_collect_cycles();
        });

        return $results;
    }

    /**
     * Get default start date (30 days ago)
     *
     * @return Carbon
     */
    protected function getDefaultStartDate(): Carbon
    {
        return Carbon::now()->subDays(30)->startOfDay();
    }

    /**
     * Get default end date (today)
     *
     * @return Carbon
     */
    protected function getDefaultEndDate(): Carbon
    {
        return Carbon::now()->endOfDay();
    }

    /**
     * Build date ranges for monthly aggregation
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array Array of [start, end] Carbon pairs for each month
     */
    protected function getMonthlyDateRanges(Carbon $startDate, Carbon $endDate): array
    {
        $ranges = [];
        $current = $startDate->copy()->startOfMonth();

        while ($current <= $endDate) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            // Clamp to actual date range
            if ($monthStart < $startDate) {
                $monthStart = $startDate->copy();
            }
            if ($monthEnd > $endDate) {
                $monthEnd = $endDate->copy();
            }

            $ranges[] = [
                'start' => $monthStart,
                'end' => $monthEnd,
                'period_date' => $current->copy()->startOfMonth()->format('Y-m-01'),
            ];

            $current->addMonth();
        }

        return $ranges;
    }

    /**
     * Build date ranges for yearly aggregation
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array Array of [start, end] Carbon pairs for each year
     */
    protected function getYearlyDateRanges(Carbon $startDate, Carbon $endDate): array
    {
        if ($startDate > $endDate) {
            throw new \InvalidArgumentException('Start date must be before or equal to end date');
        }

        $ranges = [];
        $current = $startDate->copy()->startOfYear();

        while ($current <= $endDate) {
            $yearStart = $current->copy()->startOfYear();
            $yearEnd = $current->copy()->endOfYear();

            // Clamp to actual date range
            if ($yearStart < $startDate) {
                $yearStart = $startDate->copy();
            }
            if ($yearEnd > $endDate) {
                $yearEnd = $endDate->copy();
            }

            $ranges[] = [
                'start' => $yearStart,
                'end' => $yearEnd,
                'period_date' => $current->copy()->startOfYear()->format('Y-01-01'),
            ];

            $current->addYear();
        }

        return $ranges;
    }
    /**
     * Wrap a unit rebuild operation in a database transaction
     *
     * @param int $unitId
     * @param callable $operation
     * @return mixed
     * @throws \Exception
     */
    protected function wrapInTransaction(int $unitId, callable $operation): mixed
    {
        try {
            DB::beginTransaction();
            $result = $operation();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Transaction failed for unit {$unitId}: " . $e->getMessage());
            throw $e;
        }
    }
}
