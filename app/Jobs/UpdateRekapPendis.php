<?php
// File: app/Jobs/UpdateRekapPendis.php
namespace App\Jobs;

use App\Services\RekapPendisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateRekapPendis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;
    protected $unitId;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @param string $date
     * @param int $unitId
     * @return void
     */
    public function __construct($date, $unitId)
    {
        $this->date = $date;
        $this->unitId = $unitId;
    }

    /**
     * Execute the job.
     *
     * @param RekapPendisService $rekapService
     * @return void
     */
    public function handle(RekapPendisService $rekapService)
    {
        try {
            Log::info("Starting UpdateRekapPendis job for date: {$this->date}, unit: {$this->unitId}");

            // Validasi input
            if (empty($this->date) || empty($this->unitId)) {
                Log::error("Invalid input for UpdateRekapPendis job. Date: {$this->date}, UnitId: {$this->unitId}");
                return;
            }

            // Panggil service
            $rekapService->updateDailyRekapPendis($this->date, $this->unitId);

            Log::info("Successfully completed UpdateRekapPendis job for date: {$this->date}, unit: {$this->unitId}");
        } catch (\Exception $e) {
            Log::error("Error in UpdateRekapPendis job: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            // Tambahkan informasi tentang batas percobaan
            if ($this->attempts() >= $this->tries) {
                Log::error("Job UpdateRekapPendis has been attempted {$this->tries} times and will not be retried.");
            } else {
                Log::info("Job UpdateRekapPendis will be retried. Attempt: {$this->attempts()}");
            }

            throw $e; // Lempar exception agar job gagal dan dicoba lagi
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Job UpdateRekapPendis failed finally after {$this->attempts()} attempts for date: {$this->date}, unit: {$this->unitId}");
        Log::error("Final error: " . $exception->getMessage());
    }
}
