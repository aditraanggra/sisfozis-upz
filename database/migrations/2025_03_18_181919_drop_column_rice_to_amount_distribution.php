<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn('rice_to_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('distributions', function (Blueprint $table) {
            $table->integer('rice_to_amount');
        });
    }
};
