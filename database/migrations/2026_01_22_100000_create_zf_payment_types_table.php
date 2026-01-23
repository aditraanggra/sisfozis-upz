<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zf_payment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['beras', 'uang']);
            $table->float('rice_amount')->nullable();
            $table->integer('money_amount')->nullable();
            $table->string('sk_reference')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zf_payment_types');
    }
};
