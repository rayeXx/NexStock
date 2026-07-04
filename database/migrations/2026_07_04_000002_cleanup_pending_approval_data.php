<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete all Pending Approval dummy POs
        $pendingPos = DB::table('t_purchase_orders')
            ->where('status', 'Pending Approval')
            ->get();
            
        foreach ($pendingPos as $po) {
            DB::table('t_purchase_order_details')
                ->where('po_id', $po->id)
                ->delete();
                
            DB::table('t_purchase_orders')
                ->where('id', $po->id)
                ->delete();
        }

        // Delete pending Damaged Reports
        DB::table('t_damaged_reports')
            ->where('status', 'Pending')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed for data cleanup
    }
};
