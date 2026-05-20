<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_kurbans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('unit_zis')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tahun_hijriah', 4);
            $table->year('tahun_masehi');
            $table->unsignedSmallInteger('jml_sapi')->default(0);
            $table->unsignedSmallInteger('jml_kerbau')->default(0);
            $table->unsignedSmallInteger('jml_kambing')->default(0);
            $table->unsignedSmallInteger('jml_domba')->default(0);
            $table->unsignedSmallInteger('total_ekor')->default(0);
            $table->unsignedSmallInteger('total_shohibul')->default(0);
            $table->boolean('shohibul_dikoreksi')->default(false);
            $table->string('alasan_koreksi', 255)->nullable();
            $table->unsignedSmallInteger('jml_paket_daging')->nullable();
            $table->unsignedSmallInteger('jml_penerima')->nullable();
            $table->json('kelompok_penerima')->nullable();
            $table->text('catatan')->nullable();
            $table->string('no_referensi', 50)->nullable();
            $table->enum('status', ['draft', 'submitted', 'verified', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_id', 'tahun_hijriah']);
            $table->index('status');
            $table->unique(['unit_id', 'tahun_hijriah', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_kurbans');
    }
};
