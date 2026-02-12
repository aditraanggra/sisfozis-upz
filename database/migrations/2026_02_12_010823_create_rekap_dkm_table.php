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
        Schema::create('rekap_dkm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('unit_zis')->onDelete('cascade')->after('id');
            $table->string('periode');
            $table->date('periode_date');
            $table->integer('t_penerimaan_zis')->default(0);
            $table->decimal('t_penerimaan_zis_beras', 12, 3)->default(0);
            $table->integer('t_pendistribusian')->default(0);
            $table->decimal('t_pendistribusian_beras', 12, 3)->default(0);
            $table->integer('t_setor')->default(0);
            $table->decimal('t_setor_beras', 12, 3)->default(0);
            $table->integer('muzakki')->default(0);
            $table->integer('mustahik')->default(0);
            $table->unique(['unit_id', 'periode', 'periode_date'], 'rekap_dkm_unique_unit_periode_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_dkm');
    }
};
