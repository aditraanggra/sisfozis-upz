<?php
// File: app/Services/RekapAlokasiService.php
namespace App\Services;

use App\Models\RekapAlokasi;
use App\Models\RekapZis;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class RekapAlokasiService
{
    /**
     * Update rekapitulasi alokasi harian untuk tanggal dan unit tertentu
     * 
     * @param string|Carbon $date
     * @param int $unitId
     * @return RekapAlokasi
     */
    public function updateDailyRekapAlokasi($date, $unitId)
    {
        try {
            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();

            // Get RekapZis data for the same period
            $rekapZis = RekapZis::where('unit_id', $unitId)
                ->where('periode', 'harian')
                ->where('periode_date', $startDate->format('Y-m-d'))
                ->first();

            if (!$rekapZis) {
                throw new \Exception('RekapZis data not found for this period');
            }

            // Calculate allocation data
            $allocationData = $this->calculateAllocationData($rekapZis);

            // Save or update allocation
            $rekapitulasi = RekapAlokasi::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => 'harian',
                    'periode_date' => $startDate->format('Y-m-d'),
                ],
                $allocationData
            );

            // Update weekly, monthly, and yearly allocation if needed
            $this->updatePeriodicRekapitulasi($startDate, $unitId);

            return $rekapitulasi;
        } catch (\Exception $e) {
            Log::error('Error updating rekapitulasi alokasi: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update rekapitulasi mingguan, bulanan, dan tahunan
     * 
     * @param Carbon $date
     * @param int $unitId
     */
    protected function updatePeriodicRekapitulasi(Carbon $date, $unitId)
    {
        // Update rekapitulasi bulanan
        $this->updateMonthlyRekapAlokasi($date->month, $date->year, $unitId);

        // Update rekapitulasi tahunan
        $this->updateYearlyRekapAlokasi($date->year, $unitId);
    }

    /**
     * Update rekapitulasi bulanan
     * 
     * @param int $month
     * @param int $year
     * @param int $unitId
     * @return RekapAlokasi
     */
    public function updateMonthlyRekapAlokasi($month, $year, $unitId)
    {
        try {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();

            // Get RekapZis data for the same period
            $rekapZis = RekapZis::where('unit_id', $unitId)
                ->where('periode', 'bulanan')
                ->where('periode_date', $startDate->format('Y-m-01'))
                ->first();

            if (!$rekapZis) {
                throw new \Exception('RekapZis data not found for this period');
            }

            // Calculate allocation data
            $allocationData = $this->calculateAllocationData($rekapZis);

            // Save or update allocation
            $rekapitulasi = RekapAlokasi::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => 'bulanan',
                    'periode_date' => $startDate->format('Y-m-01'),
                ],
                $allocationData
            );

            return $rekapitulasi;
        } catch (\Exception $e) {
            Log::error('Error updating monthly rekapitulasi alokasi: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update rekapitulasi tahunan
     * 
     * @param int $year
     * @param int $unitId
     * @return RekapAlokasi
     */
    public function updateYearlyRekapAlokasi($year, $unitId)
    {
        try {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();

            // Get RekapZis data for the same period
            $rekapZis = RekapZis::where('unit_id', $unitId)
                ->where('periode', 'tahunan')
                ->where('periode_date', $startDate->format('Y-01-01'))
                ->first();

            if (!$rekapZis) {
                throw new \Exception('RekapZis data not found for this period');
            }

            // Calculate allocation data
            $allocationData = $this->calculateAllocationData($rekapZis);

            // Save or update allocation
            $rekapitulasi = RekapAlokasi::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => 'tahunan',
                    'periode_date' => $startDate->format('Y-01-01'),
                ],
                $allocationData
            );

            return $rekapitulasi;
        } catch (\Exception $e) {
            Log::error('Error updating yearly rekapitulasi alokasi: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Menghitung data alokasi berdasarkan data RekapZis
     * 
     * @param RekapZis $rekapZis
     * @return array
     */
    protected function calculateAllocationData(RekapZis $rekapZis)
    {
        return [
            'total_setor_zf_amount' => $rekapZis->total_zf_amount * 0.3,
            'total_setor_zf_rice' => $rekapZis->total_zf_rice * 0.3,
            'total_setor_zm' => $rekapZis->total_zm_amount * 0.3,
            'total_setor_ifs' => $rekapZis->total_ifs_amount * 0.3,
            'total_kelola_zf_amount' => $rekapZis->total_zf_amount * 0.7,
            'total_kelola_zf_rice' => $rekapZis->total_zf_rice * 0.7,
            'total_kelola_zm' => $rekapZis->total_zm_amount * 0.7,
            'total_kelola_ifs' => $rekapZis->total_ifs_amount * 0.7,
            'hak_amil_zf_amount' => $rekapZis->total_zf_amount * 0.7 * 0.125,
            'hak_amil_zf_rice' => $rekapZis->total_zf_rice * 0.7 * 0.125,
            'hak_amil_zm' => $rekapZis->total_zm_amount * 0.7 * 0.125,
            'hak_amil_ifs' => $rekapZis->total_ifs_amount * 0.7 * 0.2,
            'alokasi_pendis_zf_amount' => $rekapZis->total_zf_amount * 0.7 * 0.875,
            'alokasi_pendis_zf_rice' => $rekapZis->total_zf_rice * 0.7 * 0.875,
            'alokasi_pendis_zm' => $rekapZis->total_zm_amount * 0.7 * 0.875,
            'alokasi_pendis_ifs' => $rekapZis->total_ifs_amount * 0.7 * 0.8,
            'hak_op_zf_amount' => $rekapZis->total_zf_amount * 0.3 * 0.05,
            'hak_op_zf_rice' => $rekapZis->total_zf_rice * 0.3 * 0.05,
        ];
    }

    /**
     * Get rekap alokasi by ID
     *
     * @param int $id
     * @return RekapAlokasi|null
     */
    public function getRekapAlokasiById(int $id): ?RekapAlokasi
    {
        return RekapAlokasi::with('unit')->find($id);
    }

    /**
     * Get rekap alokasi by periode
     *
     * @param string $periode
     * @return Collection
     */
    public function getRekapAlokasiByPeriode(string $periode): Collection
    {
        return RekapAlokasi::with('unit')
            ->where('periode', $periode)
            ->get();
    }

    /**
     * Get rekap alokasi by unit ID
     *
     * @param int $unitId
     * @return Collection
     */
    public function getRekapAlokasiByUnitId(int $unitId): Collection
    {
        return RekapAlokasi::with('unit')
            ->where('unit_id', $unitId)
            ->get();
    }

    /**
     * Delete rekap alokasi
     *
     * @param int $id
     * @return bool
     */
    public function deleteRekapAlokasi(int $id): bool
    {
        try {
            $rekapAlokasi = RekapAlokasi::find($id);

            if (!$rekapAlokasi) {
                return false;
            }

            return $rekapAlokasi->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting rekapitulasi alokasi: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process allocations for all units in a period
     *
     * @param string $periode
     * @param string $periodeDate
     * @return Collection
     */
    public function processAllocationsByPeriode(string $periode, string $periodeDate): Collection
    {
        try {
            $rekapZisCollection = RekapZis::where('periode', $periode)
                ->where('periode_date', $periodeDate)
                ->get();

            $results = collect();

            foreach ($rekapZisCollection as $rekapZis) {
                $allocationData = $this->calculateAllocationData($rekapZis);

                $rekapAlokasi = RekapAlokasi::updateOrCreate(
                    [
                        'unit_id' => $rekapZis->unit_id,
                        'periode' => $periode,
                        'periode_date' => $periodeDate,
                    ],
                    $allocationData
                );

                $results->push($rekapAlokasi);
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('Error processing allocations by periode: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate total allocation summary
     *
     * @param string $periode
     * @param string $periodeDate
     * @return array
     */
    public function calculateAllocationSummary(string $periode, string $periodeDate): array
    {
        try {
            $records = RekapAlokasi::where('periode', $periode)
                ->where('periode_date', $periodeDate)
                ->get();

            $summary = [
                'total_setor_zf_amount' => $records->sum('total_setor_zf_amount') ?? 0,
                'total_setor_zf_rice' => $records->sum('total_setor_zf_rice') ?? 0,
                'total_setor_zm' => $records->sum('total_setor_zm') ?? 0,
                'total_setor_ifs' => $records->sum('total_setor_ifs') ?? 0,
                'total_kelola_zf_amount' => $records->sum('total_kelola_zf_amount') ?? 0,
                'total_kelola_zf_rice' => $records->sum('total_kelola_zf_rice') ?? 0,
                'total_kelola_zm' => $records->sum('total_kelola_zm') ?? 0,
                'total_kelola_ifs' => $records->sum('total_kelola_ifs') ?? 0,
                'hak_amil_zf_amount' => $records->sum('hak_amil_zf_amount') ?? 0,
                'hak_amil_zf_rice' => $records->sum('hak_amil_zf_rice') ?? 0,
                'hak_amil_zm' => $records->sum('hak_amil_zm') ?? 0,
                'hak_amil_ifs' => $records->sum('hak_amil_ifs') ?? 0,
                'alokasi_pendis_zf_amount' => $records->sum('alokasi_pendis_zf_amount') ?? 0,
                'alokasi_pendis_zf_rice' => $records->sum('alokasi_pendis_zf_rice') ?? 0,
                'alokasi_pendis_zm' => $records->sum('alokasi_pendis_zm') ?? 0,
                'alokasi_pendis_ifs' => $records->sum('alokasi_pendis_ifs') ?? 0,
                'hak_op_zf_amount' => $records->sum('hak_op_zf_amount') ?? 0,
                'hak_op_zf_rice' => $records->sum('hak_op_zf_rice') ?? 0,
            ];

            return $summary;
        } catch (\Exception $e) {
            Log::error('Error calculating allocation summary: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all rekap alokasi data
     *
     * @return Collection
     */
    public function getAllRekapAlokasi(): Collection
    {
        try {
            return RekapAlokasi::with('unit')->get();
        } catch (\Exception $e) {
            Log::error('Error getting all rekapitulasi alokasi: ' . $e->getMessage());
            throw $e;
        }
    }
}
