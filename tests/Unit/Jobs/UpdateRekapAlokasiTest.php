<?php

use App\Jobs\UpdateRekapAlokasi;
use App\Services\RekapAlokasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

describe('UpdateRekapAlokasi Job', function () {
    it('can be instantiated with unit and period', function () {
        $job = new UpdateRekapAlokasi(1, 'harian');

        expect($job)->toBeInstanceOf(UpdateRekapAlokasi::class);
    });

    it('can be instantiated with different periods', function () {
        $daily = new UpdateRekapAlokasi(1, 'harian');
        $monthly = new UpdateRekapAlokasi(1, 'bulanan');
        $yearly = new UpdateRekapAlokasi(1, 'tahunan');

        expect($daily)->toBeInstanceOf(UpdateRekapAlokasi::class);
        expect($monthly)->toBeInstanceOf(UpdateRekapAlokasi::class);
        expect($yearly)->toBeInstanceOf(UpdateRekapAlokasi::class);
    });

    it('has correct job properties', function () {
        $job = new UpdateRekapAlokasi(1, 'harian');

        expect($job->tries)->toBe(3);
    });

    it('supports static factory methods', function () {
        expect(class_uses_recursive(UpdateRekapAlokasi::class))->toHaveKey('Illuminate\Foundation\Bus\Dispatchable');
    });

    it('can be instantiated with period_date', function () {
        $job = new UpdateRekapAlokasi(1, 'tahunan', '2026-01-01');

        $reflection = new ReflectionClass($job);
        $periodDateProperty = $reflection->getProperty('periodDate');
        $periodDateProperty->setAccessible(true);

        expect($periodDateProperty->getValue($job))->toBe('2026-01-01');
    });

    it('defaults period_date to null when not provided', function () {
        $job = new UpdateRekapAlokasi(1, 'harian');

        $reflection = new ReflectionClass($job);
        $periodDateProperty = $reflection->getProperty('periodDate');
        $periodDateProperty->setAccessible(true);

        expect($periodDateProperty->getValue($job))->toBeNull();
    });

    it('passes period_date to service', function () {
        Log::spy();

        $mockService = Mockery::mock(RekapAlokasiService::class);
        $mockService->shouldReceive('updateOrCreateRekapAlokasi')
            ->once()
            ->with(1, 'tahunan', '2026-01-01')
            ->andReturn(new \App\Models\RekapAlokasi());

        $job = new UpdateRekapAlokasi(1, 'tahunan', '2026-01-01');
        $job->handle($mockService);
    });

    it('passes null period_date to service when not provided', function () {
        Log::spy();

        $mockService = Mockery::mock(RekapAlokasiService::class);
        $mockService->shouldReceive('updateOrCreateRekapAlokasi')
            ->once()
            ->with(1, 'harian', null)
            ->andReturn(new \App\Models\RekapAlokasi());

        $job = new UpdateRekapAlokasi(1, 'harian');
        $job->handle($mockService);
    });
});
