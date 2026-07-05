<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add per-PO price tracking to purchase order details
        Schema::table('t_purchase_order_details', function (Blueprint $table) {
            $table->decimal('harga_satuan', 15, 2)->nullable()->after('qty_diterima')
                ->comment('Harga beli aktual dari supplier pada transaksi PO ini, dapat berbeda dari harga master produk');
        });

        // Add target delivery date to purchase orders for lead time KPI measurement
        Schema::table('t_purchase_orders', function (Blueprint $table) {
            $table->date('target_tanggal_kirim')->nullable()->after('status')
                ->comment('Target tanggal pengiriman yang dijanjikan supplier, dipakai untuk KPI lead time keterlambatan');
        });
    }

    public function down(): void
    {
        Schema::table('t_purchase_order_details', function (Blueprint $table) {
            $table->dropColumn('harga_satuan');
        });

        Schema::table('t_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('target_tanggal_kirim');
        });
    }
};
