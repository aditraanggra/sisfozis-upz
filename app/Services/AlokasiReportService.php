<?php

namespace App\Services;

use App\Models\RekapAlokasi;
use App\Models\RekapHakAmil;
use App\Models\RekapPendis;
use App\Models\RekapSetor;

/**
 * Service for generating allocation report data.
 *
 * Calculates updated allocations by subtracting actual totals
 * (setor, pendis, hak amil) from planned allocations in rekap_alokasi.
 * All calculations use "tahunan" period records.
 */
class AlokasiReportService
{
    /**
     * Generate the allocation report for a given unit and year.
     *
     * @param  int  $unitId  The UPZ unit ID
     * @param  int  $year    The fiscal year
     * @return array Structured allocation data
     */
    public function generateReport(int $unitId, int $year): array
    {
        $alokasi = $this->getAlokasiTotals($unitId, $year);
        $setor = $this->getSetorTotals($unitId, $year);
        $pendis = $this->getPendisTotals($unitId, $year);
        $hakAmil = $this->getHakAmilTotals($unitId, $year);

        return [
            // Alokasi kelola (langsung dari rekap_alokasi)
            'alokasi_kelola_zf_uang' => $alokasi['total_kelola_zf_amount'],
            'alokasi_kelola_zf_beras' => $alokasi['total_kelola_zf_rice'],
            'alokasi_kelola_zm' => $alokasi['total_kelola_zm'],
            'alokasi_kelola_ifs' => $alokasi['total_kelola_ifs'],

            // Alokasi setor terupdate = rekap_alokasi - rekap_setor
            'alokasi_setor_zf_uang' => $alokasi['total_setor_zf_amount'] - $setor['t_setor_zf_amount'],
            'alokasi_setor_zf_beras' => $alokasi['total_setor_zf_rice'] - $setor['t_setor_zf_rice'],
            'alokasi_setor_zm' => $alokasi['total_setor_zm'] - $setor['t_setor_zm'],
            'alokasi_setor_ifs' => $alokasi['total_setor_ifs'] - $setor['t_setor_ifs'],

            // Alokasi pendistribusian terupdate = rekap_alokasi - rekap_pendis
            'alokasi_pendis_zf_uang' => $alokasi['alokasi_pendis_zf_amount'] - $pendis['t_pendis_zf_amount'],
            'alokasi_pendis_zf_beras' => $alokasi['alokasi_pendis_zf_rice'] - $pendis['t_pendis_zf_rice'],
            'alokasi_pendis_zm' => $alokasi['alokasi_pendis_zm'] - $pendis['t_pendis_zm'],
            'alokasi_pendis_ifs' => $alokasi['alokasi_pendis_ifs'] - $pendis['t_pendis_ifs'],

            // Alokasi hak amil terupdate = rekap_alokasi - rekap_hak_amil
            'alokasi_ha_zf_uang' => $alokasi['hak_amil_zf_amount'] - $hakAmil['t_pendis_ha_zf_amount'],
            'alokasi_ha_zf_beras' => $alokasi['hak_amil_zf_rice'] - $hakAmil['t_pendis_ha_zf_rice'],
            'alokasi_ha_zm' => $alokasi['hak_amil_zm'] - $hakAmil['t_pendis_ha_zm'],
            'alokasi_ha_ifs' => $alokasi['hak_amil_ifs'] - $hakAmil['t_pendis_ha_ifs'],

            // Alokasi operasional (langsung dari rekap_alokasi)
            'alokasi_op_uang' => $alokasi['hak_op_zf_amount'],
            'alokasi_op_beras' => $alokasi['hak_op_zf_rice'],
        ];
    }

