<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_restock_requests', function (Blueprint $table) {
            $table->id();
            $table->string('produk_id');
            $table->foreign('produk_id')->references('kode_produk')->on('m_products')->onDelete('cascade');
            $table->integer('qty_request');
            $table->text('alasan');
            $table->enum('status', ['Menunggu Review', 'Approved', 'Rejected'])->default('Menunggu Review');
            $table->text('alasan_reject')->nullable();
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('po_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_restock_requests');
    }
};
