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
        Schema::table('zfs', function (Blueprint $table) {
            $table->foreignId('unit_id')->constrained('unit_zis')->onDelete('cascade')->after('id');
            $table->date('trx_date');
            $table->string('muzakki_name');
            $table->float('zf_rice')->default(0);
            $table->integer('zf_amount')->default(0);
            $table->integer('total_muzakki')->default(1);
            $table->text('desc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zfs');
    }
};
