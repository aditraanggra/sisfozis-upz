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
        Schema::create('setor_zis', function (Blueprint $table) {
            $table->id();
            $table->integer('unit_id');
            $table->date('trx_date');
            $table->integer('zf_amount_deposit');
            $table->double('zf_rice_deposit');
            $table->integer('zm_amount_deposit');
            $table->integer('ifs_amount_deposit');
            $table->integer('total_deposit');
            $table->string('status');
            $table->string('validation');
            $table->string('upload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setor_zis');
    }
};
