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
        Schema::create('m_products', function (Blueprint $table) {
            $table->string('kode_produk')->primary(); // SKU
            $table->string('nama_produk');
            $table->foreignId('kategori_id')->constrained('m_categories')->onDelete('cascade');
            $table->text('harga_beli'); // Store encrypted value
            $table->integer('stok_minimum')->default(0);
            $table->enum('uom', ['Pcs', 'Dus', 'Pack'])->default('Pcs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_products');
    }
};
