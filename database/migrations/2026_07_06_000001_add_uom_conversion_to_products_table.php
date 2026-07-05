<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('m_products', function (Blueprint $table) {
            $table->string('satuan_beli')->default('Pack');
            $table->string('satuan_jual')->default('Pcs');
            $table->integer('rasio_konversi')->default(1);
        });

        // Initialize existing products UOM conversion fields
        $products = DB::table('m_products')->get();
        foreach ($products as $p) {
            DB::table('m_products')->where('kode_produk', $p->kode_produk)->update([
                'satuan_beli' => $p->uom ?? 'Pack',
                'satuan_jual' => $p->uom ?? 'Pcs',
                'rasio_konversi' => 1,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_products', function (Blueprint $table) {
            $table->dropColumn(['satuan_beli', 'satuan_jual', 'rasio_konversi']);
        });
    }
};
