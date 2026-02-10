<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );

        // Seed allocation configuration before transaction seeders
        // This ensures allocation rules are available when seeding transaction data
        // $this->call([
        //     AllocationConfigSeeder::class,
        // ]);

        // Seed geographic data (districts and villages)
        $this->call([
            GeographicSeeder::class,
            ZisTransactionSeeder::class,
            SetorZisDummySeeder::class,
        ]);
    }
}
