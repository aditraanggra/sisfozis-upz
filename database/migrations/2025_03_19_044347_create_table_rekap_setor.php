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
        Schema::create('rekap_setor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('unit_zis')->onDelete('cascade');
            $table->string('periode');
            $table->date('periode_date');
            $table->integer('t_setor_zf_amount');
            $table->float('t_setor_zf_rice');
            $table->integer('t_setor_zm');
            $table->integer('t_setor_ifs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_setor');
    }
};
