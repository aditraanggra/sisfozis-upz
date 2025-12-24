<?php

namespace App\Jobs;

use App\Services\BaseRekapService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queue job for rebuilding rekap data in background.
 * 
 * This job processes a chunk of units for a specific rekap service,
 * enabling parallel processing and keeping the server responsive
 * during long-running rebuild operations.
 * 
 * @see Requirements 2.1, 2.3
 */
class RebuildRekapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * 
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     * Set to 1 hour to accommodate large datasets.
     * 
     * @var int
     */
    public int $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @param string $serviceClass The fully qualified class name of the rekap service
     * @param array $unitIds Array of unit IDs to process in this job
     * @param string $periode Period type: harian, bulanan, tahunan, or all
     * @param string|null $startDate Start date in Y-m-d format
     * @param string|null $endDate End date in Y-m-d format
     * @param int $chunkSize Number of units to process per chunk within the service
     */
    public function __construct(
        public string $serviceClass,
        public array $unitIds,
        public string $periode,
        public ?string $startDate,
        public ?string $endDate,
        public int $chunkSize = 50
    ) {}

    /**
     * Execute the job.
     *
     * Processes each unit ID in the chunk using the specified rekap service.
     * Errors are logged but don't stop processing of other units.
     *
     * @return void
     * @throws \Exception If a critical error occurs that should trigger retry
     */
    public function handle(): void
    {
        /** @var BaseRekapService $service */
        $service = app($this->serviceClass);

        if (!$service instanceof BaseRekapService) {
            throw new \InvalidArgumentException(
                "Service class {$this->serviceClass} must extend BaseRekapService"
            );
        }

        $service->setChunkSize($this->chunkSize);
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;

        $processedCount = 0;
        $errorCount = 0;

        Log::info("RebuildRekapJob started", [
            'service' => $this->serviceClass,
            'unit_count' => count($this->unitIds),
            'periode' => $this->periode,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        foreach ($this->unitIds as $unitId) {
            try {
                $service->rebuild((string) $unitId, $this->periode, $startDate, $endDate);
                $processedCount++;

                Log::debug("Rebuilt rekap for unit {$unitId}");
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Failed to rebuild rekap for unit {$unitId}: " . $e->getMessage(), [
                    'unit_id' => $unitId,
                    'service' => $this->serviceClass,
                    'exception' => $e,
                ]);

                // Continue processing other units - error isolation per Requirements 4.4, 6.4
                // Only throw if ALL units fail, indicating a systemic issue
            }
        }

        Log::info("RebuildRekapJob completed", [
            'service' => $this->serviceClass,
            'processed' => $processedCount,
            'errors' => $errorCount,
            'total' => count($this->unitIds),
        ]);

        // If all units failed, throw exception to trigger retry
        if ($errorCount === count($this->unitIds) && count($this->unitIds) > 0) {
            throw new \RuntimeException(
                "All {$errorCount} units failed to process. Check logs for details."
            );
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("RebuildRekapJob failed permanently", [
            'service' => $this->serviceClass,
            'unit_ids' => $this->unitIds,
            'periode' => $this->periode,
            'exception' => $exception->getMessage(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'rebuild-rekap',
            'service:' . class_basename($this->serviceClass),
            'periode:' . $this->periode,
            'units:' . count($this->unitIds),
        ];
    }
}
