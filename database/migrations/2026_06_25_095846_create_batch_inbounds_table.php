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
        Schema::create('t_batch_inbounds', function (Blueprint $table) {
            $table->string('batch_number')->primary();
            $table->string('produk_id'); // FK to m_products
            $table->foreign('produk_id')->references('kode_produk')->on('m_products')->onDelete('cascade');
            $table->foreignId('po_id')->nullable()->constrained('t_purchase_orders')->onDelete('set null');
            $table->string('rak_id'); // FK to m_racks
            $table->foreign('rak_id')->references('kode_rak')->on('m_racks')->onDelete('cascade');
            $table->date('expired_date');
            $table->integer('stok_awal_batch');
            $table->integer('stok_sisa_batch');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_batch_inbounds');
    }
};
