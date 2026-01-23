<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocation_configs', function (Blueprint $table) {
            $table->id();
            $table->string('zis_type', 10);
            $table->integer('effective_year');
            $table->decimal('setor_percentage', 5, 2);
            $table->decimal('kelola_percentage', 5, 2);
            $table->decimal('amil_percentage', 5, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['zis_type', 'effective_year']);
            $table->index(['zis_type', 'effective_year']);
        });

        // Add PostgreSQL check constraints
        DB::statement("ALTER TABLE allocation_configs ADD CONSTRAINT chk_zis_type CHECK (zis_type IN ('zf', 'zm', 'ifs'))");
        DB::statement("ALTER TABLE allocation_configs ADD CONSTRAINT chk_effective_year CHECK (effective_year >= 2020)");
        DB::statement("ALTER TABLE allocation_configs ADD CONSTRAINT chk_setor_kelola_sum CHECK (setor_percentage + kelola_percentage = 100)");
        DB::statement("ALTER TABLE allocation_configs ADD CONSTRAINT chk_percentages_range CHECK (setor_percentage >= 0 AND setor_percentage <= 100 AND kelola_percentage >= 0 AND kelola_percentage <= 100 AND amil_percentage >= 0 AND amil_percentage <= 100)");
    }

    public function down(): void
    {
        Schema::dropIfExists('allocation_configs');
    }
};
