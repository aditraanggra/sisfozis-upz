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
        Schema::create('rekap_zis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('unit_zis')->onDelete('cascade')->after('id');
            $table->string('transaction_type');
            $table->string('period');
            $table->date('period_date');
            //Zakat FItrah
            $table->float('total_zf_rice')->default(0)->nullable();
            $table->integer('total_zf_amount')->default(0)->nullable();
            $table->integer('total_zf_muzakki')->default(0)->nullable();
            //Zakat Mal
            $table->integer('total_zm_amount')->default(0)->nullable();
            $table->integer('total_zm_muzakki')->default(0)->nullable();
            //Infak Sedekah
            $table->integer('total_ifs_amount')->default(0)->nullable();
            $table->integer('total_ifs_munfiq')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_zis');
    }
};
