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
        Schema::table('t_purchase_orders', function (Blueprint $table) {
            // Change enum to string to remove the strict CHECK constraint in SQLite
            $table->string('status')->default('Draft')->change();
        });

        Schema::table('t_damaged_reports', function (Blueprint $table) {
            // Change enum to string
            $table->string('status')->default('Approved')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 
    }
};
