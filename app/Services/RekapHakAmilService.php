<?php
// File: app/Services/RekapHakAmilService.php
namespace App\Services;

use App\Models\Distribution;
use App\Models\RekapHakAmil;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RekapHakAmilService
{
    /**
     * Update rekapitulasi harian untuk tanggal dan unit tertentu
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
        $this->updateMonthlyRekapHakAmil($date->month, $date->year, $unitId);

        // Update rekapitulasi tahunan
        $this->updateYearlyRekapHakAmil($date->year, $unitId);
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
     * Ambil data HakAmiltribusian untuk rentang tanggal tertentu
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
     * Menyiapkan data untuk update rekapitulasi
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
