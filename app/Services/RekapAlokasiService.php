<?php

namespace App\Services;

use App\Models\RekapZis;
use App\Models\RekapAlokasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RekapAlokasiService
{
    /**
     * Update or create rekap alokasi based on rekap zis data
     *
     * @param int $unitId
     * @param string $period
     * @return RekapAlokasi
     */
    public function updateOrCreateRekapAlokasi(int $unitId, string $period): RekapAlokasi
    {
        try {
            DB::beginTransaction();

            // Get rekap_zis data
            $rekapZis = RekapZis::where('unit_id', $unitId)
                ->where('period', $period)
                ->first();

            if (!$rekapZis) {
                throw new \Exception("RekapZis record not found for unit_id: {$unitId} and period: {$period}");
            }

            // Calculate allocation values based on the formulas
            $totalSetorZfAmount = $rekapZis->total_zf_amount * 0.3;
            $totalSetorZfRice = $rekapZis->total_zf_rice * 0.3;
            $totalSetorZm = $rekapZis->total_zm_amount * 0.3;
            $totalSetorIfs = $rekapZis->total_ifs_amount * 0.3;

            $totalKelolaZfAmount = $rekapZis->total_zf_amount * 0.7;
            $totalKelolaZfRice = $rekapZis->total_zf_rice * 0.7;
            $totalKelolaZm = $rekapZis->total_zm_amount * 0.7;
            $totalKelolaIfs = $rekapZis->total_ifs_amount * 0.7;

            $hakAmilZfAmount = $totalKelolaZfAmount * 0.125;
            $hakAmilZfRice = $totalKelolaZfRice * 0.125;
            $hakAmilZm = $totalKelolaZm * 0.125;
            $hakAmilIfs = $totalKelolaIfs * 0.125;

            $alokasiPendisZfAmount = $totalKelolaZfAmount * 0.875;
            $alokasiPendisZfRice = $totalKelolaZfRice * 0.875;
            $alokasiPendisZm = $totalKelolaZm * 0.875;
            $alokasiPendisIfs = $totalKelolaIfs * 0.875;

            $hakOpZfAmount = $totalSetorZfAmount * 0.05;
            $hakOpZfRice = $totalSetorZfRice * 0.05;

            // Update or create rekap_alokasi record
            $rekapAlokasi = RekapAlokasi::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => $period,
                ],
                [
                    'periode_date' => $rekapZis->period_date,
                    'total_setor_zf_amount' => $totalSetorZfAmount,
                    'total_setor_zf_rice' => $totalSetorZfRice,
                    'total_setor_zm' => $totalSetorZm,
                    'total_setor_ifs' => $totalSetorIfs,
                    'total_kelola_zf_amount' => $totalKelolaZfAmount,
                    'total_kelola_zf_rice' => $totalKelolaZfRice,
                    'total_kelola_zm' => $totalKelolaZm,
                    'total_kelola_ifs' => $totalKelolaIfs,
                    'hak_amil_zf_amount' => $hakAmilZfAmount,
                    'hak_amil_zf_rice' => $hakAmilZfRice,
                    'hak_amil_zm' => $hakAmilZm,
                    'hak_amil_ifs' => $hakAmilIfs,
                    'alokasi_pendis_zf_amount' => $alokasiPendisZfAmount,
                    'alokasi_pendis_zf_rice' => $alokasiPendisZfRice,
                    'alokasi_pendis_zm' => $alokasiPendisZm,
                    'alokasi_pendis_ifs' => $alokasiPendisIfs,
                    'hak_op_zf_amount' => $hakOpZfAmount,
                    'hak_op_zf_rice' => $hakOpZfRice,
                ]
            );

            DB::commit();
            return $rekapAlokasi;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating rekap alokasi: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update all rekap alokasi records
     *
     * @return array
     */
    public function rebuildAllRekapAlokasi(): array
    {
        $results = [];
        $rekapZisRecords = RekapZis::all();

        foreach ($rekapZisRecords as $rekapZis) {
            try {
                // Ensure both unit_id and period are not null before calling updateOrCreateRekapAlokasi
                if ($rekapZis->unit_id !== null && $rekapZis->period !== null) {
                    $rekapAlokasi = $this->updateOrCreateRekapAlokasi(
                        (int)$rekapZis->unit_id,
                        (string)$rekapZis->period
                    );

                    $results[] = [
                        'unit_id' => $rekapZis->unit_id,
                        'periode' => $rekapZis->period,
                        'status' => 'success'
                    ];
                } else {
                    // Log and record error for records with null values
                    $errorMessage = 'Missing required data: ' .
                        ($rekapZis->unit_id === null ? 'unit_id is null' : '') .
                        ($rekapZis->period === null ? 'period is null' : '');

                    Log::error("Cannot update rekap alokasi: {$errorMessage} for rekap_zis ID: {$rekapZis->id}");

                    $results[] = [
                        'id' => $rekapZis->id,
                        'unit_id' => $rekapZis->unit_id,
                        'periode' => $rekapZis->period,
                        'status' => 'error',
                        'message' => $errorMessage
                    ];
                }
            } catch (\Exception $e) {
                Log::error("Error updating rekap alokasi for rekap_zis ID: {$rekapZis->id}, Error: {$e->getMessage()}");

                $results[] = [
                    'id' => $rekapZis->id,
                    'unit_id' => $rekapZis->unit_id ?? 'unknown',
                    'periode' => $rekapZis->period ?? 'unknown',
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}
