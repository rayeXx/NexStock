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
        Schema::create('t_outbound_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outbound_id')->constrained('t_outbounds')->onDelete('cascade');
            $table->string('produk_id'); // FK to m_products
            $table->foreign('produk_id')->references('kode_produk')->on('m_products')->onDelete('cascade');
            $table->string('batch_number'); // FK to t_batch_inbounds
            $table->foreign('batch_number')->references('batch_number')->on('t_batch_inbounds')->onDelete('cascade');
            $table->integer('qty_keluar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_outbound_details');
    }
};
