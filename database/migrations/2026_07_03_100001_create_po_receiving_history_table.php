<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create receiving history table
        Schema::create('t_po_receiving_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('t_purchase_orders')->onDelete('cascade');
            $table->string('produk_id');
            $table->foreign('produk_id')->references('kode_produk')->on('m_products')->onDelete('cascade');
            $table->integer('qty_received');
            $table->string('batch_number')->nullable();
            $table->datetime('received_at');
            $table->foreignId('received_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Note: SQLite doesn't enforce enum constraints, so 'Partial' and 'Cancelled'
        // values can be stored directly without altering the column.
        // The model will handle the valid status values.
    }

    public function down(): void
    {
        Schema::dropIfExists('t_po_receiving_history');
    }
};
