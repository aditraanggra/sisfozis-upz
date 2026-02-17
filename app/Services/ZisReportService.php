<?php

namespace App\Services;

use App\Models\RekapHakAmil;
use App\Models\RekapPendis;
use App\Models\RekapSetor;
use App\Models\RekapZis;
use App\Models\SetorZis;
use App\Models\UnitZis;
use Illuminate\Support\Facades\Storage;

/**
 * Service for generating a consolidated ZIS report.
 *
 * Aggregates data from rekap_zis, rekap_pendis, rekap_hak_amil,
 * rekap_setor, setor_zis, and unit_zis into a single response
 * suitable for PDF report generation.
 */
class ZisReportService
{
    /**
     * Generate the consolidated ZIS report for a given unit.
     *
     * @param  int  $unitId  The UPZ unit ID (required)
     * @param  array  $filters  Optional filters: periode, from_date, to_date
     * @return array Structured report data
     */
    public function generateReport(int $unitId, array $filters = []): array
    {
        // Fetch all aggregated sections in parallel-safe manner
        $zisData = $this->getZisSummary($unitId, $filters);
        $pendisData = $this->getPendisSummary($unitId, $filters);
        $hakAmilData = $this->getHakAmilSummary($unitId, $filters);
        $setorData = $this->getSetorSummary($unitId, $filters);
        $buktiSetor = $this->getBuktiSetor($unitId, $filters);
        $officials = $this->getUnitOfficials($unitId);

        return array_merge(
            $zisData,
            $pendisData,
            $hakAmilData,
            $setorData,
            ['bukti_setor' => $buktiSetor],
            $officials
        );
    }

