<?php

use App\Jobs\UpdateRekapAlokasi;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
});
