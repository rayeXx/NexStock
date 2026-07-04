<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_batch_inbounds', function (Blueprint $table) {
            $table->string('batch_supplier')->nullable()->after('batch_number');
        });

        Schema::table('t_po_receiving_history', function (Blueprint $table) {
            $table->integer('qty_datang')->default(0)->after('qty_received');
            $table->integer('qty_rusak')->default(0)->after('qty_datang');
            $table->string('kondisi_barang')->nullable()->after('qty_rusak');
            $table->string('alasan_kerusakan')->nullable()->after('kondisi_barang');
            $table->text('catatan')->nullable()->after('alasan_kerusakan');
            $table->string('batch_supplier')->nullable()->after('batch_number');
            $table->date('expired_date')->nullable()->after('batch_supplier');
            $table->string('rak_id')->nullable()->after('expired_date');
            $table->string('status_retur')->nullable()->after('alasan_kerusakan');
        });

        // Update existing dummy records so they make sense
        DB::table('t_po_receiving_history')->update([
            'qty_datang' => DB::raw('qty_received'),
            'kondisi_barang' => 'Baik'
        ]);
    }

    public function down(): void
    {
        Schema::table('t_po_receiving_history', function (Blueprint $table) {
            $table->dropColumn([
                'qty_datang',
                'qty_rusak',
                'kondisi_barang',
                'alasan_kerusakan',
                'catatan',
                'batch_supplier',
                'expired_date',
                'rak_id',
                'status_retur'
            ]);
        });

        Schema::table('t_batch_inbounds', function (Blueprint $table) {
            $table->dropColumn('batch_supplier');
        });
    }
};
