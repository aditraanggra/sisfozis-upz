<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Zf;
use App\Models\Zm;
use App\Models\Ifs;
use App\Models\SetorZis;
use Illuminate\Support\Facades\DB;

class SetorZisDummySeeder extends Seeder
{
    /**
     * Run the database seeder.
     * Membuat transaksi dummy setor_zis untuk unit_id 1
     * dengan aturan 30% dari setiap transaksi ZIS
     */
    public function run(): void
    {
        $unitId = 1;
        $depositPercentage = 0.30; // 30%

        // Ambil semua transaksi ZIS untuk unit_id 1
        $zfTransactions = Zf::withoutGlobalScopes()->where('unit_id', $unitId)->get();
        $zmTransactions = Zm::withoutGlobalScopes()->where('unit_id', $unitId)->get();
        $ifsTransactions = Ifs::withoutGlobalScopes()->where('unit_id', $unitId)->get();

        // Group transaksi berdasarkan tanggal
        $transactionsByDate = [];

        // Proses Zakat Fitrah
        foreach ($zfTransactions as $zf) {
            $date = $zf->trx_date->format('Y-m-d');
            if (!isset($transactionsByDate[$date])) {
                $transactionsByDate[$date] = [
                    'zf_amount' => 0,
                    'zf_rice' => 0,
                    'zm_amount' => 0,
                    'ifs_amount' => 0,
                ];
            }
            $transactionsByDate[$date]['zf_amount'] += $zf->zf_amount ?? 0;
            $transactionsByDate[$date]['zf_rice'] += $zf->zf_rice ?? 0;
        }

        // Proses Zakat Maal
        foreach ($zmTransactions as $zm) {
            $date = $zm->trx_date->format('Y-m-d');
            if (!isset($transactionsByDate[$date])) {
                $transactionsByDate[$date] = [
                    'zf_amount' => 0,
                    'zf_rice' => 0,
                    'zm_amount' => 0,
                    'ifs_amount' => 0,
                ];
            }
            $transactionsByDate[$date]['zm_amount'] += $zm->amount ?? 0;
        }

        // Proses Infak/Sedekah
        foreach ($ifsTransactions as $ifs) {
            $date = $ifs->trx_date->format('Y-m-d');
            if (!isset($transactionsByDate[$date])) {
                $transactionsByDate[$date] = [
                    'zf_amount' => 0,
                    'zf_rice' => 0,
                    'zm_amount' => 0,
                    'ifs_amount' => 0,
                ];
            }
            $transactionsByDate[$date]['ifs_amount'] += $ifs->amount ?? 0;
        }

        // Buat transaksi setor_zis
        $setorData = [];
        foreach ($transactionsByDate as $date => $amounts) {
            $zfAmountDeposit = (int) ($amounts['zf_amount'] * $depositPercentage);
            $zfRiceDeposit = $amounts['zf_rice'] * $depositPercentage;
            $zmAmountDeposit = (int) ($amounts['zm_amount'] * $depositPercentage);
            $ifsAmountDeposit = (int) ($amounts['ifs_amount'] * $depositPercentage);
            $totalDeposit = $zfAmountDeposit + $zmAmountDeposit + $ifsAmountDeposit;

            $setorData[] = [
                'unit_id' => $unitId,
                'trx_date' => $date,
                'zf_amount_deposit' => $zfAmountDeposit,
                'zf_rice_deposit' => $zfRiceDeposit,
                'zm_amount_deposit' => $zmAmountDeposit,
                'ifs_amount_deposit' => $ifsAmountDeposit,
                'total_deposit' => $totalDeposit,
                'status' => 'completed',
                'validation' => 'validated',
                'upload' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (count($setorData) > 0) {
            // Hapus data setor_zis lama untuk unit_id 1 (jika ada)
            SetorZis::withoutGlobalScopes()->where('unit_id', $unitId)->delete();

            // Insert data baru
            DB::table('setor_zis')->insert($setorData);

            $this->command->info('✓ Berhasil membuat ' . count($setorData) . ' transaksi setor_zis untuk unit_id ' . $unitId);
            $this->command->info('✓ Total deposit: Rp ' . number_format(array_sum(array_column($setorData, 'total_deposit'))));
        } else {
            $this->command->warn('⚠ Tidak ada transaksi ZIS untuk unit_id ' . $unitId);
        }
    }
}
