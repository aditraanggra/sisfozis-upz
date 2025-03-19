<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRekapPendisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rekap_pendis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('unit_zis')->onDelete('cascade')->after('id');
            $table->string('periode');
            $table->date('periode_date');
            $table->integer('t_pendis_zf_amount')->default(0);
            $table->float('t_pendis_zf_rice')->default(0);
            $table->integer('t_pendis_zm')->default(0);
            $table->integer('t_pendis_ifs')->default(0);
            $table->integer('t_pendis_fakir_amount')->default(0);
            $table->integer('t_pendis_miskin_amount')->default(0);
            $table->integer('t_pendis_fisabilillah_amount')->default(0);
            $table->float('t_pendis_fakir_rice')->default(0);
            $table->float('t_pendis_miskin_rice')->default(0);
            $table->float('t_pendis_fisabilillah_rice')->default(0);
            $table->integer('t_pendis_kemanusiaan_amount')->default(0);
            $table->integer('t_pendis_dakwah_amount')->default(0);
            $table->float('t_pendis_kemanusiaan_rice')->default(0);
            $table->float('t_pendis_dakwah_rice')->default(0);
            $table->integer('t_pm')->default(0);
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
        Schema::dropIfExists('rekap_pendis');
    }
}
