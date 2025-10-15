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
        Schema::create('infak_terikats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('unit_zis')->onDelete('cascade');
            $table->date('trx_date');
            $table->string('munfiq_name');
            $table->integer('amount');
            $table->text('desc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infak_terikats');
    }
};
