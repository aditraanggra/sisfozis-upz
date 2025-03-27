<?php
// File: app/Services/RekapPendisService.php
namespace App\Services;

use App\Models\Distribution;
use App\Models\RekapPendis;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RekapPendisService
{
    /**
     * Update rekapitulasi harian untuk tanggal dan unit tertentu
     * 
     * @param string|Carbon $date
     * @param int $unitId
     * @return RekapPendis
     */
    public function updateDailyRekapPendis($date, $unitId)
    {
        try {
            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();

            // Data Pendis
            $pendisData = $this->getPendisData($startDate, $endDate, $unitId);

            // Simpan atau update rekapitulasi
            $rekapitulasi = RekapPendis::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => 'harian',
                    'periode_date' => $startDate->format('Y-m-d'),
                ],
                $this->prepareRekapitulasiData($pendisData)
            );

            // Update juga rekapitulasi mingguan, bulanan, dan tahunan jika diperlukan
            $this->updatePeriodicRekapitulasi($startDate, $unitId);

            return $rekapitulasi;
        } catch (\Exception $e) {
            Log::error('Error updating rekapitulasi: ' . $e->getMessage());
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
        $this->updateMonthlyRekapPendis($date->month, $date->year, $unitId);

        // Update rekapitulasi tahunan
        $this->updateYearlyRekapPendis($date->year, $unitId);
    }

    /**
     * Update rekapitulasi bulanan
     * 
     * @param int $month
     * @param int $year
     * @param int $unitId
     * @return RekapPendis
     */
    public function updateMonthlyRekapPendis($month, $year, $unitId)
    {
        try {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();

            // Data Pendis
            $pendisData = $this->getPendisData($startDate, $endDate, $unitId);

            // Simpan atau update rekapitulasi
            $rekapitulasi = RekapPendis::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => 'bulanan',
                    'periode_date' => $startDate->format('Y-m-01'),
                ],
                $this->prepareRekapitulasiData($pendisData)
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
     * @return RekapPendis
     */
    public function updateYearlyRekapPendis($year, $unitId)
    {
        try {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();

            // Data Pendis
            $pendisData = $this->getPendisData($startDate, $endDate, $unitId);

            // Simpan atau update rekapitulasi
            $rekapitulasi = RekapPendis::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => 'tahunan',
                    'periode_date' => $startDate->format('Y-01-01'),
                ],
                $this->prepareRekapitulasiData($pendisData)
            );

            return $rekapitulasi;
        } catch (\Exception $e) {
            Log::error('Error updating yearly rekapitulasi: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ambil data pendistribusian untuk rentang tanggal tertentu
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $unitId
     * @return object
     */
    protected function getPendisData(Carbon $startDate, Carbon $endDate, $unitId)
    {
        return Distribution::where('unit_id', $unitId)
            ->whereBetween('trx_date', [$startDate, $endDate])
            ->selectRaw("
                SUM(CASE WHEN fund_type='zakat fitrah' AND asnaf <> 'amil' AND total_amount > 0 THEN total_amount ELSE 0 END) as total_zf_amount,
                SUM(CASE WHEN fund_type='zakat fitrah' AND asnaf <> 'amil' AND total_rice > 0 THEN total_rice ELSE 0 END) as total_zf_rice, 
                SUM(CASE WHEN fund_type='zakat mal' AND asnaf <> 'amil' THEN total_amount ELSE 0 END) as total_zm, 
                SUM(CASE WHEN fund_type='infak' AND asnaf <> 'amil' THEN total_amount ELSE 0 END) as total_ifs, 
                SUM(CASE WHEN asnaf='fakir' AND total_amount > 0 THEN total_amount ELSE 0 END) as total_fakir_amount,
                SUM(CASE WHEN asnaf='miskin' AND total_amount > 0 THEN total_amount ELSE 0 END) as total_miskin_amount,
                SUM(CASE WHEN asnaf='fisabililah' AND total_amount > 0 THEN total_amount ELSE 0 END) as total_fisabililah_amount,
                SUM(CASE WHEN asnaf='fakir' AND total_rice > 0 THEN total_rice ELSE 0 END) as total_fakir_rice,
                SUM(CASE WHEN asnaf='miskin' AND total_rice > 0 THEN total_rice ELSE 0 END) as total_miskin_rice,
                SUM(CASE WHEN asnaf='fisabililah' AND total_rice > 0 THEN total_rice ELSE 0 END) as total_fisabililah_rice,
                SUM(CASE WHEN program='kemanusiaan' AND total_amount > 0 THEN total_amount ELSE 0 END) as total_kemanusiaan_amount,
                SUM(CASE WHEN program='dakwah' AND total_amount > 0 THEN total_amount ELSE 0 END) as total_dakwah_amount,
                SUM(CASE WHEN program='kemanusiaan' AND total_rice > 0 THEN total_rice ELSE 0 END) as total_kemanusiaan_rice,
                SUM(CASE WHEN program='dakwah' AND total_rice > 0 THEN total_rice ELSE 0 END) as total_dakwah_rice,
                COUNT(DISTINCT beneficiary) as total_pm
            ")
            ->first();
    }

    /**
     * Menyiapkan data untuk update rekapitulasi
     * 
     * @param object $pendisData
     * @return array
     */
    protected function prepareRekapitulasiData($pendisData)
    {
        return [
            't_pendis_zf_amount' => $pendisData->total_zf_amount ?? 0,
            't_pendis_zf_rice' => $pendisData->total_zf_rice ?? 0,
            't_pendis_zm' => $pendisData->total_zm ?? 0,
            't_pendis_ifs' => $pendisData->total_ifs ?? 0,
            't_pendis_fakir_amount' => $pendisData->total_fakir_amount ?? 0,
            't_pendis_miskin_amount' => $pendisData->total_miskin_amount ?? 0,
            't_pendis_fisabilillah_amount' => $pendisData->total_fisabililah_amount ?? 0,
            't_pendis_fakir_rice' => $pendisData->total_fakir_rice ?? 0,
            't_pendis_miskin_rice' => $pendisData->total_miskin_rice ?? 0,
            't_pendis_fisabilillah_rice' => $pendisData->total_fisabililah_rice ?? 0,
            't_pendis_kemanusiaan_amount' => $pendisData->total_kemanusiaan_amount ?? 0,
            't_pendis_dakwah_amount' => $pendisData->total_dakwah_amount ?? 0,
            't_pendis_kemanusiaan_rice' => $pendisData->total_kemanusiaan_rice ?? 0,
            't_pendis_dakwah_rice' => $pendisData->total_dakwah_rice ?? 0,
            't_pm' => $pendisData->total_pm ?? 0,
        ];
    }
}
