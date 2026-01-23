<?php

/**
 * Property-Based Tests for ZIS Allocation Configuration
 *
 * Feature: zis-allocation-config
 *
 * These tests verify correctness properties across randomized inputs
 * using Pest PHP with Faker for data generation.
 *
 * Minimum iterations: 100 per property test
 */

use App\Models\AllocationConfig;
use App\Services\AllocationConfigService;
use Faker\Factory as Faker;

/**
 * Helper function to generate random percentage pairs that sum to 100
 */
function generateValidSetorKelolaPairs(int $count = 100): array
{
    $faker = Faker::create();
    $pairs = [];

    for ($i = 0; $i < $count; $i++) {
        // Generate setor between 0 and 100 with 2 decimal places
        $setor = $faker->randomFloat(2, 0, 100);
        $kelola = bcsub('100', (string) $setor, 2);
        $pairs[] = [(string) $setor, $kelola];
    }

    return $pairs;
}

/**
 * Helper function to generate random percentage pairs that do NOT sum to 100
 */
function generateInvalidSetorKelolaPairs(int $count = 100): array
{
    $faker = Faker::create();
    $pairs = [];

    for ($i = 0; $i < $count; $i++) {
        // Generate two random percentages that don't sum to 100
        $setor = $faker->randomFloat(2, 0, 100);
        $kelola = $faker->randomFloat(2, 0, 100);

        // Ensure they don't sum to exactly 100
        $sum = bcadd((string) $setor, (string) $kelola, 2);
        if (bccomp($sum, '100', 2) === 0) {
            // Adjust kelola slightly to break the sum
            $kelola = bcadd((string) $kelola, '0.01', 2);
        }

        $pairs[] = [(string) $setor, (string) $kelola];
    }

    return $pairs;
}

/**
 * Helper function to generate random percentage values
 */
function generateRandomPercentages(int $count = 100): array
{
    $faker = Faker::create();
    $percentages = [];

    for ($i = 0; $i < $count; $i++) {
        $percentages[] = [$faker->randomFloat(2, 0, 100)];
    }

    return $percentages;
}

/**
 * Helper function to generate invalid percentage values (outside 0-100)
 */
function generateInvalidPercentages(int $count = 100): array
{
    $faker = Faker::create();
    $percentages = [];

    for ($i = 0; $i < $count; $i++) {
        // Randomly choose to generate below 0 or above 100
        if ($faker->boolean()) {
            $percentages[] = [$faker->randomFloat(2, -1000, -0.01)];
        } else {
            $percentages[] = [$faker->randomFloat(2, 100.01, 1000)];
        }
    }

    return $percentages;
}

/**
 * Helper function to generate random allocation rules and transaction years
 */
function generateRulesAndYears(int $count = 100): array
{
    $faker = Faker::create();
    $testCases = [];
    $zisTypes = [AllocationConfig::TYPE_ZF, AllocationConfig::TYPE_ZM, AllocationConfig::TYPE_IFS];

    for ($i = 0; $i < $count; $i++) {
        $zisType = $faker->randomElement($zisTypes);

        // Generate 1-5 rules with different effective years
        $numRules = $faker->numberBetween(1, 5);
        $years = $faker->unique()->randomElements(range(2020, 2030), $numRules);
        sort($years);

        $rules = [];
        foreach ($years as $year) {
            $setor = $faker->randomFloat(2, 0, 100);
            $kelola = bcsub('100', (string) $setor, 2);
            $amil = $faker->randomFloat(2, 0, 100);

            $rules[] = [
                'zis_type' => $zisType,
                'effective_year' => $year,
                'setor_percentage' => (string) $setor,
                'kelola_percentage' => $kelola,
                'amil_percentage' => (string) $amil,
            ];
        }

        // Generate a transaction year
        $transactionYear = $faker->numberBetween(2018, 2035);

        $testCases[] = [$zisType, $rules, $transactionYear];

        $faker->unique(true); // Reset unique generator
    }

    return $testCases;
}

/**
 * Helper function to generate random amil percentages
 */
function generateAmilPercentages(int $count = 100): array
{
    $faker = Faker::create();
    $percentages = [];

    for ($i = 0; $i < $count; $i++) {
        $percentages[] = [$faker->randomFloat(2, 0, 100)];
    }

    return $percentages;
}

/**
 * Helper function to generate random kelola amounts and amil percentages
 */
