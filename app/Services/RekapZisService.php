<?php
// 2. Modifikasi RekapitulasiService untuk mendukung update harian
// File: app/Services/RekapitulasiService.php
namespace App\Services;

use App\Models\Zf;
use App\Models\Zm;
use App\Models\Ifs;
use App\Models\RekapZis;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RekapZisService
{
    /**
     * Update rekapitulasi harian untuk tanggal dan unit tertentu
     * 
     * @param string|Carbon $date
     * @param int $unitId
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
                ->selectRaw('COUNT(DISTINCT munfiq_name) as total_munfiq, 
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
        $this->updateMonthlyRekapitulasi($date->month, $date->year, $unitId);

        // Update rekapitulasi tahunan
        $this->updateYearlyRekapitulasi($date->year, $unitId);
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
                ->selectRaw('COUNT(DISTINCT munfiq_name) as total_munfiq, 
                            SUM(amount) as total_amount')
                ->first();

            // Simpan atau update rekapitulasi bulanan
            $rekapitulasi = RekapZis::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'period' => 'bulanan',
                    'period_date' => $startDate->format('Y-m-01'), // Tanggal 1 bulan tersebut
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
                ->selectRaw('COUNT(DISTINCT munfiq_name) as total_munfiq, 
                            SUM(amount) as total_amount')
                ->first();

            // Simpan atau update rekapitulasi tahunan
            $rekapitulasi = RekapZis::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'period' => 'tahunan',
                    'period_date' => $startDate->format('Y-01-01'), // 1 Januari tahun tersebut
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
