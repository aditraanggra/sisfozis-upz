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
        //
        Schema::table('rekap_unit', function (Blueprint $table) {
            $table->float('t_pendistribusian_beras')->default(0)->after('t_pendistribusian');
            $table->float('t_setor_beras')->default(0)->after('t_setor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('rekap_unit', function (Blueprint $table) {
            $table->dropColumn('t_pendistribusian_beras');
            $table->dropColumn('t_setor_beras');
        });
    }
};
