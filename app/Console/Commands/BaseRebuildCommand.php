<?php

namespace App\Console\Commands;

use App\Jobs\RebuildRekapJob;
use App\Models\UnitZis;
use App\Services\BaseRekapService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Base abstract class for rebuild commands providing standardized interface
 * and common functionality for all rekap rebuild operations.
 *
 * All rebuild commands should extend this class to ensure consistent
 * behavior, options, and output formatting.
 *
 * @see Requirements 5.1, 5.2, 5.3, 5.4, 4.1, 4.3
 */
abstract class BaseRebuildCommand extends Command
{
    /**
     * The fully qualified class name of the rekap service to use.
     * Must be set by child classes.
     *
     * @var string
     */
    protected string $serviceClass;

    /**
     * Human-readable name of the rekap type for display purposes.
     * Must be set by child classes.
     *
     * @var string
     */
    protected string $rekapType;

    /**
     * Valid periode values for validation.
     *
     * @var array<string>
     */
    protected array $allowedPeriodes = ['harian', 'bulanan', 'tahunan', 'all'];

    /**
     * Default chunk size for processing units.
     *
     * @var int
     */
    protected int $defaultChunkSize = 50;

    /**
     * Default number of days to look back when no date range specified.
     *
     * @var int
     */
    protected int $defaultDaysBack = 30;

    /**
     * Track start time for duration calculation.
     *
     * @var float
     */
    protected float $startTime;

    /**
     * Get the standardized signature options string.
     * Child classes should append this to their signature.
     *
     * @return string
     */
    protected function getSignatureOptions(): string
    {
        return '{--unit=all : ID unit atau "all" untuk semua unit}
                {--start= : Tanggal mulai format Y-m-d}
                {--end= : Tanggal akhir format Y-m-d}
                {--periode=all : Periode (harian, bulanan, tahunan, all)}
                {--chunk-size=50 : Jumlah unit per batch}
                {--queue : Jalankan sebagai background job}';
    }

    /**
     * Execute the console command.
     *
     * @return int Exit code (0 for success, 1 for failure)
     */
    public function handle(): int
    {
        $this->startTime = microtime(true);

        $unitOption = $this->option('unit');
        $startDate = $this->option('start');
        $endDate = $this->option('end');
        $periode = $this->option('periode');
        $chunkSize = (int) $this->option('chunk-size');
        $useQueue = $this->option('queue');

        // Validate inputs - Requirements 5.2, 5.3
        if (!$this->validateInputs($startDate, $endDate, $periode)) {
            return 1;
        }

        // Parse dates with defaults - Requirements 5.4
        $parsedStartDate = $startDate
            ? Carbon::parse($startDate)
            : Carbon::now()->subDays($this->defaultDaysBack);
        $parsedEndDate = $endDate
            ? Carbon::parse($endDate)
            : Carbon::now();

        // Display start info - Requirements 4.1
        $this->displayStartInfo($parsedStartDate, $parsedEndDate, $unitOption, $periode);

        if ($useQueue) {
            return $this->dispatchToQueue($unitOption, $periode, $startDate, $endDate, $chunkSize);
        }

        return $this->runSync($unitOption, $periode, $parsedStartDate, $parsedEndDate, $chunkSize);
    }

    /**
     * Validate command inputs.
     *
     * @param string|null $startDate Start date string
     * @param string|null $endDate End date string
     * @param string $periode Period type
     * @return bool True if valid, false otherwise
     */
    protected function validateInputs(?string $startDate, ?string $endDate, string $periode): bool
    {
        // Validate start date format - Requirements 5.2
        if ($startDate && !$this->isValidDate($startDate)) {
            $this->error('Format tanggal mulai tidak valid. Gunakan format Y-m-d');
            return false;
        }

        // Validate end date format - Requirements 5.2
        if ($endDate && !$this->isValidDate($endDate)) {
            $this->error('Format tanggal akhir tidak valid. Gunakan format Y-m-d');
            return false;
        }

        // Validate date range logic
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            if ($start > $end) {
                $this->error('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
                return false;
            }
        }

        // Validate periode value - Requirements 5.3
        if (!in_array($periode, $this->allowedPeriodes)) {
            $this->error('Periode tidak valid. Pilih: ' . implode(', ', $this->allowedPeriodes));
            return false;
        }

        return true;
    }

