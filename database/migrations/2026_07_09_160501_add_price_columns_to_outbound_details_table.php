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
        Schema::table('t_outbound_details', function (Blueprint $table) {
            $table->integer('harga_satuan_final')->default(0)->after('qty_keluar');
            $table->integer('persentase_diskon')->default(0)->after('harga_satuan_final');
            $table->integer('subtotal')->default(0)->after('persentase_diskon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_outbound_details', function (Blueprint $table) {
            $table->dropColumn(['harga_satuan_final', 'persentase_diskon', 'subtotal']);
        });
    }
};
