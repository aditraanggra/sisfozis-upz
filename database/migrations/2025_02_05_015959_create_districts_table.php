<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id(); // id integer pk increments unique
            $table->string('district_code'); // district_code text
            $table->string('name'); // name text
            $table->timestamps(); // Optional: created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
