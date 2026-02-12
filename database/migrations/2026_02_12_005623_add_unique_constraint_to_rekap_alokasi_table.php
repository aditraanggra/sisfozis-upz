<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rekap_alokasi', function (Blueprint $table) {
            $table->unique(['unit_id', 'periode', 'periode_date'], 'rekap_alokasi_unique_unit_periode_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_alokasi', function (Blueprint $table) {
            $table->dropUnique('rekap_alokasi_unique_unit_periode_date');
        });
    }
};
