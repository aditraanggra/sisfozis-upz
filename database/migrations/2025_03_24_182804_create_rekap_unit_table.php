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
        Schema::create('rekap_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('unit_zis')->onDelete('cascade');
            $table->string('periode');
            $table->date('periode_date');
            $table->integer('t_penerimaan_zis')->default(0);
            $table->float('t_penerimaan_zis_beras')->default(0);
            $table->integer('t_pendistribusian')->default(0);
            $table->integer('t_setor')->default(0);
            $table->integer('muzakki')->default(0);
            $table->integer('mustahik')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_unit');
    }
};
