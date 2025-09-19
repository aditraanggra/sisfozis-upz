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
    /* public function updateOrCreateRekapAlokasi(int $unitId, string $period): RekapAlokasi
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
            $hakAmilIfs = $totalKelolaIfs * 0.2;

            $alokasiPendisZfAmount = $totalKelolaZfAmount * 0.875;
            $alokasiPendisZfRice = $totalKelolaZfRice * 0.875;
            $alokasiPendisZm = $totalKelolaZm * 0.875;
            $alokasiPendisIfs = $totalKelolaIfs * 0.8;

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
    } */

    public function updateOrCreateRekapAlokasi(int $unitId, string $period): RekapAlokasi
    {
        // ===== Helper BCMath (tanpa float) =====
        $bcPercent = function (string $value, string $percent, int $scale = 0): string {
            // (value * percent) / 100  dengan extra presisi lalu di-scale ke target
            $mul = bcmul($value, $percent, $scale + 4);
            return bcdiv($mul, '100', $scale);
        };
        $numStr = function ($v, int $scale = 0): string {
            if ($v === null || $v === '') {
                return $scale > 0 ? ('0.' . str_repeat('0', $scale)) : '0';
            }
            return (string) $v;
        };

        // ===== Skala pembulatan per jenis nilai =====
        $RUPIAH_SCALE = 0; // rupiah bulat (INT)
        $RICE_SCALE   = 3; // contoh: beras disimpan 3 desimal (kg)

        // ===== Persentase kebijakan =====
        $PCT_SETOR       = '30';    // 30%
        $PCT_AMIL_STD    = '12.5';  // 12.5% (ZF & ZM)
        $PCT_AMIL_IFS    = '20';    // 20% (IFS)
        $PCT_HAK_OP      = '5';     // 5% dari SETOR

        try {
            DB::beginTransaction();

            // Get rekap_zis data
            $rekapZis = RekapZis::where('unit_id', $unitId)
                ->where('period', $period)
                ->first();

            if (!$rekapZis) {
                throw new \Exception("RekapZis record not found for unit_id: {$unitId} and period: {$period}");
            }

            // ===== Tarik total sebagai string numerik (aman untuk BCMath) =====
            $totZfAmount  = $numStr($rekapZis->total_zf_amount,  $RUPIAH_SCALE);
            $totZfRice    = $numStr($rekapZis->total_zf_rice,    $RICE_SCALE);
            $totZmAmount  = $numStr($rekapZis->total_zm_amount,  $RUPIAH_SCALE);
            $totIfsAmount = $numStr($rekapZis->total_ifs_amount, $RUPIAH_SCALE);

            // ===== 1) SETOR (dibulatkan per skala), KELOLA = TOTAL − SETOR =====
            // ZF Amount
            $totalSetorZfAmount   = $bcPercent($totZfAmount, $PCT_SETOR, $RUPIAH_SCALE);
            $totalKelolaZfAmount  = bcsub($totZfAmount, $totalSetorZfAmount, $RUPIAH_SCALE);

            // ZF Rice
            $totalSetorZfRice     = $bcPercent($totZfRice, $PCT_SETOR, $RICE_SCALE);
            $totalKelolaZfRice    = bcsub($totZfRice, $totalSetorZfRice, $RICE_SCALE);

            // ZM Amount
            $totalSetorZm         = $bcPercent($totZmAmount, $PCT_SETOR, $RUPIAH_SCALE);
            $totalKelolaZm        = bcsub($totZmAmount, $totalSetorZm, $RUPIAH_SCALE);

            // IFS Amount
            $totalSetorIfs        = $bcPercent($totIfsAmount, $PCT_SETOR, $RUPIAH_SCALE);
            $totalKelolaIfs       = bcsub($totIfsAmount, $totalSetorIfs, $RUPIAH_SCALE);

            // ===== 2) Hak Amil dihitung dari KELOLA; Pendis = KELOLA − Amil =====
            // ZF
            $hakAmilZfAmount         = $bcPercent($totalKelolaZfAmount, $PCT_AMIL_STD, $RUPIAH_SCALE);
            $alokasiPendisZfAmount   = bcsub($totalKelolaZfAmount, $hakAmilZfAmount, $RUPIAH_SCALE);

            $hakAmilZfRice           = $bcPercent($totalKelolaZfRice, $PCT_AMIL_STD, $RICE_SCALE);
            $alokasiPendisZfRice     = bcsub($totalKelolaZfRice, $hakAmilZfRice, $RICE_SCALE);

            // ZM
            $hakAmilZm               = $bcPercent($totalKelolaZm, $PCT_AMIL_STD, $RUPIAH_SCALE);
            $alokasiPendisZm         = bcsub($totalKelolaZm, $hakAmilZm, $RUPIAH_SCALE);

            // IFS
            $hakAmilIfs              = $bcPercent($totalKelolaIfs, $PCT_AMIL_IFS, $RUPIAH_SCALE);
            $alokasiPendisIfs        = bcsub($totalKelolaIfs, $hakAmilIfs, $RUPIAH_SCALE);

            // ===== 3) Hak Operasional 5% dari SETOR =====
            $hakOpZfAmount           = $bcPercent($totalSetorZfAmount, $PCT_HAK_OP, $RUPIAH_SCALE);
            $hakOpZfRice             = $bcPercent($totalSetorZfRice,   $PCT_HAK_OP, $RICE_SCALE);

            // ===== 4) Simpan (casting sesuai tipe kolom Anda) =====
            // Asumsi: kolom amount bertipe INT/NUMERIC(,0) → cast ke int.
            //         kolom rice bertipe DECIMAL(,3)       → simpan string 3 desimal (aman).
            $rekapAlokasi = RekapAlokasi::updateOrCreate(
                [
                    'unit_id' => $unitId,
                    'periode' => $period, // sesuai skema Anda
                ],
                [
                    'periode_date'               => $rekapZis->period_date,

                    'total_setor_zf_amount'      => (int) $totalSetorZfAmount,
                    'total_setor_zf_rice'        => $totalSetorZfRice,     // "12.345"
                    'total_setor_zm'             => (int) $totalSetorZm,
                    'total_setor_ifs'            => (int) $totalSetorIfs,

                    'total_kelola_zf_amount'     => (int) $totalKelolaZfAmount,
                    'total_kelola_zf_rice'       => $totalKelolaZfRice,
                    'total_kelola_zm'            => (int) $totalKelolaZm,
                    'total_kelola_ifs'           => (int) $totalKelolaIfs,

                    'hak_amil_zf_amount'         => (int) $hakAmilZfAmount,
                    'hak_amil_zf_rice'           => $hakAmilZfRice,
                    'hak_amil_zm'                => (int) $hakAmilZm,
                    'hak_amil_ifs'               => (int) $hakAmilIfs,

                    'alokasi_pendis_zf_amount'   => (int) $alokasiPendisZfAmount,
                    'alokasi_pendis_zf_rice'     => $alokasiPendisZfRice,
                    'alokasi_pendis_zm'          => (int) $alokasiPendisZm,
                    'alokasi_pendis_ifs'         => (int) $alokasiPendisIfs,

                    'hak_op_zf_amount'           => (int) $hakOpZfAmount,
                    'hak_op_zf_rice'             => $hakOpZfRice,
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
