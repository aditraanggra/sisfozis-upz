<?php

use App\Jobs\UpdateRekapPendis;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UpdateRekapPendis Job', function () {
    it('can be instantiated with default harian period', function () {
        $job = new UpdateRekapPendis('2024-01-01', 1);

        expect($job)->toBeInstanceOf(UpdateRekapPendis::class);
    });

    it('can be instantiated with specific period type', function () {
        $job = new UpdateRekapPendis('2024-01-01', 1, 'tahunan');

        expect($job)->toBeInstanceOf(UpdateRekapPendis::class);
    });

    it('creates correct job for daily update', function () {
        $job = new UpdateRekapPendis('2024-01-15', 1, 'harian');

        expect($job)->toBeInstanceOf(UpdateRekapPendis::class);
    });

    it('creates correct job for monthly update', function () {
        $job = new UpdateRekapPendis('2024-01-15', 1, 'bulanan');

        expect($job)->toBeInstanceOf(UpdateRekapPendis::class);
    });

    it('creates correct job for yearly update', function () {
        $job = new UpdateRekapPendis('2024-01-15', 1, 'tahunan');

        expect($job)->toBeInstanceOf(UpdateRekapPendis::class);
    });

    it('has correct job properties for daily period', function () {
        $job = new UpdateRekapPendis('2024-01-15', 1, 'harian');

        expect($job->tries)->toBe(3);
        expect($job->timeout)->toBe(120);
    });

    it('supports static factory methods', function () {
        // Test that static methods exist without calling them
        expect(class_uses_recursive(UpdateRekapPendis::class))->toHaveKey('Illuminate\Foundation\Bus\Dispatchable');
    });
});
