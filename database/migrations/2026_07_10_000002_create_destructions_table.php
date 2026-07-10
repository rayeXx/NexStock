<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_destructions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('damaged_report_id');
            $table->foreign('damaged_report_id')->references('id')->on('t_damaged_reports')->onDelete('cascade');
            
            $table->string('produk_id');
            $table->foreign('produk_id')->references('kode_produk')->on('m_products')->onDelete('cascade');
            
            $table->string('batch_number');
            $table->string('rak_id');
            
            $table->integer('qty_dimusnahkan');
            $table->text('alasan');
            $table->text('catatan_pemusnahan')->nullable();
            
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            
            $table->string('foto_pemusnahan')->nullable();
            
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->foreign('confirmed_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            
            $table->string('status')->default('Belum Ditugaskan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_destructions');
    }
};
