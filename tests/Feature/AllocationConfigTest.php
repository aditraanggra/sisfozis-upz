<?php

/**
 * Feature Integration Tests for ZIS Allocation Configuration
 *
 * Tests cover:
 * - Cache invalidation on config update via observer
 * - Permission restrictions (only super_admin, admin, tim_sisfo can access)
 * - Seeder creates expected configurations
 * - Backward compatibility (empty table uses fallback defaults)
 * - Database constraints
 */

use App\Filament\Resources\AllocationConfigResource;
use App\Models\AllocationConfig;
use App\Models\User;
use App\Services\AllocationConfigService;
use Database\Seeders\AllocationConfigSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

beforeEach(function () {
    // Clear cache before each test
    Cache::forget('allocation_configs');

    // Clean up existing allocation configs to avoid unique constraint violations
    AllocationConfig::query()->delete();

    // Create roles for testing (use firstOrCreate to handle existing roles)
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'tim_sisfo', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'monitoring', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'upz_kecamatan', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'upz_desa', 'guard_name' => 'web']);
});

/**
 * Helper function to create a user with a specific role
 */
function createUserWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);
    return $user;
}

describe('Cache Invalidation via Observer', function () {
    it('clears cache when allocation config is created', function () {
        $service = app(AllocationConfigService::class);

        // Pre-populate cache by making a request
        $service->getAllocation(AllocationConfig::TYPE_ZF, '2025-06-15');
        expect(Cache::has('allocation_configs'))->toBeTrue();

        // Create new config - observer should clear cache
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        // Cache should be cleared by observer
        expect(Cache::has('allocation_configs'))->toBeFalse();
    });

    it('clears cache when allocation config is updated', function () {
        $service = app(AllocationConfigService::class);

        $config = AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        // Clear and re-populate cache
        $service->clearCache();
        $service->getAllocation(AllocationConfig::TYPE_ZF, '2025-06-15');
        expect(Cache::has('allocation_configs'))->toBeTrue();

        // Update config - observer should clear cache
        $config->update(['amil_percentage' => '15.00']);

        // Cache should be cleared by observer
        expect(Cache::has('allocation_configs'))->toBeFalse();
    });

    it('clears cache when allocation config is deleted', function () {
        $service = app(AllocationConfigService::class);

        $config = AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        // Pre-populate cache
        $service->getAllocation(AllocationConfig::TYPE_ZF, '2025-06-15');
        expect(Cache::has('allocation_configs'))->toBeTrue();

        // Delete config - observer should clear cache
        $config->delete();

        // Cache should be cleared by observer
        expect(Cache::has('allocation_configs'))->toBeFalse();
    });

    it('service returns updated values after cache invalidation', function () {
        $service = app(AllocationConfigService::class);

        $config = AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        // Get initial value
        $initialAllocation = $service->getAllocation(AllocationConfig::TYPE_ZF, '2025-06-15');
        expect($initialAllocation['setor'])->toBe('30.00');

        // Update config (observer clears cache)
        $config->update([
            'setor_percentage' => '40.00',
            'kelola_percentage' => '60.00',
        ]);

        // Get updated value - should reflect the change
        $updatedAllocation = $service->getAllocation(AllocationConfig::TYPE_ZF, '2025-06-15');
        expect($updatedAllocation['setor'])->toBe('40.00');
        expect($updatedAllocation['kelola'])->toBe('60.00');
    });
});

