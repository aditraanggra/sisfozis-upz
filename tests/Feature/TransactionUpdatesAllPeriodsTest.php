<?php

use App\Models\Ifs;
use App\Models\RekapZis;
use App\Models\UnitZis;
use App\Models\Zf;
use App\Models\Zm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

describe('Transaction Updates All Periods', function () {
    beforeEach(function () {
        Queue::fake();
    });

    describe('Zf Transaction Updates', function () {
        it('updates all periods when ZF is created', function () {
            $unit = UnitZis::factory()->create();

            // Create a ZF transaction
            $zf = Zf::factory()->create([
                'unit_id' => $unit->id,
                'trx_date' => '2024-01-15',
            ]);

            // Should have dispatched 3 jobs (daily, monthly, yearly)
            Queue::assertPushed(\App\Jobs\UpdateRekapZis::class, 3);
        });

        it('updates all periods when ZF is updated', function () {
            $unit = UnitZis::factory()->create();

            // Create initial ZF
            $zf = Zf::factory()->create([
                'unit_id' => $unit->id,
                'trx_date' => '2024-01-15',
            ]);

            // Reset queue fake
            Queue::fake();

            // Update the ZF
            $zf->update(['amount' => 50000]);

            // Should have dispatched 3 jobs for current date
            Queue::assertPushed(\App\Jobs\UpdateRekapZis::class, 3);
        });

        it('updates old and new dates when ZF date is changed', function () {
            $unit = UnitZis::factory()->create();

            // Create initial ZF
            $zf = Zf::factory()->create([
                'unit_id' => $unit->id,
                'trx_date' => '2024-01-15',
            ]);

            // Reset queue fake
            Queue::fake();

            // Update the ZF date
            $zf->update(['trx_date' => '2024-02-15']);

            // Should have dispatched 6 jobs (3 for old date, 3 for new date)
            Queue::assertPushed(\App\Jobs\UpdateRekapZis::class, 6);
        });
    });

    describe('Zm Transaction Updates', function () {
        it('updates all periods when ZM is created', function () {
            $unit = UnitZis::factory()->create();

            // Create a ZM transaction
            $zm = Zm::factory()->create([
                'unit_id' => $unit->id,
                'trx_date' => '2024-01-15',
            ]);

            // Should have dispatched 3 jobs (daily, monthly, yearly)
            Queue::assertPushed(\App\Jobs\UpdateRekapZis::class, 3);
        });

        it('updates all periods when ZM is updated', function () {
            $unit = UnitZis::factory()->create();

            // Create initial ZM
            $zm = Zm::factory()->create([
                'unit_id' => $unit->id,
                'trx_date' => '2024-01-15',
            ]);

            // Reset queue fake
            Queue::fake();

            // Update the ZM
            $zm->update(['amount' => 30000]);

            // Should have dispatched 3 jobs for current date
            Queue::assertPushed(\App\Jobs\UpdateRekapZis::class, 3);
        });
    });

    describe('Ifs Transaction Updates', function () {
        it('updates all periods when IFS is created', function () {
            $unit = UnitZis::factory()->create();

            // Create an IFS transaction
            $ifs = Ifs::factory()->create([
                'unit_id' => $unit->id,
                'trx_date' => '2024-01-15',
            ]);

            // Should have dispatched 3 jobs (daily, monthly, yearly)
            Queue::assertPushed(\App\Jobs\UpdateRekapZis::class, 3);
        });

        it('updates all periods when IFS is updated', function () {
            $unit = UnitZis::factory()->create();

            // Create initial IFS
            $ifs = Ifs::factory()->create([
                'unit_id' => $unit->id,
                'trx_date' => '2024-01-15',
            ]);

            // Reset queue fake
            Queue::fake();

            // Update the IFS
            $ifs->update(['amount' => 25000]);

            // Should have dispatched 3 jobs for current date
            Queue::assertPushed(\App\Jobs\UpdateRekapZis::class, 3);
        });
    });

    describe('RekapZis Records Creation', function () {
        it('creates correct rekap records for all periods', function () {
            $unit = UnitZis::factory()->create();

            // Process the queue jobs to simulate real execution
            Queue::fake();

            // Create a ZF transaction
            $zf = Zf::factory()->create([
                'unit_id' => $unit->id,
                'trx_date' => '2024-01-15',
                'amount' => 10000,
            ]);

            // Process all queued jobs
            Queue::assertPushed(\App\Jobs\UpdateRekapZis::class, 3);

            // Simulate job processing by calling the service directly
            $service = app(\App\Services\RekapZisService::class);

            // Update daily
            $service->updateDailyRekapitulasi('2024-01-15', $unit->id);

            // Update monthly
            $service->updateMonthlyRekapitulasi(1, 2024, $unit->id);

            // Update yearly
            $service->updateYearlyRekapitulasi(2024, $unit->id);

            // Verify all rekap records were created
            $rekapRecords = RekapZis::where('unit_id', $unit->id)->get();

            expect($rekapRecords)->toHaveCount(3);

            $dailyRecord = $rekapRecords->where('period', 'harian')->first();
            $monthlyRecord = $rekapRecords->where('period', 'bulanan')->first();
            $yearlyRecord = $rekapRecords->where('period', 'tahunan')->first();

            // Verify daily record
            expect($dailyRecord->period_date)->toBe('2024-01-15');
            expect($dailyRecord->total_zf_amount)->toBe(10000);

            // Verify monthly record
            expect($monthlyRecord->period_date)->toBe('2024-01-01');
            expect($monthlyRecord->total_zf_amount)->toBe(10000);

            // Verify yearly record
            expect($yearlyRecord->period_date)->toBe('2024-01-01');
            expect($yearlyRecord->total_zf_amount)->toBe(10000);
        });
    });

    describe('Job Types Verification', function () {
        it('creates jobs with correct period types', function () {
            $unit = UnitZis::factory()->create();

            // Create a transaction
            $zf = Zf::factory()->create([
                'unit_id' => $unit->id,
                'trx_date' => '2024-01-15',
            ]);

            Queue::assertPushed(\App\Jobs\UpdateRekapZis::class, function ($job) use ($unit) {
                $reflection = new ReflectionClass($job);
                $dateProperty = $reflection->getProperty('date');
                $unitIdProperty = $reflection->getProperty('unitId');
                $periodTypeProperty = $reflection->getProperty('periodType');

                $dateProperty->setAccessible(true);
                $unitIdProperty->setAccessible(true);
                $periodTypeProperty->setAccessible(true);

                return $dateProperty->getValue($job) === '2024-01-15'
                    && $unitIdProperty->getValue($job) === $unit->id
                    && $periodTypeProperty->getValue($job) === 'harian';
            });

            Queue::assertPushed(\App\Jobs\UpdateRekapZis::class, function ($job) {
                $reflection = new ReflectionClass($job);
                $periodTypeProperty = $reflection->getProperty('periodType');
                $periodTypeProperty->setAccessible(true);

                return $periodTypeProperty->getValue($job) === 'bulanan';
            });

            Queue::assertPushed(\App\Jobs\UpdateRekapZis::class, function ($job) {
                $reflection = new ReflectionClass($job);
                $periodTypeProperty = $reflection->getProperty('periodType');
                $periodTypeProperty->setAccessible(true);

                return $periodTypeProperty->getValue($job) === 'tahunan';
            });
        });
    });
});
