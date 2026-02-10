<?php

use App\Jobs\UpdateRekapZis;
use App\Services\RekapZisService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UpdateRekapZis Job', function () {
    it('can be instantiated with default harian period', function () {
        $job = new UpdateRekapZis('2024-01-15', 1);

        // Use reflection to access protected properties for testing
        $reflection = new ReflectionClass($job);
        $dateProperty = $reflection->getProperty('date');
        $unitIdProperty = $reflection->getProperty('unitId');
        $periodTypeProperty = $reflection->getProperty('periodType');

        $dateProperty->setAccessible(true);
        $unitIdProperty->setAccessible(true);
        $periodTypeProperty->setAccessible(true);

        expect($dateProperty->getValue($job))->toBe('2024-01-15');
        expect($unitIdProperty->getValue($job))->toBe(1);
        expect($periodTypeProperty->getValue($job))->toBe('harian');
    });

    it('can be instantiated with specific period type', function () {
        $job = new UpdateRekapZis('2024-01-15', 1, 'bulanan');

        // Use reflection to access protected properties for testing
        $reflection = new ReflectionClass($job);
        $periodTypeProperty = $reflection->getProperty('periodType');
        $periodTypeProperty->setAccessible(true);

        expect($periodTypeProperty->getValue($job))->toBe('bulanan');
    });

    it('creates correct job for daily update', function () {
        $job = new UpdateRekapZis('2024-01-15', 1, 'harian');

        $reflection = new ReflectionClass($job);
        $dateProperty = $reflection->getProperty('date');
        $unitIdProperty = $reflection->getProperty('unitId');
        $periodTypeProperty = $reflection->getProperty('periodType');

        $dateProperty->setAccessible(true);
        $unitIdProperty->setAccessible(true);
        $periodTypeProperty->setAccessible(true);

        expect($dateProperty->getValue($job))->toBe('2024-01-15');
        expect($unitIdProperty->getValue($job))->toBe(1);
        expect($periodTypeProperty->getValue($job))->toBe('harian');
    });

    it('creates correct job for monthly update', function () {
        $job = new UpdateRekapZis('2024-01-15', 1, 'bulanan');

        $reflection = new ReflectionClass($job);
        $dateProperty = $reflection->getProperty('date');
        $unitIdProperty = $reflection->getProperty('unitId');
        $periodTypeProperty = $reflection->getProperty('periodType');

        $dateProperty->setAccessible(true);
        $unitIdProperty->setAccessible(true);
        $periodTypeProperty->setAccessible(true);

        expect($dateProperty->getValue($job))->toBe('2024-01-15');
        expect($unitIdProperty->getValue($job))->toBe(1);
        expect($periodTypeProperty->getValue($job))->toBe('bulanan');
    });

    it('creates correct job for yearly update', function () {
        $job = new UpdateRekapZis('2024-01-15', 1, 'tahunan');

        $reflection = new ReflectionClass($job);
        $dateProperty = $reflection->getProperty('date');
        $unitIdProperty = $reflection->getProperty('unitId');
        $periodTypeProperty = $reflection->getProperty('periodType');

        $dateProperty->setAccessible(true);
        $unitIdProperty->setAccessible(true);
        $periodTypeProperty->setAccessible(true);

        expect($dateProperty->getValue($job))->toBe('2024-01-15');
        expect($unitIdProperty->getValue($job))->toBe(1);
        expect($periodTypeProperty->getValue($job))->toBe('tahunan');
    });

    it('handles daily period correctly', function () {
        $mockService = Mockery::mock(RekapZisService::class);
        $mockService->shouldReceive('updateDailyRekapitulasi')
            ->once()
            ->with('2024-01-15', 1);

        $job = new UpdateRekapZis('2024-01-15', 1, 'harian');
        $job->handle($mockService);
    });

    it('handles monthly period correctly', function () {
        $mockService = Mockery::mock(RekapZisService::class);
        $mockService->shouldReceive('updateMonthlyRekapitulasi')
            ->once()
            ->with(1, 2024, 1);

        $job = new UpdateRekapZis('2024-01-15', 1, 'bulanan');
        $job->handle($mockService);
    });

    it('handles yearly period correctly', function () {
        $mockService = Mockery::mock(RekapZisService::class);
        $mockService->shouldReceive('updateYearlyRekapitulasi')
            ->once()
            ->with(2024, 1);

        $job = new UpdateRekapZis('2024-01-15', 1, 'tahunan');
        $job->handle($mockService);
    });
});