describe('Permission Restrictions', function () {
    describe('Resource Access Control', function () {
        it('allows super_admin to access allocation config resource', function () {
            $user = createUserWithRole('super_admin');
            $this->actingAs($user);

            expect(AllocationConfigResource::canViewAny())->toBeTrue();
        });

        it('allows admin to access allocation config resource', function () {
            $user = createUserWithRole('admin');
            $this->actingAs($user);

            expect(AllocationConfigResource::canViewAny())->toBeTrue();
        });

        it('allows tim_sisfo to access allocation config resource', function () {
            $user = createUserWithRole('tim_sisfo');
            $this->actingAs($user);

            expect(AllocationConfigResource::canViewAny())->toBeTrue();
        });

        it('denies monitoring role access to allocation config resource', function () {
            $user = createUserWithRole('monitoring');
            $this->actingAs($user);

            expect(AllocationConfigResource::canViewAny())->toBeFalse();
        });

        it('denies upz_kecamatan role access to allocation config resource', function () {
            $user = createUserWithRole('upz_kecamatan');
            $this->actingAs($user);

            expect(AllocationConfigResource::canViewAny())->toBeFalse();
        });

        it('denies upz_desa role access to allocation config resource', function () {
            $user = createUserWithRole('upz_desa');
            $this->actingAs($user);

            expect(AllocationConfigResource::canViewAny())->toBeFalse();
        });

        it('denies unauthenticated users access', function () {
            expect(AllocationConfigResource::canViewAny())->toBeFalse();
        });
    });

    describe('Policy Authorization', function () {
        it('policy allows super_admin all actions', function () {
            $user = createUserWithRole('super_admin');
            $config = AllocationConfig::create([
                'zis_type' => AllocationConfig::TYPE_ZF,
                'effective_year' => 2025,
                'setor_percentage' => '30.00',
                'kelola_percentage' => '70.00',
                'amil_percentage' => '12.50',
            ]);

            expect($user->can('viewAny', AllocationConfig::class))->toBeTrue();
            expect($user->can('view', $config))->toBeTrue();
            expect($user->can('create', AllocationConfig::class))->toBeTrue();
            expect($user->can('update', $config))->toBeTrue();
            expect($user->can('delete', $config))->toBeTrue();
        });

        it('policy allows admin all actions', function () {
            $user = createUserWithRole('admin');
            $config = AllocationConfig::create([
                'zis_type' => AllocationConfig::TYPE_ZF,
                'effective_year' => 2025,
                'setor_percentage' => '30.00',
                'kelola_percentage' => '70.00',
                'amil_percentage' => '12.50',
            ]);

            expect($user->can('viewAny', AllocationConfig::class))->toBeTrue();
            expect($user->can('view', $config))->toBeTrue();
            expect($user->can('create', AllocationConfig::class))->toBeTrue();
            expect($user->can('update', $config))->toBeTrue();
            expect($user->can('delete', $config))->toBeTrue();
        });

        it('policy allows tim_sisfo all actions', function () {
            $user = createUserWithRole('tim_sisfo');
            $config = AllocationConfig::create([
                'zis_type' => AllocationConfig::TYPE_ZF,
                'effective_year' => 2025,
                'setor_percentage' => '30.00',
                'kelola_percentage' => '70.00',
                'amil_percentage' => '12.50',
            ]);

            expect($user->can('viewAny', AllocationConfig::class))->toBeTrue();
            expect($user->can('view', $config))->toBeTrue();
            expect($user->can('create', AllocationConfig::class))->toBeTrue();
            expect($user->can('update', $config))->toBeTrue();
            expect($user->can('delete', $config))->toBeTrue();
        });

        it('policy denies monitoring all actions', function () {
            $user = createUserWithRole('monitoring');
            $config = AllocationConfig::create([
                'zis_type' => AllocationConfig::TYPE_ZF,
                'effective_year' => 2025,
                'setor_percentage' => '30.00',
                'kelola_percentage' => '70.00',
                'amil_percentage' => '12.50',
            ]);

            expect($user->can('viewAny', AllocationConfig::class))->toBeFalse();
            expect($user->can('view', $config))->toBeFalse();
            expect($user->can('create', AllocationConfig::class))->toBeFalse();
            expect($user->can('update', $config))->toBeFalse();
            expect($user->can('delete', $config))->toBeFalse();
        });

        it('policy denies upz_kecamatan all actions', function () {
            $user = createUserWithRole('upz_kecamatan');
            $config = AllocationConfig::create([
                'zis_type' => AllocationConfig::TYPE_ZF,
                'effective_year' => 2025,
                'setor_percentage' => '30.00',
                'kelola_percentage' => '70.00',
                'amil_percentage' => '12.50',
            ]);

            expect($user->can('viewAny', AllocationConfig::class))->toBeFalse();
            expect($user->can('view', $config))->toBeFalse();
            expect($user->can('create', AllocationConfig::class))->toBeFalse();
            expect($user->can('update', $config))->toBeFalse();
            expect($user->can('delete', $config))->toBeFalse();
        });
    });
});