function generateKelolaAndAmilPairs(int $count = 100): array
{
    $faker = Faker::create();
    $pairs = [];

    for ($i = 0; $i < $count; $i++) {
        // Generate kelola amount (can be 0 or positive)
        $kelola = $faker->boolean(90)
            ? $faker->randomFloat(2, 0.01, 10000000) // 90% positive
            : '0'; // 10% zero

        $amil = $faker->randomFloat(2, 0, 100);

        $pairs[] = [(string) $kelola, (string) $amil];
    }

    return $pairs;
}

/**
 * Helper function to generate ZIS type configurations with different amil percentages
 */
function generateZisTypeConfigs(int $count = 100): array
{
    $faker = Faker::create();
    $configs = [];

    for ($i = 0; $i < $count; $i++) {
        $year = $faker->numberBetween(2020, 2030);

        // Generate different amil percentages for each ZIS type
        $zfAmil = $faker->randomFloat(2, 0, 100);
        $zmAmil = $faker->randomFloat(2, 0, 100);
        $ifsAmil = $faker->randomFloat(2, 0, 100);

        // Generate valid setor/kelola pairs for each
        $zfSetor = $faker->randomFloat(2, 0, 100);
        $zmSetor = $faker->randomFloat(2, 0, 100);
        $ifsSetor = $faker->randomFloat(2, 0, 100);

        $configs[] = [
            $year,
            [
                'zf' => [
                    'setor' => (string) $zfSetor,
                    'kelola' => bcsub('100', (string) $zfSetor, 2),
                    'amil' => (string) $zfAmil,
                ],
                'zm' => [
                    'setor' => (string) $zmSetor,
                    'kelola' => bcsub('100', (string) $zmSetor, 2),
                    'amil' => (string) $zmAmil,
                ],
                'ifs' => [
                    'setor' => (string) $ifsSetor,
                    'kelola' => bcsub('100', (string) $ifsSetor, 2),
                    'amil' => (string) $ifsAmil,
                ],
            ],
        ];
    }

    return $configs;
}

/**
 * Property 1: Setor and Kelola Sum Validation
 *
 * For any AllocationConfig record with setor_percentage S and kelola_percentage K,
 * the sum S + K SHALL equal exactly 100.
 *
 * **Validates: Requirements 1.2**
 */
describe('Property 1: Setor + Kelola Sum Validation', function () {
    it('accepts valid setor/kelola pairs that sum to 100', function (string $setor, string $kelola) {
        // Verify the sum equals 100
        $sum = bcadd($setor, $kelola, 2);
        expect($sum)->toBe('100.00');

        // Create a valid config and verify it saves
        $config = AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => $setor,
            'kelola_percentage' => $kelola,
            'amil_percentage' => '12.50',
        ]);

        expect($config->exists)->toBeTrue();

        // Verify the stored values
        $storedSum = bcadd($config->setor_percentage, $config->kelola_percentage, 2);
        expect($storedSum)->toBe('100.00');
    })->with(generateValidSetorKelolaPairs(100));

    it('rejects invalid setor/kelola pairs that do not sum to 100', function (string $setor, string $kelola) {
        // Verify the sum does NOT equal 100
        $sum = bcadd($setor, $kelola, 2);
        expect($sum)->not->toBe('100.00');

        // Attempt to create an invalid config - should fail due to DB constraint
        try {
            AllocationConfig::create([
                'zis_type' => AllocationConfig::TYPE_ZF,
                'effective_year' => 2025,
                'setor_percentage' => $setor,
                'kelola_percentage' => $kelola,
                'amil_percentage' => '12.50',
            ]);

            // If we get here, the constraint didn't fire - fail the test
            expect(false)->toBeTrue('Expected database constraint violation');
        } catch (\Illuminate\Database\QueryException $e) {
            // Expected - constraint violation
            expect($e->getMessage())->toContain('chk_setor_kelola_sum');
        }
    })->with(generateInvalidSetorKelolaPairs(100));
});

/**
 * Property 2: Percentage Range Validation
 *
 * For any AllocationConfig record, setor_percentage, kelola_percentage, and amil_percentage
 * SHALL each be between 0 and 100 inclusive.
 *
 * **Validates: Requirements 1.3**
 */