    /**
     * Check if a date string is valid Y-m-d format.
     *
     * @param string $date Date string to validate
     * @return bool True if valid format
     */
    protected function isValidDate(string $date): bool
    {
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    /**
     * Dispatch rebuild jobs to queue for background processing.
     *
     * @param string $unitOption Unit ID or 'all'
     * @param string $periode Period type
     * @param string|null $startDate Start date string
     * @param string|null $endDate End date string
     * @param int $chunkSize Units per job
     * @return int Exit code
     */
    protected function dispatchToQueue(
        string $unitOption,
        string $periode,
        ?string $startDate,
        ?string $endDate,
        int $chunkSize
    ): int {
        $unitIds = $this->getUnitIds($unitOption);

        if (empty($unitIds)) {
            $this->error('Unit tidak ditemukan!');
            return 1;
        }

        $chunks = array_chunk($unitIds, $chunkSize);
        $totalJobs = count($chunks);

        $this->info("Dispatching {$totalJobs} job(s) to queue 'rebuild'...");
        $this->newLine();

        $bar = $this->output->createProgressBar($totalJobs);
        $bar->start();

        foreach ($chunks as $chunk) {
            RebuildRekapJob::dispatch(
                $this->serviceClass,
                $chunk,
                $periode,
                $startDate,
                $endDate,
                $chunkSize
            )->onQueue('rebuild');

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display summary - Requirements 4.3
        $this->displayQueueSummary($totalJobs, count($unitIds), $chunkSize);

        return 0;
    }

    /**
     * Run rebuild synchronously with progress display.
     *
     * @param string $unitOption Unit ID or 'all'
     * @param string $periode Period type
     * @param Carbon $startDate Parsed start date
     * @param Carbon $endDate Parsed end date
     * @param int $chunkSize Units per chunk
     * @return int Exit code
     */
    protected function runSync(
        string $unitOption,
        string $periode,
        Carbon $startDate,
        Carbon $endDate,
        int $chunkSize
    ): int {
        /** @var BaseRekapService $service */
        $service = app($this->serviceClass);
        $service->setChunkSize($chunkSize);

        // Get total units for progress estimation - Requirements 4.1
        $totalUnits = $this->getTotalUnits($unitOption);

        if ($totalUnits === 0) {
            $this->error('Unit tidak ditemukan!');
            return 1;
        }

        $this->info("Total unit: {$totalUnits}");
        $this->info("Chunk size: {$chunkSize}");
        $this->newLine();

        // Execute rebuild
        $result = $service->rebuild($unitOption, $periode, $startDate, $endDate);

        // Display summary - Requirements 4.3
        $this->displaySyncSummary($result);

        return empty($result['errors']) ? 0 : 1;
    }

    /**
     * Get array of unit IDs based on option value.
     *
     * @param string $unitOption Unit ID or 'all'
     * @return array<int> Array of unit IDs
     */
    protected function getUnitIds(string $unitOption): array
    {
        if ($unitOption === 'all') {
            return UnitZis::pluck('id')->toArray();
        }

        // Check if unit exists
        $unit = UnitZis::find((int) $unitOption);
        if (!$unit) {
            return [];
        }

        return [(int) $unitOption];
    }

    /**
     * Get total number of units to process.
     *
     * @param string $unitOption Unit ID or 'all'
     * @return int Total unit count
     */
    protected function getTotalUnits(string $unitOption): int
    {
        if ($unitOption === 'all') {
            return UnitZis::count();
        }

        return UnitZis::where('id', (int) $unitOption)->count();
    }

    /**
     * Display start information.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $unitOption
     * @param string $periode
     * @return void
     */
    protected function displayStartInfo(
        Carbon $startDate,
        Carbon $endDate,
        string $unitOption,
        string $periode
    ): void {
        $this->info("=== Rebuild {$this->rekapType} ===");
        $this->info("Periode: {$startDate->format('Y-m-d')} sampai {$endDate->format('Y-m-d')}");
        $this->info("Unit: " . ($unitOption === 'all' ? 'Semua unit' : "Unit ID {$unitOption}"));
        $this->info("Tipe periode: {$periode}");
        $this->newLine();
    }

    /**
     * Display summary for queue dispatch.
     *
     * @param int $totalJobs Number of jobs dispatched
     * @param int $totalUnits Total units to process
     * @param int $chunkSize Units per job
     * @return void
     */
    protected function displayQueueSummary(int $totalJobs, int $totalUnits, int $chunkSize): void
    {
        $duration = round(microtime(true) - $this->startTime, 2);

        $this->info("=== Ringkasan ===");
        $this->info("Total job dispatched: {$totalJobs}");
        $this->info("Total unit: {$totalUnits}");
        $this->info("Unit per job: {$chunkSize}");
        $this->info("Waktu dispatch: {$duration} detik");
        $this->newLine();
        $this->info("Job akan diproses di background. Monitor dengan: php artisan queue:work --queue=rebuild");
    }

    /**
     * Display summary for synchronous execution.
     *
     * @param array $result Result array with 'processed' and 'errors' keys
     * @return void
     */
    protected function displaySyncSummary(array $result): void
    {
        $duration = round(microtime(true) - $this->startTime, 2);

        $this->newLine();
        $this->info("=== Ringkasan ===");
        $this->info("Waktu eksekusi: {$duration} detik");
        $this->info("Unit berhasil diproses: {$result['processed']}");

        if (!empty($result['errors'])) {
            $errorCount = count($result['errors']);
            $this->warn("Unit gagal diproses: {$errorCount}");
            $this->newLine();

            // Display error details - Requirements 4.4
            $this->error("Detail error:");
            foreach ($result['errors'] as $error) {
                $this->error("  - Unit {$error['unit_id']}: {$error['error']}");
            }
        } else {
            $this->info("Tidak ada error.");
        }

        $this->newLine();
        $this->info("{$this->rekapType} berhasil dibangun ulang!");
    }

    /**
     * Calculate estimated completion time based on historical data.
     * Can be overridden by child classes for more accurate estimates.
     *
     * @param int $totalUnits Total units to process
     * @param int $totalDays Total days in date range
     * @return string Human-readable estimate
     */
    protected function estimateCompletionTime(int $totalUnits, int $totalDays): string
    {
        // Rough estimate: ~0.5 seconds per unit per day
        $estimatedSeconds = $totalUnits * $totalDays * 0.5;

        if ($estimatedSeconds < 60) {
            return "< 1 menit";
        } elseif ($estimatedSeconds < 3600) {
            $minutes = ceil($estimatedSeconds / 60);
            return "~{$minutes} menit";
        } else {
            $hours = round($estimatedSeconds / 3600, 1);
            return "~{$hours} jam";
        }
    }
}
