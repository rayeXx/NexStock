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
        Schema::create('t_purchase_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('t_purchase_orders')->onDelete('cascade');
            $table->string('produk_id'); // FK to m_products
            $table->foreign('produk_id')->references('kode_produk')->on('m_products')->onDelete('cascade');
            $table->integer('qty_pesan');
            $table->integer('qty_diterima')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_purchase_order_details');
    }
};
