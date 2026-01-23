<?php

use App\Models\AllocationConfig;
use App\Services\AllocationConfigService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;

uses(DatabaseTransactions::class);

beforeEach(function () {
    // Clear cache before each test
    Cache::forget('allocation_configs');

    // Clean up existing allocation configs to avoid unique constraint violations
    AllocationConfig::query()->delete();
});

describe('getAllocation', function () {
    it('returns correct percentages for existing config', function () {
        // Create a config for ZF 2025
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        $service = new AllocationConfigService();
        $allocation = $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));

        expect($allocation['setor'])->toBe('30.00');
        expect($allocation['kelola'])->toBe('70.00');
        expect($allocation['amil'])->toBe('12.50');
        expect($allocation['penyaluran'])->toBe('87.50');
    });

    it('returns correct percentages when using string date', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZM,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        $service = new AllocationConfigService();
        $allocation = $service->getAllocation(AllocationConfig::TYPE_ZM, '2025-06-15');

        expect($allocation['setor'])->toBe('30.00');
        expect($allocation['kelola'])->toBe('70.00');
        expect($allocation['amil'])->toBe('12.50');
        expect($allocation['penyaluran'])->toBe('87.50');
    });

    it('returns different amil percentage for IFS type', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_IFS,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '20.00',
        ]);

        $service = new AllocationConfigService();
        $allocation = $service->getAllocation(AllocationConfig::TYPE_IFS, Carbon::create(2025, 6, 15));

        expect($allocation['amil'])->toBe('20.00');
        expect($allocation['penyaluran'])->toBe('80.00');
    });
});

describe('year-based rule resolution', function () {
    it('selects highest effective_year less than or equal to transaction year', function () {
        // Create configs for multiple years
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2024,
            'setor_percentage' => '25.00',
            'kelola_percentage' => '75.00',
            'amil_percentage' => '10.00',
        ]);

        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2026,
            'setor_percentage' => '35.00',
            'kelola_percentage' => '65.00',
            'amil_percentage' => '15.00',
        ]);

        $service = new AllocationConfigService();

        // Transaction in 2025 should use 2025 rule
        $allocation2025 = $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));
        expect($allocation2025['setor'])->toBe('30.00');

        // Clear cache between calls to ensure fresh lookup
        $service->clearCache();

        // Transaction in 2024 should use 2024 rule
        $allocation2024 = $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2024, 6, 15));
        expect($allocation2024['setor'])->toBe('25.00');

        $service->clearCache();

        // Transaction in 2026 should use 2026 rule
        $allocation2026 = $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2026, 6, 15));
        expect($allocation2026['setor'])->toBe('35.00');

        $service->clearCache();

        // Transaction in 2027 should use 2026 rule (highest <= 2027)
        $allocation2027 = $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2027, 6, 15));
        expect($allocation2027['setor'])->toBe('35.00');
    });

    it('does not use future rules for past transactions', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2026,
            'setor_percentage' => '35.00',
            'kelola_percentage' => '65.00',
            'amil_percentage' => '15.00',
        ]);

        $service = new AllocationConfigService();

        // Transaction in 2025 should NOT use 2026 rule, should fall back to defaults
        $allocation = $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));
        expect($allocation['setor'])->toBe('30'); // Default setor
    });
});

describe('fallback to defaults', function () {
    it('returns default values when no config exists', function () {
        $service = new AllocationConfigService();

        // ZF/ZM defaults
        $zfAllocation = $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));
        expect($zfAllocation['setor'])->toBe('30');
        expect($zfAllocation['kelola'])->toBe('70');
        expect($zfAllocation['amil'])->toBe('12.5');
        expect($zfAllocation['penyaluran'])->toBe('87.50');

        // IFS defaults (different amil percentage)
        $ifsAllocation = $service->getAllocation(AllocationConfig::TYPE_IFS, Carbon::create(2025, 6, 15));
        expect($ifsAllocation['setor'])->toBe('30');
        expect($ifsAllocation['kelola'])->toBe('70');
        expect($ifsAllocation['amil'])->toBe('20');
        expect($ifsAllocation['penyaluran'])->toBe('80.00');
    });

    it('returns default values when no config exists for the year', function () {
        // Only create config for 2026
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2026,
            'setor_percentage' => '35.00',
            'kelola_percentage' => '65.00',
            'amil_percentage' => '15.00',
        ]);

        $service = new AllocationConfigService();

        // Transaction in 2025 should fall back to defaults
        $allocation = $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));
        expect($allocation['setor'])->toBe('30');
        expect($allocation['kelola'])->toBe('70');
    });
});