describe('Seeder Creates Expected Configurations', function () {
    it('seeder creates all expected 2025 configurations', function () {
        $seeder = new AllocationConfigSeeder();
        $seeder->run();

        $this->assertDatabaseHas('allocation_configs', [
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        $this->assertDatabaseHas('allocation_configs', [
            'zis_type' => AllocationConfig::TYPE_ZM,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        $this->assertDatabaseHas('allocation_configs', [
            'zis_type' => AllocationConfig::TYPE_IFS,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '20.00',
        ]);
    });

    it('seeder creates all expected 2026 configurations', function () {
        $seeder = new AllocationConfigSeeder();
        $seeder->run();

        $this->assertDatabaseHas('allocation_configs', [
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2026,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        $this->assertDatabaseHas('allocation_configs', [
            'zis_type' => AllocationConfig::TYPE_ZM,
            'effective_year' => 2026,
            'setor_percentage' => '100.00',
            'kelola_percentage' => '0.00',
            'amil_percentage' => '12.50',
        ]);

        $this->assertDatabaseHas('allocation_configs', [
            'zis_type' => AllocationConfig::TYPE_IFS,
            'effective_year' => 2026,
            'setor_percentage' => '80.00',
            'kelola_percentage' => '20.00',
            'amil_percentage' => '20.00',
        ]);
    });

    it('seeder creates exactly 6 configurations', function () {
        $seeder = new AllocationConfigSeeder();
        $seeder->run();

        expect(AllocationConfig::count())->toBe(6);
    });

    it('seeder is idempotent (can run multiple times)', function () {
        $seeder = new AllocationConfigSeeder();
        $seeder->run();
        $seeder->run();

        expect(AllocationConfig::count())->toBe(6);
    });

    it('seeder updates existing configs on re-run', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '50.00',
            'kelola_percentage' => '50.00',
            'amil_percentage' => '10.00',
            'description' => 'Old description',
        ]);

        $seeder = new AllocationConfigSeeder();
        $seeder->run();

        $config = AllocationConfig::where('zis_type', AllocationConfig::TYPE_ZF)
            ->where('effective_year', 2025)
            ->first();

        expect($config->setor_percentage)->toBe('30.00');
        expect($config->kelola_percentage)->toBe('70.00');
        expect($config->amil_percentage)->toBe('12.50');
        expect($config->description)->toBe('Aturan ZF 2025');
    });

    it('seeded configs work correctly with AllocationConfigService', function () {
        $seeder = new AllocationConfigSeeder();
        $seeder->run();

        $service = app(AllocationConfigService::class);
        $service->clearCache();

        $zf2025 = $service->getAllocation(AllocationConfig::TYPE_ZF, '2025-06-15');
        expect($zf2025['setor'])->toBe('30.00');
        expect($zf2025['kelola'])->toBe('70.00');
        expect($zf2025['amil'])->toBe('12.50');

        $zm2026 = $service->getAllocation(AllocationConfig::TYPE_ZM, '2026-06-15');
        expect($zm2026['setor'])->toBe('100.00');
        expect($zm2026['kelola'])->toBe('0.00');

        $ifs2026 = $service->getAllocation(AllocationConfig::TYPE_IFS, '2026-06-15');
        expect($ifs2026['setor'])->toBe('80.00');
        expect($ifs2026['kelola'])->toBe('20.00');
        expect($ifs2026['amil'])->toBe('20.00');
    });
});

describe('Backward Compatibility', function () {
    it('empty table uses fallback defaults for ZF', function () {
        $service = app(AllocationConfigService::class);
        $service->clearCache();

        $allocation = $service->getAllocation(AllocationConfig::TYPE_ZF, '2025-06-15');

        expect($allocation['setor'])->toBe('30');
        expect($allocation['kelola'])->toBe('70');
        expect($allocation['amil'])->toBe('12.5');
    });

    it('empty table uses fallback defaults for ZM', function () {
        $service = app(AllocationConfigService::class);
        $service->clearCache();

        $allocation = $service->getAllocation(AllocationConfig::TYPE_ZM, '2025-06-15');

        expect($allocation['setor'])->toBe('30');
        expect($allocation['kelola'])->toBe('70');
        expect($allocation['amil'])->toBe('12.5');
    });

    it('empty table uses fallback defaults for IFS with different amil', function () {
        $service = app(AllocationConfigService::class);
        $service->clearCache();

        $allocation = $service->getAllocation(AllocationConfig::TYPE_IFS, '2025-06-15');

        expect($allocation['setor'])->toBe('30');
        expect($allocation['kelola'])->toBe('70');
        expect($allocation['amil'])->toBe('20');
    });

    it('existing rekap_alokasi records remain valid after migration', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        $service = app(AllocationConfigService::class);
        $service->clearCache();

        $totalAmount = '100000';
        $allocation = $service->getAllocation(AllocationConfig::TYPE_ZF, '2025-06-15');

        $setorAmount = bcdiv(bcmul($totalAmount, $allocation['setor'], 4), '100', 2);
        $kelolaAmount = bcsub($totalAmount, $setorAmount, 2);

        expect($setorAmount)->toBe('30000.00');
        expect($kelolaAmount)->toBe('70000.00');

        $hakAmil = $service->calculateHakAmil($kelolaAmount, $allocation['amil']);
        expect($hakAmil)->toBe('8750.00');
    });

    it('defaults match current hardcoded values', function () {
        expect(AllocationConfig::DEFAULT_SETOR)->toBe(30.00);
        expect(AllocationConfig::DEFAULT_KELOLA)->toBe(70.00);
        expect(AllocationConfig::DEFAULT_AMIL_ZF_ZM)->toBe(12.50);
        expect(AllocationConfig::DEFAULT_AMIL_IFS)->toBe(20.00);
    });

    it('service handles missing config for specific year gracefully', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2026,
            'setor_percentage' => '35.00',
            'kelola_percentage' => '65.00',
            'amil_percentage' => '15.00',
        ]);

        $service = app(AllocationConfigService::class);
        $service->clearCache();

        $allocation2025 = $service->getAllocation(AllocationConfig::TYPE_ZF, '2025-06-15');
        expect($allocation2025['setor'])->toBe('30');
        expect($allocation2025['kelola'])->toBe('70');

        $allocation2026 = $service->getAllocation(AllocationConfig::TYPE_ZF, '2026-06-15');
        expect($allocation2026['setor'])->toBe('35.00');
        expect($allocation2026['kelola'])->toBe('65.00');
    });

    it('service handles future year transactions correctly', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        $service = app(AllocationConfigService::class);
        $service->clearCache();

        $allocation = $service->getAllocation(AllocationConfig::TYPE_ZF, '2030-06-15');
        expect($allocation['setor'])->toBe('30.00');
        expect($allocation['kelola'])->toBe('70.00');
    });
});

describe('Database Constraints', function () {
    it('enforces unique constraint on zis_type and effective_year', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        expect(fn() => AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '40.00',
            'kelola_percentage' => '60.00',
            'amil_percentage' => '15.00',
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('allows same year with different zis_type', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        $config = AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZM,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        expect($config->exists)->toBeTrue();
    });

    it('allows same zis_type with different year', function () {
        AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2025,
            'setor_percentage' => '30.00',
            'kelola_percentage' => '70.00',
            'amil_percentage' => '12.50',
        ]);

        $config = AllocationConfig::create([
            'zis_type' => AllocationConfig::TYPE_ZF,
            'effective_year' => 2026,
            'setor_percentage' => '35.00',
            'kelola_percentage' => '65.00',
            'amil_percentage' => '15.00',
        ]);

        expect($config->exists)->toBeTrue();
    });
});
