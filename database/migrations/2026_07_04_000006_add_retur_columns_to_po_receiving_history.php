<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_po_receiving_history', function (Blueprint $table) {
            $table->date('tanggal_retur')->nullable()->after('status_retur');
            $table->string('catatan_retur')->nullable()->after('tanggal_retur');
        });
    }

    public function down(): void
    {
        Schema::table('t_po_receiving_history', function (Blueprint $table) {
            $table->dropColumn(['tanggal_retur', 'catatan_retur']);
        });
    }
};
