<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumentasi_kurbans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_kurban_id')->constrained('laporan_kurbans')->cascadeOnDelete();
            $table->enum('jenis', ['foto_kegiatan', 'berita_acara', 'lainnya']);
            $table->string('file_path');
            $table->unsignedInteger('file_size')->default(0);
            $table->string('mime_type');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumentasi_kurbans');
    }
};
