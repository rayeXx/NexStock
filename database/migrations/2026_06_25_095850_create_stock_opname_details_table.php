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
        Schema::create('t_stock_opname_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('t_stock_opnames')->onDelete('cascade');
            $table->string('produk_id'); // FK to m_products
            $table->foreign('produk_id')->references('kode_produk')->on('m_products')->onDelete('cascade');
            $table->string('batch_number'); // FK to t_batch_inbounds
            $table->foreign('batch_number')->references('batch_number')->on('t_batch_inbounds')->onDelete('cascade');
            $table->integer('qty_sistem');
            $table->integer('qty_fisik');
            $table->integer('selisih');
            $table->string('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_stock_opname_details');
    }
};
