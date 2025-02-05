<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villages', function (Blueprint $table) {
            $table->id(); // id integer pk increments unique
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade'); // distric_id integer *> districts.id
            $table->string('village_code'); // village_code text
            $table->string('name'); // name text
            $table->timestamps(); // Optional: created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
