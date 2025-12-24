<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add composite indexes on (unit_id, trx_date) for transaction tables
 * to optimize rebuild command queries.
 *
 * Requirements: 3.3
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('zfs', function (Blueprint $table) {
            $table->index(['unit_id', 'trx_date'], 'zfs_unit_trx_date_idx');
        });

        Schema::table('zms', function (Blueprint $table) {
            $table->index(['unit_id', 'trx_date'], 'zms_unit_trx_date_idx');
        });

        Schema::table('ifs', function (Blueprint $table) {
            $table->index(['unit_id', 'trx_date'], 'ifs_unit_trx_date_idx');
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->index(['unit_id', 'trx_date'], 'distributions_unit_trx_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zfs', function (Blueprint $table) {
            $table->dropIndex('zfs_unit_trx_date_idx');
        });

        Schema::table('zms', function (Blueprint $table) {
            $table->dropIndex('zms_unit_trx_date_idx');
        });

        Schema::table('ifs', function (Blueprint $table) {
            $table->dropIndex('ifs_unit_trx_date_idx');
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropIndex('distributions_unit_trx_date_idx');
        });
    }
};
