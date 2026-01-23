<?php

use App\Services\AllocationConfigService;

/**
 * Pure unit tests for AllocationConfigService calculation methods
 * These tests don't require database access
 */
describe('calculateHakAmil', function () {
    it('calculates hak_amil correctly', function () {
        $service = new AllocationConfigService();

        // 70000 kelola * 12.5% amil = 8750
        $hakAmil = $service->calculateHakAmil('70000', '12.5');
        expect($hakAmil)->toBe('8750.00');
    });

    it('calculates hak_amil with different amil percentages', function () {
        $service = new AllocationConfigService();

        // 70000 kelola * 20% amil = 14000
        $hakAmil = $service->calculateHakAmil('70000', '20');
        expect($hakAmil)->toBe('14000.00');
    });

    it('returns zero when kelola is zero', function () {
        $service = new AllocationConfigService();

        $hakAmil = $service->calculateHakAmil('0', '12.5');
        expect($hakAmil)->toBe('0');
    });

    it('handles decimal kelola amounts', function () {
        $service = new AllocationConfigService();

        // 70000.50 kelola * 12.5% amil = 8750.0625 -> 8750.06
        $hakAmil = $service->calculateHakAmil('70000.50', '12.5');
        expect($hakAmil)->toBe('8750.06');
    });

    it('respects custom scale parameter', function () {
        $service = new AllocationConfigService();

        // With scale 4
        $hakAmil = $service->calculateHakAmil('70000.50', '12.5', 4);
        expect($hakAmil)->toBe('8750.0625');
    });

    it('handles large amounts correctly', function () {
        $service = new AllocationConfigService();

        // 1000000000 kelola * 12.5% amil = 125000000
        $hakAmil = $service->calculateHakAmil('1000000000', '12.5');
        expect($hakAmil)->toBe('125000000.00');
    });
});

describe('calculatePenyaluran', function () {
    it('calculates penyaluran correctly', function () {
        $service = new AllocationConfigService();

        // 70000 kelola - 8750 hak_amil = 61250
        $penyaluran = $service->calculatePenyaluran('70000', '8750');
        expect($penyaluran)->toBe('61250.00');
    });

    it('returns zero when kelola is zero', function () {
        $service = new AllocationConfigService();

        $penyaluran = $service->calculatePenyaluran('0', '0');
        expect($penyaluran)->toBe('0');
    });

    it('handles decimal amounts', function () {
        $service = new AllocationConfigService();

        $penyaluran = $service->calculatePenyaluran('70000.50', '8750.06');
        expect($penyaluran)->toBe('61250.44');
    });

    it('respects custom scale parameter', function () {
        $service = new AllocationConfigService();

        $penyaluran = $service->calculatePenyaluran('70000.5000', '8750.0625', 4);
        expect($penyaluran)->toBe('61250.4375');
    });

    it('handles large amounts correctly', function () {
        $service = new AllocationConfigService();

        $penyaluran = $service->calculatePenyaluran('1000000000', '125000000');
        expect($penyaluran)->toBe('875000000.00');
    });
});

describe('zero kelola case (100% setor) - pure calculations', function () {
    it('returns zero for hak_amil when kelola is zero', function () {
        $service = new AllocationConfigService();

        // Even with non-zero amil percentage, zero kelola means zero hak_amil
        $hakAmil = $service->calculateHakAmil('0', '12.5');
        expect($hakAmil)->toBe('0');
    });

    it('returns zero for penyaluran when kelola is zero', function () {
        $service = new AllocationConfigService();

        $penyaluran = $service->calculatePenyaluran('0', '0');
        expect($penyaluran)->toBe('0');
    });

    it('handles complete zero kelola workflow', function () {
        $service = new AllocationConfigService();

        // Simulate 100% setor scenario
        $totalAmount = '100000';
        $setorPercentage = '100';
        $kelolaPercentage = '0';
        $amilPercentage = '12.5';

        // Calculate setor and kelola amounts
        $setorAmount = bcdiv(bcmul($totalAmount, $setorPercentage, 4), '100', 2);
        $kelolaAmount = bcdiv(bcmul($totalAmount, $kelolaPercentage, 4), '100', 2);

        expect($setorAmount)->toBe('100000.00');
        expect($kelolaAmount)->toBe('0.00');

        // Both hak_amil and penyaluran should be zero
        $hakAmil = $service->calculateHakAmil($kelolaAmount, $amilPercentage);
        $penyaluran = $service->calculatePenyaluran($kelolaAmount, $hakAmil);

        expect($hakAmil)->toBe('0');
        expect($penyaluran)->toBe('0');
    });
});
