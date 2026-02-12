<?php

use App\Jobs\UpdateRekapUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UpdateRekapUnit Job', function () {
    it('can be instantiated with unit and period', function () {
        $job = new UpdateRekapUnit(1, 'harian');

        expect($job)->toBeInstanceOf(UpdateRekapUnit::class);
    });

    it('can be instantiated with different periods', function () {
        $daily = new UpdateRekapUnit(1, 'harian');
        $monthly = new UpdateRekapUnit(1, 'bulanan');
        $yearly = new UpdateRekapUnit(1, 'tahunan');

        expect($daily)->toBeInstanceOf(UpdateRekapUnit::class);
        expect($monthly)->toBeInstanceOf(UpdateRekapUnit::class);
        expect($yearly)->toBeInstanceOf(UpdateRekapUnit::class);
    });

    it('has correct job properties', function () {
        $job = new UpdateRekapUnit(1, 'harian');

        expect($job->tries)->toBe(3);
    });

    it('supports static factory methods', function () {
        expect(class_uses(UpdateRekapUnit::class))->toHaveKey('Illuminate\Foundation\Bus\Dispatchable');
    });
});
