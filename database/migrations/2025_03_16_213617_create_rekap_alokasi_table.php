<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRekapAlokasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rekap_alokasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('unit_zis')->onDelete('cascade')->after('id');
            $table->string('periode');
            $table->date('periode_date');
            $table->integer('total_setor_zf_amount')->default(0);
            $table->float('total_setor_zf_rice')->default(0);
            $table->integer('total_setor_zm')->default(0);
            $table->integer('total_setor_ifs')->default(0);
            $table->integer('total_kelola_zf_amount')->default(0);
            $table->float('total_kelola_zf_rice')->default(0);
            $table->integer('total_kelola_zm')->default(0);
            $table->integer('total_kelola_ifs')->default(0);
            $table->integer('hak_amil_zf_amount')->default(0);
            $table->float('hak_amil_zf_rice')->default(0);
            $table->integer('hak_amil_zm')->default(0);
            $table->integer('hak_amil_ifs')->default(0);
            $table->integer('alokasi_pendis_zf_amount')->default(0);
            $table->float('alokasi_pendis_zf_rice')->default(0);
            $table->integer('alokasi_pendis_zm')->default(0);
            $table->integer('alokasi_pendis_ifs')->default(0);
            $table->integer('hak_op_zf_amount')->default(0);
            $table->float('hak_op_zf_rice')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rekap_alokasi');
    }
}
