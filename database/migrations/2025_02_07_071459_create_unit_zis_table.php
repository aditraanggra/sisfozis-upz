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
        Schema::create('unit_zis', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('unit_categories')->onDelete('cascade');
            $table->foreignId('village_id')->constrained('villages')->onDelete('cascade');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->text('no_sk')->nullable();
            $table->text('unit_name')->nullable();
            $table->text('no_register')->nullable();
            $table->text('address')->nullable();
            $table->text('unit_leader')->nullable();
            $table->text('unit_assistant')->nullable();
            $table->text('unit_finance')->nullable();
            $table->text('operator_name')->nullable();
            $table->text('operator_phone')->nullable();
            $table->integer('rice_price')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_zis');
    }
};
