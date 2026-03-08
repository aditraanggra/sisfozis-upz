<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds columns to support:
     * - Rice sold as cash (zf_rice_sold_amount, zf_rice_sold_price)
     * - Proof of rice sale document (zf_rice_sold_proof)
     * - Deposit destination tracking (deposit_destination)
     */
    public function up(): void
    {
        Schema::table('setor_zis', function (Blueprint $table) {
            $table->integer('zf_rice_sold_amount')->default(0)->after('zf_rice_deposit');
            $table->integer('zf_rice_sold_price')->default(0)->after('zf_rice_sold_amount');
            $table->string('zf_rice_sold_proof')->nullable()->after('zf_rice_sold_price');
            $table->string('deposit_destination')->nullable()->after('upload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setor_zis', function (Blueprint $table) {
            $table->dropColumn([
                'zf_rice_sold_amount',
                'zf_rice_sold_price',
                'zf_rice_sold_proof',
                'deposit_destination',
            ]);
        });
    }
};
