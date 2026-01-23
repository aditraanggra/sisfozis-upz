<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Zf;
use App\Models\Zm;
use App\Models\Ifs;
use App\Models\SetorZis;
use App\Models\AllocationConfig;
use App\Services\AllocationConfigService;
use Illuminate\Support\Facades\DB;

class SetorZisDummySeeder extends Seeder
{
    /**
     * Create a new seeder instance.
     */
    public function __construct(
        protected AllocationConfigService $allocationConfigService
    ) {}

    /**
     * Run the database seeder.
     * Membuat transaksi dummy setor_zis untuk unit_id 1
     * dengan persentase setor dari AllocationConfigService berdasarkan tanggal transaksi
     */
    public function run(): void
    {
        $unitId = 1;

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
            // Get allocation percentages for each ZIS type based on transaction date
            $zfAlloc = $this->allocationConfigService->getAllocation(AllocationConfig::TYPE_ZF, $date);
            $zmAlloc = $this->allocationConfigService->getAllocation(AllocationConfig::TYPE_ZM, $date);
            $ifsAlloc = $this->allocationConfigService->getAllocation(AllocationConfig::TYPE_IFS, $date);

            // Calculate deposit amounts using dynamic percentages
            // Setor percentage is stored as a whole number (e.g., 30 for 30%), so divide by 100
            $zfSetorPercentage = floatval($zfAlloc['setor']) / 100;
            $zmSetorPercentage = floatval($zmAlloc['setor']) / 100;
            $ifsSetorPercentage = floatval($ifsAlloc['setor']) / 100;

            $zfAmountDeposit = (int) ($amounts['zf_amount'] * $zfSetorPercentage);
            $zfRiceDeposit = $amounts['zf_rice'] * $zfSetorPercentage;
            $zmAmountDeposit = (int) ($amounts['zm_amount'] * $zmSetorPercentage);
            $ifsAmountDeposit = (int) ($amounts['ifs_amount'] * $ifsSetorPercentage);
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
            $this->command->info('✓ Persentase setor diambil dari AllocationConfigService berdasarkan tanggal transaksi');
        } else {
            $this->command->warn('⚠ Tidak ada transaksi ZIS untuk unit_id ' . $unitId);
        }
    }
}
