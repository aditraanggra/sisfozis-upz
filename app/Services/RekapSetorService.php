<?php

namespace App\Services;

use App\Models\SetorZis;
use App\Models\RekapSetor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RekapSetorService
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

            // Data Setor
            $setorData = SetorZis::where('unit_id', $unitId)
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->selectRaw('SUM(zf_amount_deposit) as zf_amount, 
                             SUM(zf_rice_deposit) as zf_rice,
                             SUM(zm_amount_deposit) as zm_amount,
                             SUM(ifs_amount_deposit) as ifs_amount
                             ')
                ->first();

            // Simpan atau update rekapitulasi
            $rekapitulasi = RekapSetor::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => 'harian',
                    'periode_date' => $startDate->format('Y-m-d'),
                ],
                [
                    't_setor_zf_amount' => $setorData->zf_amount ?? 0,
                    't_setor_zf_rice' => $setorData->zf_rice ?? 0,
                    't_setor_zm' => $setorData->zm_amount ?? 0,
                    't_setor_ifs' => $setorData->ifs_amount ?? 0,
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
        $this->updateMonthlyRekapSetor($date->month, $date->year, $unitId);

        // Update rekapitulasi tahunan
        $this->updateYearlyRekapSetor($date->year, $unitId);
    }

    public function updateMonthlyRekapSetor($month, $year, $unitId)
    {
        try {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();

            // Data Setor
            $setorData = SetorZis::where('unit_id', $unitId)
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->selectRaw('SUM(zf_amount_deposit) as zf_amount, 
                             SUM(zf_rice_deposit) as zf_rice,
                             SUM(zm_amount_deposit) as zm_amount,
                             SUM(ifs_amount_deposit) as ifs_amount
                             ')
                ->first();

            // Simpan atau update rekapitulasi
            $rekapitulasi = RekapSetor::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => 'bulanan',
                    'periode_date' => $startDate->format('Y-m-d'),
                ],
                [
                    't_setor_zf_amount' => $setorData->zf_amount ?? 0,
                    't_setor_zf_rice' => $setorData->zf_rice ?? 0,
                    't_setor_zm' => $setorData->zm_amount ?? 0,
                    't_setor_ifs' => $setorData->ifs_amount ?? 0,
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
    public function updateYearlyRekapSetor($year, $unitId)
    {
        try {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();

            // Data Setor
            $setorData = SetorZis::where('unit_id', $unitId)
                ->whereBetween('trx_date', [$startDate, $endDate])
                ->selectRaw('SUM(zf_amount_deposit) as zf_amount, 
                     SUM(zf_rice_deposit) as zf_rice,
                     SUM(zm_amount_deposit) as zm_amount,
                     SUM(ifs_amount_deposit) as ifs_amount
                     ')
                ->first();

            // Simpan atau update rekapitulasi
            $rekapitulasi = RekapSetor::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => 'tahunan',
                    'periode_date' => $startDate->format('Y-m-d'),
                ],
                [
                    't_setor_zf_amount' => $setorData->zf_amount ?? 0,
                    't_setor_zf_rice' => $setorData->zf_rice ?? 0,
                    't_setor_zm' => $setorData->zm_amount ?? 0,
                    't_setor_ifs' => $setorData->ifs_amount ?? 0,
                ]
            );

            return $rekapitulasi;
        } catch (\Exception $e) {
            Log::error('Error updating yearly rekapitulasi: ' . $e->getMessage());
            throw $e;
        }
    }
}