describe('individual percentage methods', function () {
    beforeEach(function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);
    });

    it('getSetorPercentage returns correct value', function () {
        $service = new AllocationConfigService();
        $setor = $service->getSetorPercentage(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));
        expect($setor)->toBe('30.00');
    });

    it('getKelolaPercentage returns correct value', function () {
        $service = new AllocationConfigService();
        $kelola = $service->getKelolaPercentage(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));
        expect($kelola)->toBe('70.00');
    });

    it('getAmilPercentage returns correct value', function () {
        $service = new AllocationConfigService();
        $amil = $service->getAmilPercentage(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));
        expect($amil)->toBe('12.50');
    });

    it('getPenyaluranPercentage returns correct value', function () {
        $service = new AllocationConfigService();
        $penyaluran = $service->getPenyaluranPercentage(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));
        expect($penyaluran)->toBe('87.50');
    });
});

describe('cache behavior', function () {
    it('caches configs after first load', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        $service = new AllocationConfigService();

        // First call - should load from database
        $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));

        // Verify cache is populated
        expect(Cache::has('allocation_configs'))->toBeTrue();
    });

    it('uses cached data on subsequent calls', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        $service = new AllocationConfigService();

        // First call
        $allocation1 = $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));

        // Modify database directly (bypassing cache) - must update both setor and kelola to satisfy constraint
        AllocationConfig::where('zis_type', AllocationConfig::TYPE_ZF)
            ->where('effective_year', 2025)
            ->update(['setor_percentage' => '40.00', 'kelola_percentage' => '60.00']);

        // Second call should still return cached value
        $allocation2 = $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));

        expect($allocation2['setor'])->toBe('30.00'); // Still cached value
    });

    it('clearCache removes cached data', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        $service = new AllocationConfigService();

        // Load into cache
        $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));
        expect(Cache::has('allocation_configs'))->toBeTrue();

        // Clear cache
        $service->clearCache();
        expect(Cache::has('allocation_configs'))->toBeFalse();
    });

    it('reloads from database after cache clear', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        $service = new AllocationConfigService();

        // First call
        $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));

        // Modify database
        AllocationConfig::where('zis_type', AllocationConfig::TYPE_ZF)
            ->where('effective_year', 2025)
            ->update(['setor_percentage' => '40.00', 'kelola_percentage' => '60.00']);

        // Clear cache
        $service->clearCache();

        // Next call should get updated value
        $allocation = $service->getAllocation(AllocationConfig::TYPE_ZF, Carbon::create(2025, 6, 15));
        expect($allocation['setor'])->toBe('40.00');
    });
});

describe('zero kelola case with database', function () {
    it('returns zero for hak_amil when kelola is zero from config', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZM,
            'effective_year' => 2026,
            'setor_percentage' => '100.00',
            'kelola_percentage' => '0.00',
            'amil_percentage' => '12.50',
        ]);

        $service = new AllocationConfigService();
        $allocation = $service->getAllocation(AllocationConfig::TYPE_ZM, Carbon::create(2026, 6, 15));

        expect($allocation['setor'])->toBe('100.00');
        expect($allocation['kelola'])->toBe('0.00');

        // Calculate hak_amil from zero kelola
        $hakAmil = $service->calculateHakAmil('0', $allocation['amil']);
        expect($hakAmil)->toBe('0');
    });

    it('handles complete zero kelola workflow with config', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZM,
            'effective_year' => 2026,
            'setor_percentage' => '100.00',
            'kelola_percentage' => '0.00',
            'amil_percentage' => '12.50',
        ]);

        $service = new AllocationConfigService();
        $allocation = $service->getAllocation(AllocationConfig::TYPE_ZM, Carbon::create(2026, 6, 15));

        // Simulate calculation with 100000 total amount
        $totalAmount = '100000';
        $setorAmount = bcdiv(bcmul($totalAmount, $allocation['setor'], 4), '100', 2);
        $kelolaAmount = bcsub($totalAmount, $setorAmount, 2);

        expect($setorAmount)->toBe('100000.00');
        expect($kelolaAmount)->toBe('0.00');

        // Both hak_amil and penyaluran should be zero
        $hakAmil = $service->calculateHakAmil($kelolaAmount, $allocation['amil']);
        $penyaluran = $service->calculatePenyaluran($kelolaAmount, $hakAmil);

        expect($hakAmil)->toBe('0');
        expect($penyaluran)->toBe('0');
    });
});
