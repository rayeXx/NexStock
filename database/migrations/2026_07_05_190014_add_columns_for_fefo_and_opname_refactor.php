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
        Schema::table('t_stock_opnames', function (Blueprint $table) {
            $table->string('status')->default('Pending Approval');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
        });

        Schema::table('t_outbound_details', function (Blueprint $table) {
            $table->string('rak_id')->nullable();
            $table->foreign('rak_id')->references('kode_rak')->on('m_racks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_stock_opnames', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['status', 'approved_by', 'approved_at']);
        });

        Schema::table('t_outbound_details', function (Blueprint $table) {
            $table->dropForeign(['rak_id']);
            $table->dropColumn('rak_id');
        });
    }
};