    /**
     * Get allocation totals from rekap_alokasi for tahunan period.
     */
    protected function getAlokasiTotals(int $unitId, int $year): array
    {
        $result = RekapAlokasi::where('unit_id', $unitId)
            ->where('periode', 'tahunan')
            ->whereYear('periode_date', $year)
            ->selectRaw('
                COALESCE(SUM(total_kelola_zf_amount), 0) as total_kelola_zf_amount,
                COALESCE(SUM(total_kelola_zf_rice), 0) as total_kelola_zf_rice,
                COALESCE(SUM(total_kelola_zm), 0) as total_kelola_zm,
                COALESCE(SUM(total_kelola_ifs), 0) as total_kelola_ifs,
                COALESCE(SUM(total_setor_zf_amount), 0) as total_setor_zf_amount,
                COALESCE(SUM(total_setor_zf_rice), 0) as total_setor_zf_rice,
                COALESCE(SUM(total_setor_zm), 0) as total_setor_zm,
                COALESCE(SUM(total_setor_ifs), 0) as total_setor_ifs,
                COALESCE(SUM(alokasi_pendis_zf_amount), 0) as alokasi_pendis_zf_amount,
                COALESCE(SUM(alokasi_pendis_zf_rice), 0) as alokasi_pendis_zf_rice,
                COALESCE(SUM(alokasi_pendis_zm), 0) as alokasi_pendis_zm,
                COALESCE(SUM(alokasi_pendis_ifs), 0) as alokasi_pendis_ifs,
                COALESCE(SUM(hak_amil_zf_amount), 0) as hak_amil_zf_amount,
                COALESCE(SUM(hak_amil_zf_rice), 0) as hak_amil_zf_rice,
                COALESCE(SUM(hak_amil_zm), 0) as hak_amil_zm,
                COALESCE(SUM(hak_amil_ifs), 0) as hak_amil_ifs,
                COALESCE(SUM(hak_op_zf_amount), 0) as hak_op_zf_amount,
                COALESCE(SUM(hak_op_zf_rice), 0) as hak_op_zf_rice
            ')->first();

        return [
            'total_kelola_zf_amount' => (int) ($result->total_kelola_zf_amount ?? 0),
            'total_kelola_zf_rice' => (float) ($result->total_kelola_zf_rice ?? 0),
            'total_kelola_zm' => (int) ($result->total_kelola_zm ?? 0),
            'total_kelola_ifs' => (int) ($result->total_kelola_ifs ?? 0),
            'total_setor_zf_amount' => (int) ($result->total_setor_zf_amount ?? 0),
            'total_setor_zf_rice' => (float) ($result->total_setor_zf_rice ?? 0),
            'total_setor_zm' => (int) ($result->total_setor_zm ?? 0),
            'total_setor_ifs' => (int) ($result->total_setor_ifs ?? 0),
            'alokasi_pendis_zf_amount' => (int) ($result->alokasi_pendis_zf_amount ?? 0),
            'alokasi_pendis_zf_rice' => (float) ($result->alokasi_pendis_zf_rice ?? 0),
            'alokasi_pendis_zm' => (int) ($result->alokasi_pendis_zm ?? 0),
            'alokasi_pendis_ifs' => (int) ($result->alokasi_pendis_ifs ?? 0),
            'hak_amil_zf_amount' => (int) ($result->hak_amil_zf_amount ?? 0),
            'hak_amil_zf_rice' => (float) ($result->hak_amil_zf_rice ?? 0),
            'hak_amil_zm' => (int) ($result->hak_amil_zm ?? 0),
            'hak_amil_ifs' => (int) ($result->hak_amil_ifs ?? 0),
            'hak_op_zf_amount' => (int) ($result->hak_op_zf_amount ?? 0),
            'hak_op_zf_rice' => (float) ($result->hak_op_zf_rice ?? 0),
        ];
    }

    /**
     * Get setor totals from rekap_setor for tahunan period.
     */
    protected function getSetorTotals(int $unitId, int $year): array
    {
        $result = RekapSetor::where('unit_id', $unitId)
            ->where('periode', 'tahunan')
            ->whereYear('periode_date', $year)
            ->selectRaw('
                COALESCE(SUM(t_setor_zf_amount), 0) as t_setor_zf_amount,
                COALESCE(SUM(t_setor_zf_rice), 0) as t_setor_zf_rice,
                COALESCE(SUM(t_setor_zm), 0) as t_setor_zm,
                COALESCE(SUM(t_setor_ifs), 0) as t_setor_ifs
            ')->first();

        return [
            't_setor_zf_amount' => (int) ($result->t_setor_zf_amount ?? 0),
            't_setor_zf_rice' => (float) ($result->t_setor_zf_rice ?? 0),
            't_setor_zm' => (int) ($result->t_setor_zm ?? 0),
            't_setor_ifs' => (int) ($result->t_setor_ifs ?? 0),
        ];
    }

    /**
     * Get pendis totals from rekap_pendis for tahunan period.
     */
    protected function getPendisTotals(int $unitId, int $year): array
    {
        $result = RekapPendis::where('unit_id', $unitId)
            ->where('periode', 'tahunan')
            ->whereYear('periode_date', $year)
            ->selectRaw('
                COALESCE(SUM(t_pendis_zf_amount), 0) as t_pendis_zf_amount,
                COALESCE(SUM(t_pendis_zf_rice), 0) as t_pendis_zf_rice,
                COALESCE(SUM(t_pendis_zm), 0) as t_pendis_zm,
                COALESCE(SUM(t_pendis_ifs), 0) as t_pendis_ifs
            ')->first();

        return [
            't_pendis_zf_amount' => (int) ($result->t_pendis_zf_amount ?? 0),
            't_pendis_zf_rice' => (float) ($result->t_pendis_zf_rice ?? 0),
            't_pendis_zm' => (int) ($result->t_pendis_zm ?? 0),
            't_pendis_ifs' => (int) ($result->t_pendis_ifs ?? 0),
        ];
    }

    /**
     * Get hak amil totals from rekap_hak_amil for tahunan period.
     */
    protected function getHakAmilTotals(int $unitId, int $year): array
    {
        $result = RekapHakAmil::where('unit_id', $unitId)
            ->where('periode', 'tahunan')
            ->whereYear('periode_date', $year)
            ->selectRaw('
                COALESCE(SUM(t_pendis_ha_zf_amount), 0) as t_pendis_ha_zf_amount,
                COALESCE(SUM(t_pendis_ha_zf_rice), 0) as t_pendis_ha_zf_rice,
                COALESCE(SUM(t_pendis_ha_zm), 0) as t_pendis_ha_zm,
                COALESCE(SUM(t_pendis_ha_ifs), 0) as t_pendis_ha_ifs
            ')->first();

        return [
            't_pendis_ha_zf_amount' => (int) ($result->t_pendis_ha_zf_amount ?? 0),
            't_pendis_ha_zf_rice' => (float) ($result->t_pendis_ha_zf_rice ?? 0),
            't_pendis_ha_zm' => (int) ($result->t_pendis_ha_zm ?? 0),
            't_pendis_ha_ifs' => (int) ($result->t_pendis_ha_ifs ?? 0),
        ];
    }
}
