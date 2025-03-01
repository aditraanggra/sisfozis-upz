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
        Schema::table('distributions', function (Blueprint $table) {
            $table->foreignId('unit_id')->constrained('unit_zis')->onDelete('cascade')->after('id');
            $table->date('trx_date');
            $table->string('mustahik_name');
            $table->string('nik')->nullable();
            $table->string('fund_type')->nullable();
            $table->string('asnaf')->nullable();
            $table->string('program')->nullable();
            $table->integer('total_rice')->default(0);
            $table->integer('total_amount')->default(0);
            $table->integer('beneficiary')->default(1);
            $table->integer('rice_to_amount')->default(0);
            $table->text('desc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};
