<?php

namespace Tests\Feature;

use App\Models\RekapHakAmil;
use App\Models\RekapPendis;
use App\Models\RekapSetor;
use App\Models\RekapZis;
use App\Models\SetorZis;
use App\Models\UnitZis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the consolidated ZIS report endpoint.
 *
 * Endpoint: GET /api/v1/rekap/zis-report?unit_id=X
 */
class ZisReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed a complete test scenario: unit + all rekap tables + setor record.
     *
     * Uses RekapZis::withoutEvents() to prevent the RekapZisObserver
     * from dispatching UpdateRekapAlokasi jobs during test seeding.
     *
     * @return array [user, unit]
     */
    protected function seedTestData(): array
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var UnitZis $unit */
        $unit = UnitZis::factory()->create([
            'user_id' => $user->id,
            'unit_leader' => 'Ahmad Fauzi',
            'unit_assistant' => 'Budi Santoso',
            'unit_finance' => 'Siti Aminah',
        ]);

        // RekapZis: ZF/ZM/IFS collection totals (bulanan and harian periods)
        // Wrapped in withoutEvents() to prevent RekapZisObserver from
        // dispatching UpdateRekapAlokasi jobs that require a 'harian' record
        RekapZis::withoutEvents(function () use ($unit) {
            // Bulanan records
            RekapZis::create([
                'unit_id' => $unit->id,
                'period' => 'bulanan',
                'period_date' => '2026-01-01',
                'total_zf_amount' => 10000000,
                'total_zf_rice' => 150.5,
                'total_zf_muzakki' => 80,
                'total_zm_amount' => 5000000,
                'total_zm_muzakki' => 30,
                'total_ifs_amount' => 2000000,
                'total_ifs_munfiq' => 20,
            ]);

            RekapZis::create([
                'unit_id' => $unit->id,
                'period' => 'bulanan',
                'period_date' => '2026-02-01',
                'total_zf_amount' => 5000000,
                'total_zf_rice' => 100.0,
                'total_zf_muzakki' => 40,
                'total_zm_amount' => 3000000,
                'total_zm_muzakki' => 15,
                'total_ifs_amount' => 1000000,
                'total_ifs_munfiq' => 10,
            ]);

            // Harian record for filtering test
            RekapZis::create([
                'unit_id' => $unit->id,
                'period' => 'harian',
                'period_date' => '2026-01-15',
                'total_zf_amount' => 3000000,
                'total_zf_rice' => 50.0,
                'total_zf_muzakki' => 25,
                'total_zm_amount' => 1000000,
                'total_zm_muzakki' => 10,
                'total_ifs_amount' => 500000,
                'total_ifs_munfiq' => 5,
            ]);
        });

        // RekapPendis: distribution totals
        RekapPendis::create([
            'unit_id' => $unit->id,
            'periode' => 'bulanan',
            'periode_date' => '2026-01-01',
            't_pendis_zf_amount' => 6000000,
            't_pendis_zf_rice' => 100,
            't_pendis_zm' => 3000000,
            't_pendis_ifs' => 1000000,
            't_pendis_fakir_amount' => 2000000,
            't_pendis_miskin_amount' => 2000000,
            't_pendis_fisabilillah_amount' => 2000000,
            't_pendis_fakir_rice' => 40,
            't_pendis_miskin_rice' => 30,
            't_pendis_fisabilillah_rice' => 30,
            't_pendis_kemanusiaan_amount' => 1000000,
            't_pendis_dakwah_amount' => 500000,
            't_pendis_kemanusiaan_rice' => 20,
            't_pendis_dakwah_rice' => 10,
            't_pm' => 50,
        ]);

        // RekapHakAmil: amil rights
        RekapHakAmil::create([
            'unit_id' => $unit->id,
            'periode' => 'bulanan',
            'periode_date' => '2026-01-01',
            't_pendis_ha_zf_amount' => 1500000,
            't_pendis_ha_zf_rice' => 20,
            't_pendis_ha_zm' => 600000,
            't_pendis_ha_ifs' => 500000,
            't_pm' => 10,
        ]);

        // RekapSetor: deposit totals
        RekapSetor::withoutEvents(function () use ($unit) {
            RekapSetor::create([
                'unit_id' => $unit->id,
                'periode' => 'bulanan',
                'periode_date' => '2026-01-01',
                't_setor_zf_amount' => 8000000,
                't_setor_zf_rice' => 120.0,
                't_setor_zm' => 4000000,
                't_setor_ifs' => 1500000,
            ]);
        });

        // SetorZis: deposit record with upload (bukti setor)
        SetorZis::withoutEvents(function () use ($unit) {
            SetorZis::withoutGlobalScopes()->create([
                'unit_id' => $unit->id,
                'trx_date' => '2026-01-15',
                'zf_amount_deposit' => 8000000,
                'zf_rice_deposit' => 120.0,
                'zm_amount_deposit' => 4000000,
                'ifs_amount_deposit' => 1500000,
                'total_deposit' => 13500000,
                'status' => 'approved',
                'validation' => 'valid',
                'upload' => 'uploads/bukti_setor_test.jpg',
            ]);
        });

        return [$user, $unit];
    }

    /** @test */
    public function it_returns_consolidated_report_with_all_fields(): void
    {
        [$user, $unit] = $this->seedTestData();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/rekap/zis-report?unit_id={$unit->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'total_zf_amount',
                    'total_zf_rice',
                    'total_zf_muzakki',
                    'total_zm_amount',
                    'total_zm_muzakki',
                    'total_ifs_amount',
                    'total_ifs_munfiq',
                    'total_pendis_amount',
                    'total_pendis_rice',
                    'hak_amil_zf_beras',
                    'hak_amil_zf_uang',
                    'hak_amil_zm',
                    'hak_amil_ifs',
                    'total_setor_zf_amount',
                    'total_setor_zf_rice',
                    'total_setor_zm',
                    'total_setor_ifs',
                    'bukti_setor',
                    'ketua',
                    'sekretaris',
                    'bendahara',
                ],
            ]);

        $data = $response->json('data');

        // ZIS totals (sum of two bulanan + one harian RekapZis records)
        $this->assertEquals(18000000, $data['total_zf_amount']);
        $this->assertEquals(300.5, $data['total_zf_rice']);
        $this->assertEquals(145, $data['total_zf_muzakki']);
        $this->assertEquals(9000000, $data['total_zm_amount']);
        $this->assertEquals(55, $data['total_zm_muzakki']);
        $this->assertEquals(3500000, $data['total_ifs_amount']);
        $this->assertEquals(35, $data['total_ifs_munfiq']);

        // Pendis total (zf_amount + zm + ifs = 6M + 3M + 1M)
        $this->assertEquals(10000000, $data['total_pendis_amount']);

        // Hak amil detail
        $this->assertEquals(20, $data['hak_amil_zf_beras']);
        $this->assertEquals(1500000, $data['hak_amil_zf_uang']);
        $this->assertEquals(600000, $data['hak_amil_zm']);
        $this->assertEquals(500000, $data['hak_amil_ifs']);

        // Setor totals
        $this->assertEquals(8000000, $data['total_setor_zf_amount']);
        $this->assertEquals(120.0, $data['total_setor_zf_rice']);
        $this->assertEquals(4000000, $data['total_setor_zm']);
        $this->assertEquals(1500000, $data['total_setor_ifs']);

        // Bukti setor URL
        $this->assertNotNull($data['bukti_setor']);
        $this->assertStringContainsString('bukti_setor_test.jpg', $data['bukti_setor']);

        // Officials
        $this->assertEquals('Ahmad Fauzi', $data['ketua']);
        $this->assertEquals('Budi Santoso', $data['sekretaris']);
        $this->assertEquals('Siti Aminah', $data['bendahara']);
    }

    /** @test */
    public function it_validates_unit_id_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/rekap/zis-report');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['unit_id']);
    }

    /** @test */
    public function it_returns_zeros_when_no_data_exists_for_unit(): void
    {
        $user = User::factory()->create();
        $unit = UnitZis::factory()->create([
            'user_id' => $user->id,
            'unit_leader' => 'Test Leader',
            'unit_assistant' => 'Test Assistant',
            'unit_finance' => 'Test Finance',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/rekap/zis-report?unit_id={$unit->id}");

        $response->assertOk();

        $data = $response->json('data');

        $this->assertEquals(0, $data['total_zf_amount']);
        $this->assertEquals(0, $data['total_zm_amount']);
        $this->assertEquals(0, $data['total_ifs_amount']);
        $this->assertEquals(0, $data['total_pendis_amount']);
        $this->assertEquals(0, $data['total_pendis_rice']);
        $this->assertEquals(0, $data['hak_amil_zf_beras']);
        $this->assertEquals(0, $data['hak_amil_zf_uang']);
        $this->assertEquals(0, $data['hak_amil_zm']);
        $this->assertEquals(0, $data['hak_amil_ifs']);
        $this->assertEquals(0, $data['total_setor_zf_amount']);
        $this->assertNull($data['bukti_setor']);
        $this->assertEquals('Test Leader', $data['ketua']);
    }

    /** @test */
    public function it_filters_by_periode(): void
    {
        [$user, $unit] = $this->seedTestData();

        // Test bulanan filter
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/rekap/zis-report?unit_id={$unit->id}&periode=bulanan");

        $response->assertOk();

        $data = $response->json('data');

        // Should include only bulanan records (10000000 + 5000000)
        $this->assertEquals(15000000, $data['total_zf_amount']);

        // Test harian filter
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/rekap/zis-report?unit_id={$unit->id}&periode=harian");

        $response->assertOk();

        $data = $response->json('data');

        // Should include only harian record
        $this->assertEquals(3000000, $data['total_zf_amount']);

        // Test without filter - should include all records (10000000 + 5000000 + 3000000)
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/rekap/zis-report?unit_id={$unit->id}");

        $response->assertOk();

        $data = $response->json('data');

        // Should include all records (bulanan + harian)
        $this->assertEquals(18000000, $data['total_zf_amount']);
    }
}