describe('Property 2: Percentage Range Validation', function () {
    it('accepts valid percentages within 0-100 range', function (float $percentage) {
        expect($percentage)->toBeGreaterThanOrEqual(0);
        expect($percentage)->toBeLessThanOrEqual(100);

        // Use the percentage as amil (setor/kelola must sum to 100)
        $config = AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => (string) $percentage,
        ]);

        expect($config->exists)->toBeTrue();
        expect((float) $config->amil_percentage)->toBeGreaterThanOrEqual(0);
        expect((float) $config->amil_percentage)->toBeLessThanOrEqual(100);
    })->with(generateRandomPercentages(100));

    it('rejects invalid percentages outside 0-100 range', function (float $percentage) {
        $isNegative = $percentage < 0;
        $isAbove100 = $percentage > 100;

        expect($isNegative || $isAbove100)->toBeTrue();

        // Attempt to create config with invalid amil percentage
        try {
            AllocationConfig::create([
                'zis_type' => AllocationConfig::TYPE_ZF,
                'effective_year' => 2025,
                'setor_percentage' => '30.00',
                'kelola_percentage' => '70.00',
                'amil_percentage' => (string) $percentage,
            ]);

            // If we get here, the constraint didn't fire - fail the test
            expect(false)->toBeTrue('Expected database constraint violation');
        } catch (\Illuminate\Database\QueryException $e) {
            // Expected - constraint violation
            expect($e->getMessage())->toContain('chk_percentages_range');
        }
    })->with(generateInvalidPercentages(100));
});

/**
 * Property 3: Year-Based Rule Resolution
 *
 * For any ZIS type T, set of allocation rules R, and transaction year Y,
 * the system SHALL select the rule from R where:
 * - The rule's zis_type equals T
 * - The rule's effective_year is the maximum value that is less than or equal to Y
 *
 * **Validates: Requirements 2.1, 2.2, 2.4, 4.2**
 */
describe('Property 3: Year-Based Rule Resolution', function () {
    it('selects the correct rule based on transaction year', function (string $zisType, array $rules, int $transactionYear) {
        // Clear any existing configs
        AllocationConfig::query()->delete();

        // Insert all rules
        foreach ($rules as $rule) {
            AllocationConfig::create($rule);
        }

        // Get the service and resolve the allocation
        $service = app(AllocationConfigService::class);
        $service->clearCache();

        $allocation = $service->getAllocation($zisType, "{$transactionYear}-06-15");

        // Find the expected rule: highest effective_year <= transactionYear
        $applicableRules = array_filter($rules, fn($r) => $r['effective_year'] <= $transactionYear);

        if (empty($applicableRules)) {
            // Should fall back to defaults
            $expectedSetor = (string) AllocationConfig::DEFAULT_SETOR;
            $expectedKelola = (string) AllocationConfig::DEFAULT_KELOLA;
        } else {
            // Find the rule with the highest effective_year
            usort($applicableRules, fn($a, $b) => $b['effective_year'] <=> $a['effective_year']);
            $expectedRule = $applicableRules[0];
            $expectedSetor = $expectedRule['setor_percentage'];
            $expectedKelola = $expectedRule['kelola_percentage'];
        }

        expect($allocation['setor'])->toBe($expectedSetor);
        expect($allocation['kelola'])->toBe($expectedKelola);
    })->with(generateRulesAndYears(100));
});

/**
 * Property 4: Penyaluran Percentage Calculation
 *
 * For any amil_percentage A, the penyaluran_percentage P SHALL equal (100 - A).
 *
 * **Validates: Requirements 3.6**
 */
describe('Property 4: Penyaluran Calculation', function () {
    it('calculates penyaluran as 100 minus amil percentage', function (float $amilPercentage) {
        // Create a config with the given amil percentage
        AllocationConfig::query()->delete();

        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => (string) $amilPercentage,
        ]);

        $service = app(AllocationConfigService::class);
        $service->clearCache();

        $allocation = $service->getAllocation(AllocationConfig::TYPE_ZF, '2025-06-15');

        // Verify penyaluran = 100 - amil
        $expectedPenyaluran = bcsub('100', (string) $amilPercentage, 2);
        expect($allocation['penyaluran'])->toBe($expectedPenyaluran);

        // Also verify the mathematical relationship
        $sum = bcadd($allocation['amil'], $allocation['penyaluran'], 2);
        expect($sum)->toBe('100.00');
    })->with(generateAmilPercentages(100));
});

/**
 * Property 5: Allocation Calculation from Kelola
 *
 * For any kelola amount K and amil_percentage A:
 * - IF K > 0: hak_amil = K × A / 100, alokasi_pendis = K - hak_amil
 * - IF K = 0: hak_amil = 0, alokasi_pendis = 0
 *
 * **Validates: Requirements 4.4, 4.5, 10.1, 10.2, 10.3**
 */
