<?php

namespace App\Services;

use App\Models\RekapAlokasi;
use App\Models\RekapZis;
use Illuminate\Support\Collection;

class RekapAlokasiService
{
    /**
     * Process allocation calculation based on RekapZis data
     *
     * @param RekapZis $rekapZis
     * @return RekapAlokasi
     */
    public function processAllocation(RekapZis $rekapZis): RekapAlokasi
    {
        // Check if allocation record exists for this unit and periode
        $rekapAlokasi = RekapAlokasi::where('unit_id', $rekapZis->unit_id)
            ->where('periode', $rekapZis->period)
            ->first();

        // If not exists, create new one
        if (!$rekapAlokasi) {
            $rekapAlokasi = new RekapAlokasi();
            $rekapAlokasi->unit_id = $rekapZis->unit_id;
            $rekapAlokasi->periode = $rekapZis->period;
            $rekapAlokasi->periode_date = $rekapZis->period_date;
        }

        // Calculate values based on formulas
        $rekapAlokasi->total_setor_zf_amount = $rekapZis->total_zf_amount * 0.3;
        $rekapAlokasi->total_setor_zf_rice = $rekapZis->total_zf_rice * 0.3;
        $rekapAlokasi->total_setor_zm = $rekapZis->total_zm_amount * 0.3;
        $rekapAlokasi->total_setor_ifs = $rekapZis->total_ifs_amount * 0.3;

        $rekapAlokasi->total_kelola_zf_amount = $rekapZis->total_zf_amount * 0.7;
        $rekapAlokasi->total_kelola_zf_rice = $rekapZis->total_zf_rice * 0.7;
        $rekapAlokasi->total_kelola_zm = $rekapZis->total_zm_amount * 0.7;
        $rekapAlokasi->total_kelola_ifs = $rekapZis->total_ifs_amount * 0.7;

        $rekapAlokasi->hak_amil_zf_amount = $rekapAlokasi->total_kelola_zf_amount * 0.125;
        $rekapAlokasi->hak_amil_zf_rice = $rekapAlokasi->total_kelola_zf_rice * 0.125;
        $rekapAlokasi->hak_amil_zm = $rekapAlokasi->total_kelola_zm * 0.125;
        $rekapAlokasi->hak_amil_ifs = $rekapAlokasi->total_kelola_ifs * 0.2;

        $rekapAlokasi->alokasi_pendis_zf_amount = $rekapAlokasi->total_kelola_zf_amount * 0.875;
        $rekapAlokasi->alokasi_pendis_zf_rice = $rekapAlokasi->total_kelola_zf_rice * 0.875;
        $rekapAlokasi->alokasi_pendis_zm = $rekapAlokasi->total_kelola_zm * 0.875;
        $rekapAlokasi->alokasi_pendis_ifs = $rekapAlokasi->total_kelola_ifs * 0.8;

        $rekapAlokasi->hak_op_zf_amount = $rekapAlokasi->total_setor_zf_amount * 0.05;
        $rekapAlokasi->hak_op_zf_rice = $rekapAlokasi->total_setor_zf_rice * 0.05;

        $rekapAlokasi->save();

        return $rekapAlokasi;
    }

    /**
     * Process allocations for all units in a period
     *
     * @param string $periode
     * @return Collection
     */
    public function processAllocationsByPeriode(string $periode): Collection
    {
        $rekapZisCollection = RekapZis::where('periode', $periode)->get();
        $results = collect();

        foreach ($rekapZisCollection as $rekapZis) {
            $results->push($this->processAllocation($rekapZis));
        }

        return $results;
    }

    /**
     * Get all rekap alokasi data
     *
     * @return Collection
     */
    public function getAllRekapAlokasi(): Collection
    {
        return RekapAlokasi::with('unit')->get();
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
        $rekapAlokasi = RekapAlokasi::find($id);

        if (!$rekapAlokasi) {
            return false;
        }

        return $rekapAlokasi->delete();
    }

    /**
     * Calculate total allocation summary
     *
     * @param string $periode
     * @return array
     */
    public function calculateAllocationSummary(string $periode): array
    {
        $records = RekapAlokasi::where('periode', $periode)->get();

        $summary = [
            'total_setor_zf_amount' => $records->sum('total_setor_zf_amount'),
            'total_setor_zf_rice' => $records->sum('total_setor_zf_rice'),
            'total_setor_zm' => $records->sum('total_setor_zm'),
            'total_setor_ifs' => $records->sum('total_setor_ifs'),
            'total_kelola_zf_amount' => $records->sum('total_kelola_zf_amount'),
            'total_kelola_zf_rice' => $records->sum('total_kelola_zf_rice'),
            'total_kelola_zm' => $records->sum('total_kelola_zm'),
            'total_kelola_ifs' => $records->sum('total_kelola_ifs'),
            'hak_amil_zf_amount' => $records->sum('hak_amil_zf_amount'),
            'hak_amil_zf_rice' => $records->sum('hak_amil_zf_rice'),
            'hak_amil_zm' => $records->sum('hak_amil_zm'),
            'hak_amil_ifs' => $records->sum('hak_amil_ifs'),
            'alokasi_pendis_zf_amount' => $records->sum('alokasi_pendis_zf_amount'),
            'alokasi_pendis_zf_rice' => $records->sum('alokasi_pendis_zf_rice'),
            'alokasi_pendis_zm' => $records->sum('alokasi_pendis_zm'),
            'alokasi_pendis_ifs' => $records->sum('alokasi_pendis_ifs'),
            'hak_op_zf_amount' => $records->sum('hak_op_zf_amount'),
            'hak_op_zf_rice' => $records->sum('hak_op_zf_rice'),
        ];

        return $summary;
    }
}
