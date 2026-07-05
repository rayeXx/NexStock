<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add audit trail column for anti-blind picking validation
        Schema::table('t_outbound_details', function (Blueprint $table) {
            $table->string('batch_scanned')->nullable()->after('rak_id')
                ->comment('Batch number yang di-input/scan oleh staf sebagai konfirmasi fisik pengambilan; harus cocok dengan batch_number FEFO');
        });
    }

    public function down(): void
    {
        Schema::table('t_outbound_details', function (Blueprint $table) {
            $table->dropColumn('batch_scanned');
        });
    }
};