describe('Property 5: Allocation Calculation from Kelola', function () {
    it('calculates hak_amil and alokasi_pendis correctly from kelola', function (string $kelolaAmount, string $amilPercentage) {
        $service = new AllocationConfigService();

        $hakAmil = $service->calculateHakAmil($kelolaAmount, $amilPercentage);
        $alokasiPendis = $service->calculatePenyaluran($kelolaAmount, $hakAmil);

        if (bccomp($kelolaAmount, '0', 2) === 0) {
            // Zero kelola case: both should be zero
            expect($hakAmil)->toBe('0');
            expect($alokasiPendis)->toBe('0');
        } else {
            // Positive kelola case
            // Verify hak_amil = kelola × amil / 100
            $expectedHakAmil = bcdiv(bcmul($kelolaAmount, $amilPercentage, 4), '100', 2);
            expect($hakAmil)->toBe($expectedHakAmil);

            // Verify alokasi_pendis = kelola - hak_amil
            $expectedAlokasiPendis = bcsub($kelolaAmount, $hakAmil, 2);
            expect($alokasiPendis)->toBe($expectedAlokasiPendis);

            // Verify the sum equals the original kelola amount
            $sum = bcadd($hakAmil, $alokasiPendis, 2);
            expect($sum)->toBe(number_format((float) $kelolaAmount, 2, '.', ''));
        }
    })->with(generateKelolaAndAmilPairs(100));

    it('returns zero for both when kelola is exactly zero', function () {
        $service = new AllocationConfigService();

        // Test with various amil percentages when kelola is zero
        $amilPercentages = ['0', '12.5', '20', '50', '100'];

        foreach ($amilPercentages as $amil) {
            $hakAmil = $service->calculateHakAmil('0', $amil);
            $alokasiPendis = $service->calculatePenyaluran('0', $hakAmil);

            expect($hakAmil)->toBe('0');
            expect($alokasiPendis)->toBe('0');
        }
    });
});

/**
 * Property 6: ZIS Type-Specific Amil Percentages
 *
 * For any set of allocation configurations, the system SHALL support different
 * amil_percentage values for each ZIS type (zf, zm, ifs) within the same effective_year.
 *
 * **Validates: Requirements 1.4, 10.4**
 */
describe('Property 6: ZIS Type-Specific Amil Percentages', function () {
    it('supports different amil percentages per ZIS type within same year', function (int $year, array $typeConfigs) {
        // Clear existing configs
        AllocationConfig::query()->delete();

        // Create configs for each ZIS type with different amil percentages
        foreach (['zf', 'zm', 'ifs'] as $type) {
            AllocationConfig::create([
                'zis_type' => $type,
                'effective_year' => $year,
                'setor_percentage' => $typeConfigs[$type]['setor'],
                'kelola_percentage' => $typeConfigs[$type]['kelola'],
                'amil_percentage' => $typeConfigs[$type]['amil'],
            ]);
        }

        // Verify all three configs exist
        expect(AllocationConfig::count())->toBe(3);

        // Verify each ZIS type has its own independent amil percentage
        $service = app(AllocationConfigService::class);
        $service->clearCache();

        $date = "{$year}-06-15";

        $zfAlloc = $service->getAllocation(AllocationConfig::TYPE_ZF, $date);
        $zmAlloc = $service->getAllocation(AllocationConfig::TYPE_ZM, $date);
        $ifsAlloc = $service->getAllocation(AllocationConfig::TYPE_IFS, $date);

        // Verify each type returns its configured amil percentage
        expect($zfAlloc['amil'])->toBe($typeConfigs['zf']['amil']);
        expect($zmAlloc['amil'])->toBe($typeConfigs['zm']['amil']);
        expect($ifsAlloc['amil'])->toBe($typeConfigs['ifs']['amil']);

        // Verify the unique constraint allows same year with different ZIS types
        // (if we got here without exception, the constraint is working correctly)
        expect(true)->toBeTrue();
    })->with(generateZisTypeConfigs(100));

    it('enforces unique constraint on zis_type and effective_year combination', function () {
        AllocationConfig::query()->delete();

        // Create first config
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        // Attempt to create duplicate - should fail
        try {
            AllocationConfig::create([
                'zis_type' => AllocationConfig::TYPE_ZF,
                'effective_year' => 2025,
                'setor_percentage' => '40.00',
                'kelola_percentage' => '60.00',
                'amil_percentage' => '15.00',
            ]);

            expect(false)->toBeTrue('Expected unique constraint violation');
        } catch (\Illuminate\Database\QueryException $e) {
            // Expected - unique constraint violation
            expect($e->getMessage())->toContain('allocation_configs_zis_type_effective_year_unique');
        }
    });
});
