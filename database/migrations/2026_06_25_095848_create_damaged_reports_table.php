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
        Schema::create('t_damaged_reports', function (Blueprint $table) {
            $table->id();
            $table->string('produk_id'); // FK to m_products
            $table->foreign('produk_id')->references('kode_produk')->on('m_products')->onDelete('cascade');
            $table->string('batch_number'); // FK to t_batch_inbounds
            $table->foreign('batch_number')->references('batch_number')->on('t_batch_inbounds')->onDelete('cascade');
            $table->string('rak_id'); // FK to m_racks
            $table->foreign('rak_id')->references('kode_rak')->on('m_racks')->onDelete('cascade');
            $table->integer('qty_rusak');
            $table->string('foto_bukti')->nullable();
            $table->text('alasan');
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_damaged_reports');
    }
};
