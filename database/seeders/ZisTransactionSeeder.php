<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Ifs;
use App\Models\UnitCategory;
use App\Models\UnitZis;
use App\Models\User;
use App\Models\Zf;
use App\Models\Zm;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ZisTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $this->createDkmUnits();
        $this->createDummyTransactions();
    }

    private function createDkmUnits(): void
    {
        $dkmCategory = UnitCategory::firstOrCreate(
            ['name' => 'DKM'],
            ['desc' => 'Dewan Kemakmuran Masjid']
        );

        $districts = District::with('villages')->get();

        if ($districts->isEmpty()) {
            $this->command->warn('Tidak ada data kecamatan.');
            return;
        }

        $this->command->info("Membuat 1 UnitZis DKM per desa di setiap kecamatan...");

        $masjidNames = [
            'Al-Ikhlas',
            'Al-Falah',
            'Al-Hidayah',
            'Al-Muhajirin',
            'Al-Anshor',
            'Nurul Iman',
            'Nurul Huda',
            'Baitul Makmur',
            'At-Taqwa',
            'Al-Muttaqin'
        ];
        $leaderNames = [
            'H. Ahmad Syafii',
            'KH. Abdul Karim',
            'Ust. Muhammad Ridwan',
            'H. Sulaiman',
            'KH. Hasan Basri',
            'Ust. Fauzi Rahman'
        ];

        $unitCount = 0;

        foreach ($districts as $district) {
            $villages = $district->villages;
            if ($villages->isEmpty()) continue;

            // Buat 1 unit DKM per desa
            foreach ($villages as $village) {
                // Cek apakah sudah ada unit DKM di desa ini
                $exists = UnitZis::where('village_id', $village->id)
                    ->where('category_id', $dkmCategory->id)->exists();
                if ($exists) continue;

                $masjidName = $masjidNames[array_rand($masjidNames)];
                $unitName = "DKM Masjid {$masjidName} {$village->name}";

                $email = 'dkm.' . $village->id . '@dummy.test';
                $user = User::firstOrCreate(['email' => $email], [
                    'name' => "Operator DKM {$village->name}",
                    'password' => Hash::make('password'),
                    'district_id' => $district->id,
                    'village_id' => $village->id,
                ]);

                UnitZis::create([
                    'user_id' => $user->id,
                    'category_id' => $dkmCategory->id,
                    'village_id' => $village->id,
                    'district_id' => $district->id,
                    'unit_name' => $unitName,
                    'no_register' => 'DKM-' . $district->id . '-' . str_pad($unitCount + 1, 4, '0', STR_PAD_LEFT),
                    'address' => "Jl. Masjid {$masjidName}, Desa {$village->name}",
                    'unit_leader' => $leaderNames[array_rand($leaderNames)],
                    'unit_assistant' => $leaderNames[array_rand($leaderNames)],
                    'unit_finance' => $leaderNames[array_rand($leaderNames)],
                    'operator_phone' => '08' . rand(1000000000, 9999999999),
                    'rice_price' => rand(14, 18) * 1000,
                    'is_verified' => true,
                    'profile_completion' => 100,
                ]);
                $unitCount++;
            }
        }
        $this->command->info("Berhasil membuat {$unitCount} UnitZis DKM baru (1 per desa).");
    }

    private function createDummyTransactions(): void
    {
        $units = UnitZis::all();

        if ($units->isEmpty()) {
            $this->command->warn('Tidak ada UnitZis.');
            return;
        }

        $this->command->info("Membuat transaksi dummy untuk {$units->count()} unit...");

        $names = [
            'Ahmad Fauzi',
            'Budi Santoso',
            'Citra Dewi',
            'Dian Pratama',
            'Eko Wijaya',
            'Fatimah Zahra',
            'Gunawan Hadi',
            'Hana Safitri',
            'Irfan Hakim',
            'Joko Susilo',
            'Kartini Sari',
            'Lukman Hakim',
            'Maya Anggraini',
            'Nur Hidayah',
            'Omar Bakri',
            'Putri Rahayu',
            'Qori Amin',
            'Rina Wati',
            'Surya Darma',
            'Taufik Rahman'
        ];

        $categoryMaal = [
            'Perdagangan',
            'Pertanian',
            'Peternakan',
            'Emas/Perak',
            'Profesi',
            'Saham',
            'Tabungan',
            'Properti'
        ];

        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now();

        foreach ($units as $unit) {
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                $zfCount = rand(0, 3);
                $zmCount = rand(0, 2);
                $ifsCount = rand(0, 3);

                for ($i = 0; $i < $zfCount; $i++) {
                    Zf::withoutGlobalScopes()->create([
                        'unit_id' => $unit->id,
                        'trx_date' => $currentDate->format('Y-m-d'),
                        'muzakki_name' => $names[array_rand($names)],
                        'zf_rice' => rand(1, 10) * 2.5,
                        'zf_amount' => rand(1, 5) * 50000,
                        'total_muzakki' => rand(1, 5),
                        'desc' => rand(0, 1) ? 'Zakat fitrah keluarga' : null,
                    ]);
                }

                for ($i = 0; $i < $zmCount; $i++) {
                    Zm::withoutGlobalScopes()->create([
                        'unit_id' => $unit->id,
                        'trx_date' => $currentDate->format('Y-m-d'),
                        'category_maal' => $categoryMaal[array_rand($categoryMaal)],
                        'muzakki_name' => $names[array_rand($names)],
                        'amount' => rand(1, 50) * 100000,
                        'desc' => rand(0, 1) ? 'Zakat maal tahunan' : null,
                    ]);
                }

                for ($i = 0; $i < $ifsCount; $i++) {
                    Ifs::withoutGlobalScopes()->create([
                        'unit_id' => $unit->id,
                        'trx_date' => $currentDate->format('Y-m-d'),
                        'munfiq_name' => $names[array_rand($names)],
                        'amount' => rand(1, 20) * 50000,
                        'desc' => rand(0, 1) ? 'Infak bulanan' : null,
                    ]);
                }

                $currentDate->addDays(rand(1, 3));
            }
        }

        $this->command->info('Transaksi dummy ZIS berhasil dibuat!');
        $this->command->info('Total Zakat Fitrah: ' . Zf::withoutGlobalScopes()->count());
        $this->command->info('Total Zakat Maal: ' . Zm::withoutGlobalScopes()->count());
        $this->command->info('Total Infak/Sedekah: ' . Ifs::withoutGlobalScopes()->count());
    }
}
