<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add unique composite indexes for rekap tables to support bulk upsert operations.
 * These indexes ensure data integrity and enable efficient upsert queries.
 *
 * Requirements: 3.2, 3.3
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // rekap_zis uses 'period' and 'period_date' column names
        Schema::table('rekap_zis', function (Blueprint $table) {
            $table->unique(['unit_id', 'period', 'period_date'], 'rekap_zis_unique_idx');
        });

        // rekap_pendis uses 'periode' and 'periode_date' column names
        Schema::table('rekap_pendis', function (Blueprint $table) {
            $table->unique(['unit_id', 'periode', 'periode_date'], 'rekap_pendis_unique_idx');
        });

        // rekap_setor uses 'periode' and 'periode_date' column names
        Schema::table('rekap_setor', function (Blueprint $table) {
            $table->unique(['unit_id', 'periode', 'periode_date'], 'rekap_setor_unique_idx');
        });

        // rekap_hak_amil uses 'periode' and 'periode_date' column names
        Schema::table('rekap_hak_amil', function (Blueprint $table) {
            $table->unique(['unit_id', 'periode', 'periode_date'], 'rekap_hak_amil_unique_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_zis', function (Blueprint $table) {
            $table->dropUnique('rekap_zis_unique_idx');
        });

        Schema::table('rekap_pendis', function (Blueprint $table) {
            $table->dropUnique('rekap_pendis_unique_idx');
        });

        Schema::table('rekap_setor', function (Blueprint $table) {
            $table->dropUnique('rekap_setor_unique_idx');
        });

        Schema::table('rekap_hak_amil', function (Blueprint $table) {
            $table->dropUnique('rekap_hak_amil_unique_idx');
        });
    }
};
