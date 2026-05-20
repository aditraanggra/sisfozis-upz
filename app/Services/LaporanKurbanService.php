<?php

namespace App\Services;

use App\Models\LaporanKurban;

class LaporanKurbanService
{
    /**
     * Idempotent: return existing draft, error if verified, create new otherwise.
     */
    public function createOrUpdate(int $unitId, int $userId, array $data): LaporanKurban
    {
        $existing = LaporanKurban::where('unit_id', $unitId)
            ->where('tahun_hijriah', $data['tahun_hijriah'])
            ->first();

        if ($existing && $existing->status === 'verified') {
            throw new \RuntimeException('Laporan untuk tahun ini sudah diverifikasi');
        }

        $totalEkor = $this->calculateTotalEkor($data);
        $totalShohibul = ! empty($data['shohibul_dikoreksi'])
            ? (int) $data['total_shohibul']
            : $this->calculateShohibul($data);

        $payload = array_merge($data, [
            'unit_id' => $unitId,
            'user_id' => $userId,
            'total_ekor' => $totalEkor,
            'total_shohibul' => $totalShohibul,
            'status' => 'draft',
        ]);

        if ($existing && $existing->status === 'draft') {
            $existing->update(array_merge($payload, ['status' => 'draft']));

            return $existing->fresh();
        }

        return LaporanKurban::create($payload);
    }

    /**
     * Submit: validate total_ekor > 0, set status terkirim, generate no_referensi.
     */
    public function submit(LaporanKurban $laporan): LaporanKurban
    {
        if ($laporan->status !== 'draft') {
            throw new \RuntimeException('Hanya laporan berstatus draft yang dapat dikirim');
        }

        if ($laporan->total_ekor < 1) {
            throw new \RuntimeException('Total ekor harus lebih dari 0');
        }

        $laporan->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'no_referensi' => $this->generateNoReferensi($laporan),
        ]);

        return $laporan->fresh();
    }

    /**
     * Delete: only if status = draft (soft delete).
     */
    public function destroy(LaporanKurban $laporan): void
    {
        if ($laporan->status !== 'draft') {
            throw new \RuntimeException('Hanya laporan berstatus draft yang dapat dihapus');
        }

        $laporan->delete();
    }

    public function calculateShohibul(array $data): int
    {
        return ((int) ($data['jml_sapi'] ?? 0) + (int) ($data['jml_kerbau'] ?? 0)) * 7
            + (int) ($data['jml_kambing'] ?? 0) + (int) ($data['jml_domba'] ?? 0);
    }

    public function calculateTotalEkor(array $data): int
    {
        return (int) ($data['jml_sapi'] ?? 0) + (int) ($data['jml_kerbau'] ?? 0)
            + (int) ($data['jml_kambing'] ?? 0) + (int) ($data['jml_domba'] ?? 0);
    }

    private function generateNoReferensi(LaporanKurban $laporan): string
    {
        $unit = $laporan->unit;
        $noRegister = $unit ? $unit->no_register : '0000';

        $latest = LaporanKurban::where('unit_id', $laporan->unit_id)
            ->where('tahun_hijriah', $laporan->tahun_hijriah)
            ->whereNotNull('no_referensi')
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($latest) {
            $parts = explode('-', $latest->no_referensi);
            $lastPart = end($parts);
            if (is_numeric($lastPart)) {
                $seq = (int) $lastPart + 1;
            }
        }

        return sprintf('KRB-%s-%s-%s', $laporan->tahun_hijriah, $noRegister, str_pad((string) $seq, 4, '0', STR_PAD_LEFT));
    }
}
