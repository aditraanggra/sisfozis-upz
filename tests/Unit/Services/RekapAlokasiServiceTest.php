<?php

use App\Services\AllocationConfigService;
use App\Services\RekapAlokasiService;
use Mockery;

/**
 * Unit tests for RekapAlokasiService allocation calculations
 * These tests verify the service uses AllocationConfigService correctly
 * without requiring database access
 */

afterEach(function () {
    Mockery::close();
});

describe('RekapAlokasiService buildRekapRecord calculations', function () {
    it('uses dynamic percentages from AllocationConfigService for 2025 rules', function () {
        // Mock AllocationConfigService
        $mockAllocationService = Mockery::mock(AllocationConfigService::class);

        // Setup mock to return 2025 rules (30% setor, 70% kelola)
        $mockAllocationService->shouldReceive('getAllocation')
            ->with('zf', '2025-06-15')
            ->andReturn([
                'setor' => '30.00',
                'kelola' => '70.00',
                'amil' => '12.50',
                'penyaluran' => '87.50',
            ]);

        $mockAllocationService->shouldReceive('getAllocation')
            ->with('zm', '2025-06-15')
            ->andReturn([
                'setor' => '30.00',
                'kelola' => '70.00',
                'amil' => '12.50',
                'penyaluran' => '87.50',
            ]);

        $mockAllocationService->shouldReceive('getAllocation')
            ->with('ifs', '2025-06-15')
            ->andReturn([
                'setor' => '30.00',
                'kelola' => '70.00',
                'amil' => '20.00',
                'penyaluran' => '80.00',
            ]);

        // Create service with mocked dependency
        $service = new RekapAlokasiService($mockAllocationService);

        // Use reflection to access protected method
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('buildRekapRecord');
        $method->setAccessible(true);

        // Create test data object
        $data = (object) [
            'period_date' => '2025-06-15',
            'total_zf_amount' => 100000,
            'total_zf_rice' => 10.000,
            'total_zm_amount' => 200000,
            'total_ifs_amount' => 50000,
        ];

        // Call the method
        $result = $method->invoke($service, 1, 'harian', $data);

        // Verify ZF calculations (30% setor, 70% kelola, 12.5% amil)
        expect($result['total_setor_zf_amount'])->toBe(30000); // 100000 * 30%
        expect($result['total_kelola_zf_amount'])->toBe(70000); // 100000 - 30000
        expect($result['hak_amil_zf_amount'])->toBe(8750); // 70000 * 12.5%
        expect($result['alokasi_pendis_zf_amount'])->toBe(61250); // 70000 - 8750

        // Verify ZM calculations (30% setor, 70% kelola, 12.5% amil)
        expect($result['total_setor_zm'])->toBe(60000); // 200000 * 30%
        expect($result['total_kelola_zm'])->toBe(140000); // 200000 - 60000
        expect($result['hak_amil_zm'])->toBe(17500); // 140000 * 12.5%
        expect($result['alokasi_pendis_zm'])->toBe(122500); // 140000 - 17500

        // Verify IFS calculations (30% setor, 70% kelola, 20% amil)
        expect($result['total_setor_ifs'])->toBe(15000); // 50000 * 30%
        expect($result['total_kelola_ifs'])->toBe(35000); // 50000 - 15000
        expect($result['hak_amil_ifs'])->toBe(7000); // 35000 * 20%
        expect($result['alokasi_pendis_ifs'])->toBe(28000); // 35000 - 7000
    });

    it('applies different percentages for 2026 rules', function () {
        // Mock AllocationConfigService
        $mockAllocationService = Mockery::mock(AllocationConfigService::class);

        // Setup mock to return 2026 rules
        $mockAllocationService->shouldReceive('getAllocation')
            ->with('zf', '2026-06-15')
            ->andReturn([
                'setor' => '30.00',
                'kelola' => '70.00',
                'amil' => '12.50',
                'penyaluran' => '87.50',
            ]);

        // ZM 2026: 100% setor, 0% kelola
        $mockAllocationService->shouldReceive('getAllocation')
            ->with('zm', '2026-06-15')
            ->andReturn([
                'setor' => '100.00',
                'kelola' => '0.00',
                'amil' => '12.50',
                'penyaluran' => '87.50',
            ]);

        // IFS 2026: 80% setor, 20% kelola
        $mockAllocationService->shouldReceive('getAllocation')
            ->with('ifs', '2026-06-15')
            ->andReturn([
                'setor' => '80.00',
                'kelola' => '20.00',
                'amil' => '20.00',
                'penyaluran' => '80.00',
            ]);

        // Create service with mocked dependency
        $service = new RekapAlokasiService($mockAllocationService);

        // Use reflection to access protected method
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('buildRekapRecord');
        $method->setAccessible(true);

        // Create test data object
        $data = (object) [
            'period_date' => '2026-06-15',
            'total_zf_amount' => 100000,
            'total_zf_rice' => 10.000,
            'total_zm_amount' => 200000,
            'total_ifs_amount' => 50000,
        ];

        // Call the method
        $result = $method->invoke($service, 1, 'harian', $data);

        // Verify ZF 2026 calculations (30% setor, 70% kelola, 12.5% amil)
        expect($result['total_setor_zf_amount'])->toBe(30000);
        expect($result['total_kelola_zf_amount'])->toBe(70000);
        expect($result['hak_amil_zf_amount'])->toBe(8750);
        expect($result['alokasi_pendis_zf_amount'])->toBe(61250);

        // Verify ZM 2026 calculations (100% setor, 0% kelola)
        expect($result['total_setor_zm'])->toBe(200000); // 200000 * 100%
        expect($result['total_kelola_zm'])->toBe(0); // 200000 - 200000
        expect($result['hak_amil_zm'])->toBe(0); // Zero kelola = zero amil
        expect($result['alokasi_pendis_zm'])->toBe(0); // Zero kelola = zero pendis

        // Verify IFS 2026 calculations (80% setor, 20% kelola, 20% amil)
        expect($result['total_setor_ifs'])->toBe(40000); // 50000 * 80%
        expect($result['total_kelola_ifs'])->toBe(10000); // 50000 - 40000
        expect($result['hak_amil_ifs'])->toBe(2000); // 10000 * 20%
        expect($result['alokasi_pendis_ifs'])->toBe(8000); // 10000 - 2000
    });

    it('handles zero kelola scenario correctly (Requirement 10.3)', function () {
        // Mock AllocationConfigService
        $mockAllocationService = Mockery::mock(AllocationConfigService::class);

        // All ZIS types with 100% setor (zero kelola)
        $mockAllocationService->shouldReceive('getAllocation')
            ->with('zf', '2026-06-15')
            ->andReturn([
                'setor' => '100.00',
                'kelola' => '0.00',
                'amil' => '12.50',
                'penyaluran' => '87.50',
            ]);

        $mockAllocationService->shouldReceive('getAllocation')
            ->with('zm', '2026-06-15')
            ->andReturn([
                'setor' => '100.00',
                'kelola' => '0.00',
                'amil' => '12.50',
                'penyaluran' => '87.50',
            ]);

        $mockAllocationService->shouldReceive('getAllocation')
            ->with('ifs', '2026-06-15')
            ->andReturn([
                'setor' => '100.00',
                'kelola' => '0.00',
                'amil' => '20.00',
                'penyaluran' => '80.00',
            ]);

        // Create service with mocked dependency
        $service = new RekapAlokasiService($mockAllocationService);

        // Use reflection to access protected method
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('buildRekapRecord');
        $method->setAccessible(true);

        // Create test data object
        $data = (object) [
            'period_date' => '2026-06-15',
            'total_zf_amount' => 100000,
            'total_zf_rice' => 10.000,
            'total_zm_amount' => 200000,
            'total_ifs_amount' => 50000,
        ];

        // Call the method
        $result = $method->invoke($service, 1, 'harian', $data);

        // All ZIS types should have 100% setor, 0% kelola
        // And when kelola is 0, hak_amil and alokasi_pendis should be 0

        // ZF
        expect($result['total_setor_zf_amount'])->toBe(100000);
        expect($result['total_kelola_zf_amount'])->toBe(0);
        expect($result['hak_amil_zf_amount'])->toBe(0);
        expect($result['alokasi_pendis_zf_amount'])->toBe(0);

        // ZM
        expect($result['total_setor_zm'])->toBe(200000);
        expect($result['total_kelola_zm'])->toBe(0);
        expect($result['hak_amil_zm'])->toBe(0);
        expect($result['alokasi_pendis_zm'])->toBe(0);

        // IFS
        expect($result['total_setor_ifs'])->toBe(50000);
        expect($result['total_kelola_ifs'])->toBe(0);
        expect($result['hak_amil_ifs'])->toBe(0);
        expect($result['alokasi_pendis_ifs'])->toBe(0);
    });

    it('calculates rice allocations correctly', function () {
        // Mock AllocationConfigService
        $mockAllocationService = Mockery::mock(AllocationConfigService::class);

        $mockAllocationService->shouldReceive('getAllocation')
            ->with('zf', '2025-06-15')
            ->andReturn([
                'setor' => '30.00',
                'kelola' => '70.00',
                'amil' => '12.50',
                'penyaluran' => '87.50',
            ]);

        $mockAllocationService->shouldReceive('getAllocation')
            ->with('zm', '2025-06-15')
            ->andReturn([
                'setor' => '30.00',
                'kelola' => '70.00',
                'amil' => '12.50',
                'penyaluran' => '87.50',
            ]);

        $mockAllocationService->shouldReceive('getAllocation')
            ->with('ifs', '2025-06-15')
            ->andReturn([
                'setor' => '30.00',
                'kelola' => '70.00',
                'amil' => '20.00',
                'penyaluran' => '80.00',
            ]);

        // Create service with mocked dependency
        $service = new RekapAlokasiService($mockAllocationService);

        // Use reflection to access protected method
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('buildRekapRecord');
        $method->setAccessible(true);

        // Create test data object with rice
        $data = (object) [
            'period_date' => '2025-06-15',
            'total_zf_amount' => 0,
            'total_zf_rice' => 100.000, // 100 kg
            'total_zm_amount' => 0,
            'total_ifs_amount' => 0,
        ];

        // Call the method
        $result = $method->invoke($service, 1, 'harian', $data);

        // Verify ZF Rice calculations (30% setor, 70% kelola, 12.5% amil)
        expect((float) $result['total_setor_zf_rice'])->toBe(30.0); // 100 * 30%
        expect((float) $result['total_kelola_zf_rice'])->toBe(70.0); // 100 - 30
        expect((float) $result['hak_amil_zf_rice'])->toBe(8.75); // 70 * 12.5%
        expect((float) $result['alokasi_pendis_zf_rice'])->toBe(61.25); // 70 - 8.75
    });

    it('calculates hak_op correctly (5% from setor)', function () {
        // Mock AllocationConfigService
        $mockAllocationService = Mockery::mock(AllocationConfigService::class);

        $mockAllocationService->shouldReceive('getAllocation')
            ->with('zf', '2025-06-15')
            ->andReturn([
                'setor' => '30.00',
                'kelola' => '70.00',
                'amil' => '12.50',
                'penyaluran' => '87.50',
            ]);

        $mockAllocationService->shouldReceive('getAllocation')
            ->with('zm', '2025-06-15')
            ->andReturn([
                'setor' => '30.00',
                'kelola' => '70.00',
                'amil' => '12.50',
                'penyaluran' => '87.50',
            ]);

        $mockAllocationService->shouldReceive('getAllocation')
            ->with('ifs', '2025-06-15')
            ->andReturn([
                'setor' => '30.00',
                'kelola' => '70.00',
                'amil' => '20.00',
                'penyaluran' => '80.00',
            ]);

        // Create service with mocked dependency
        $service = new RekapAlokasiService($mockAllocationService);

        // Use reflection to access protected method
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('buildRekapRecord');
        $method->setAccessible(true);

        // Create test data object
        $data = (object) [
            'period_date' => '2025-06-15',
            'total_zf_amount' => 100000,
            'total_zf_rice' => 100.000,
            'total_zm_amount' => 0,
            'total_ifs_amount' => 0,
        ];

        // Call the method
        $result = $method->invoke($service, 1, 'harian', $data);

        // Verify hak_op is 5% of setor (not from allocation config)
        // Setor ZF Amount = 30000, so hak_op = 30000 * 5% = 1500
        expect($result['hak_op_zf_amount'])->toBe(1500);

        // Setor ZF Rice = 30, so hak_op = 30 * 5% = 1.5
        expect((float) $result['hak_op_zf_rice'])->toBe(1.5);
    });

    it('uses different amil percentages per ZIS type', function () {
        // Mock AllocationConfigService
        $mockAllocationService = Mockery::mock(AllocationConfigService::class);

        // ZF with 12.5% amil
        $mockAllocationService->shouldReceive('getAllocation')
            ->with('zf', '2025-06-15')
            ->andReturn([
                'setor' => '30.00',
                'kelola' => '70.00',
                'amil' => '12.50',
                'penyaluran' => '87.50',
            ]);

        // ZM with 15% amil (different from ZF)
        $mockAllocationService->shouldReceive('getAllocation')
            ->with('zm', '2025-06-15')
            ->andReturn([
                'setor' => '30.00',
                'kelola' => '70.00',
                'amil' => '15.00',
                'penyaluran' => '85.00',
            ]);

        // IFS with 20% amil
        $mockAllocationService->shouldReceive('getAllocation')
            ->with('ifs', '2025-06-15')
            ->andReturn([
                'setor' => '30.00',
                'kelola' => '70.00',
                'amil' => '20.00',
                'penyaluran' => '80.00',
            ]);

        // Create service with mocked dependency
        $service = new RekapAlokasiService($mockAllocationService);

        // Use reflection to access protected method
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('buildRekapRecord');
        $method->setAccessible(true);

        // Create test data object with same amount for all ZIS types
        $data = (object) [
            'period_date' => '2025-06-15',
            'total_zf_amount' => 100000,
            'total_zf_rice' => 0,
            'total_zm_amount' => 100000,
            'total_ifs_amount' => 100000,
        ];

        // Call the method
        $result = $method->invoke($service, 1, 'harian', $data);

        // All have same kelola (70000), but different amil percentages
        // ZF: 70000 * 12.5% = 8750
        expect($result['hak_amil_zf_amount'])->toBe(8750);

        // ZM: 70000 * 15% = 10500
        expect($result['hak_amil_zm'])->toBe(10500);

        // IFS: 70000 * 20% = 14000
        expect($result['hak_amil_ifs'])->toBe(14000);
    });
});

describe('RekapAlokasiService constructor injection', function () {
    it('accepts AllocationConfigService via constructor', function () {
        $mockAllocationService = Mockery::mock(AllocationConfigService::class);

        $service = new RekapAlokasiService($mockAllocationService);

        expect($service)->toBeInstanceOf(RekapAlokasiService::class);
    });
});
