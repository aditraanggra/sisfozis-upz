<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kurbans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')
                ->constrained('unit_zis')
                ->cascadeOnDelete(); // FK ke tabel units (UPZ)

            $table->unsignedInteger('total_mudhohi')->default(0);  // jumlah peserta
            $table->enum('animal_types', ['Kambing', 'Domba', 'Sapi', 'Kerbau']);
            $table->unsignedInteger('total')->default(0);          // jumlah hewan
            $table->unsignedInteger('total_benef')->default(0);    // penerima manfaat
            $table->text('desc')->nullable();                      // keterangan tambahan

            $table->timestamps();
            $table->softDeletes();

            // Index untuk laporan/filter cepat
            $table->index(['unit_id', 'animal_types']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kurbans');
    }
};