    /**
     * Aggregate ZIS collection totals from rekap_zis.
     *
     * Sums: total_zf_amount, total_zf_rice, total_zf_muzakki,
     *       total_zm_amount, total_zm_muzakki,
     *       total_ifs_amount, total_ifs_munfiq
     */
    protected function getZisSummary(int $unitId, array $filters): array
    {
        $query = RekapZis::where('unit_id', $unitId);
        $this->applyRekapFilters($query, $filters, 'period', 'period_date');

        // Use a single selectRaw query to avoid N+1 sum calls
        $result = $query->selectRaw('
            COALESCE(SUM(total_zf_amount), 0) as total_zf_amount,
            COALESCE(SUM(total_zf_rice), 0)   as total_zf_rice,
            COALESCE(SUM(total_zf_muzakki), 0) as total_zf_muzakki,
            COALESCE(SUM(total_zm_amount), 0) as total_zm_amount,
            COALESCE(SUM(total_zm_muzakki), 0) as total_zm_muzakki,
            COALESCE(SUM(total_ifs_amount), 0) as total_ifs_amount,
            COALESCE(SUM(total_ifs_munfiq), 0) as total_ifs_munfiq
        ')->first();

        return [
            'total_zf_amount' => (int) ($result->total_zf_amount ?? 0),
            'total_zf_rice' => (float) ($result->total_zf_rice ?? 0),
            'total_zf_muzakki' => (int) ($result->total_zf_muzakki ?? 0),
            'total_zm_amount' => (int) ($result->total_zm_amount ?? 0),
            'total_zm_muzakki' => (int) ($result->total_zm_muzakki ?? 0),
            'total_ifs_amount' => (int) ($result->total_ifs_amount ?? 0),
            'total_ifs_munfiq' => (int) ($result->total_ifs_munfiq ?? 0),
        ];
    }

    /**
     * Aggregate distribution totals from rekap_pendis (excluding amil rights).
     *
     * Sums all distribution fields (zf_amount + zf_rice + zm + ifs)
     * to produce a single total_pendis value.
     */
    protected function getPendisSummary(int $unitId, array $filters): array
    {
        $query = RekapPendis::where('unit_id', $unitId);
        $this->applyRekapFilters($query, $filters, 'periode', 'periode_date');

        $result = $query->selectRaw('
            COALESCE(SUM(t_pendis_zf_amount), 0) as total_pendis_zf_amount,
            COALESCE(SUM(t_pendis_zf_rice), 0) as total_pendis_zf_rice,
            COALESCE(SUM(t_pendis_zm), 0) as total_pendis_zm,
            COALESCE(SUM(t_pendis_ifs), 0) as total_pendis_ifs,
            COALESCE(SUM(t_pm), 0) as total_pm
        ')->first();

        return [
            'total_pendis_zf_amount' => (int) ($result->total_pendis_zf_amount ?? 0),
            'total_pendis_zf_rice' => (float) ($result->total_pendis_zf_rice ?? 0),
            'total_pendis_zm' => (int) ($result->total_pendis_zm ?? 0),
            'total_pendis_ifs' => (int) ($result->total_pendis_ifs ?? 0),
            'total_pendis_amount' => (int) ($result->total_pendis_zf_amount ?? 0) + (int) ($result->total_pendis_zm ?? 0) + (int) ($result->total_pendis_ifs ?? 0),
            'total_pendis_rice' => (float) ($result->total_pendis_zf_rice ?? 0),
            'total_pm' => (int) ($result->total_pm ?? 0),
        ];
    }

    /**
     * Aggregate absorbed amil rights from rekap_hak_amil.
     *
     * Sums all hak amil fields to produce total_hak_amil.
     */
    protected function getHakAmilSummary(int $unitId, array $filters): array
    {
        $query = RekapHakAmil::where('unit_id', $unitId);
        $this->applyRekapFilters($query, $filters, 'periode', 'periode_date');

        $result = $query->selectRaw('
            COALESCE(SUM(t_pendis_ha_zf_amount), 0)
            + COALESCE(SUM(t_pendis_ha_zm), 0)
            + COALESCE(SUM(t_pendis_ha_ifs), 0) as total_hak_amil
        ')->first();

        return [
            'total_hak_amil' => (int) ($result->total_hak_amil ?? 0),
        ];
    }

    /**
     * Aggregate deposit/setor totals from rekap_setor.
     */
    protected function getSetorSummary(int $unitId, array $filters): array
    {
        $query = RekapSetor::where('unit_id', $unitId);
        $this->applyRekapFilters($query, $filters, 'periode', 'periode_date');

        $result = $query->selectRaw('
            COALESCE(SUM(t_setor_zf_amount), 0) as total_setor_zf_amount,
            COALESCE(SUM(t_setor_zf_rice), 0)   as total_setor_zf_rice,
            COALESCE(SUM(t_setor_zm), 0)         as total_setor_zm,
            COALESCE(SUM(t_setor_ifs), 0)        as total_setor_ifs
        ')->first();

        return [
            'total_setor_zf_amount' => (int) ($result->total_setor_zf_amount ?? 0),
            'total_setor_zf_rice' => (float) ($result->total_setor_zf_rice ?? 0),
            'total_setor_zm' => (int) ($result->total_setor_zm ?? 0),
            'total_setor_ifs' => (int) ($result->total_setor_ifs ?? 0),
        ];
    }

    /**
     * Get the bukti setor (proof of deposit) image URL.
     *
     * Retrieves the latest SetorZis record's upload field for the unit,
     * optionally scoped by date filters.
     *
     * @return string|null Full URL to the image, or null if none exists
     */
    protected function getBuktiSetor(int $unitId, array $filters): ?string
    {
        $query = SetorZis::withoutGlobalScopes()
            ->where('unit_id', $unitId)
            ->whereNotNull('upload')
            ->where('upload', '!=', '');

        // Apply date filters on setor_zis.trx_date
        if (! empty($filters['from_date']) && ! empty($filters['to_date'])) {
            $query->whereBetween('trx_date', [$filters['from_date'], $filters['to_date']]);
        }

        $setor = $query->latest('trx_date')->first();

        if (! $setor || ! $setor->upload) {
            return null;
        }

        // Return the full URL for the uploaded image
        return Storage::url($setor->upload);
    }

    /**
     * Get unit officials (ketua, sekretaris, bendahara) from UnitZis.
     */
    protected function getUnitOfficials(int $unitId): array
    {
        $unit = UnitZis::select('unit_leader', 'unit_assistant', 'unit_finance')
            ->find($unitId);

        return [
            'ketua' => $unit->unit_leader ?? null,
            'sekretaris' => $unit->unit_assistant ?? null,
            'bendahara' => $unit->unit_finance ?? null,
        ];
    }

    /**
     * Apply common period and date range filters to a rekap query.
     *
     * Supports filters: periode, year, from_date, to_date.
     * Uses the correct column names for the target table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $periodeCol  Column name for period type
     * @param  string  $periodeDateCol  Column name for period date
     */
    protected function applyRekapFilters($query, array $filters, string $periodeCol, string $periodeDateCol): void
    {
        if (! empty($filters['periode'])) {
            $query->where($periodeCol, $filters['periode']);
        }

        if (! empty($filters['year'])) {
            $query->whereYear($periodeDateCol, $filters['year']);
        }

        if (! empty($filters['from_date']) && ! empty($filters['to_date'])) {
            $query->whereBetween($periodeDateCol, [$filters['from_date'], $filters['to_date']]);
        }
    }
}
