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
        Schema::create('fidyahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('unit_zis')->onDelete('cascade')->after('id');
            $table->date('trx_date');
            $table->string('name');
            $table->integer('total_day')->default(1);
            $table->integer('amount')->default(0);
            $table->text('desc')->nullable();
            $table->timestamps('created_at');
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fidyahs');
    }
};
