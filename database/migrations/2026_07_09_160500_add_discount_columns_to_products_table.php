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
        Schema::table('m_products', function (Blueprint $table) {
            if (!Schema::hasColumn('m_products', 'harga_jual')) {
                $table->integer('harga_jual')->default(0)->after('harga_beli');
            }
            if (!Schema::hasColumn('m_products', 'diskon_bawah_1_tahun')) {
                $table->integer('diskon_bawah_1_tahun')->default(0)->after('harga_jual');
            }
            if (!Schema::hasColumn('m_products', 'diskon_bawah_6_bulan')) {
                $table->integer('diskon_bawah_6_bulan')->default(0)->after('diskon_bawah_1_tahun');
            }
            if (!Schema::hasColumn('m_products', 'diskon_bawah_3_bulan')) {
                $table->integer('diskon_bawah_3_bulan')->default(0)->after('diskon_bawah_6_bulan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_products', function (Blueprint $table) {
            $table->dropColumn([
                'harga_jual',
                'diskon_bawah_1_tahun',
                'diskon_bawah_6_bulan',
                'diskon_bawah_3_bulan',
            ]);
        });
    }
};
